<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Initialize a payment session for an order
     */
    public function initiatePayment(Order $order, string $gateway = 'sslcommerz'): array
    {
        $transactionId = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));

        // Create or update pending payment record
        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id, 'status' => 'pending'],
            [
                'transaction_id' => $transactionId,
                'payment_method' => $gateway,
                'gateway' => $gateway,
                'amount' => $order->total_amount,
                'currency' => 'BDT',
                'status' => 'pending',
            ]
        );

        Log::info("[PaymentGateway] Initiated {$gateway} payment for Order #{$order->order_number}", [
            'transaction_id' => $transactionId,
            'amount' => $order->total_amount,
        ]);

        return [
            'transaction_id' => $transactionId,
            'amount' => (float) $order->total_amount,
            'currency' => 'BDT',
            'order_number' => $order->order_number,
        ];
    }

    /**
     * Process gateway IPN / Webhook callback with strict idempotency (Section 38)
     */
    public function handleWebhook(string $transactionId, string $status, array $gatewayPayload): array
    {
        return DB::transaction(function () use ($transactionId, $status, $gatewayPayload) {
            $payment = Payment::where('transaction_id', $transactionId)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                Log::warning("[PaymentGateway:Webhook] Unknown transaction ID received: {$transactionId}");
                return ['success' => false, 'message' => 'Transaction record not found'];
            }

            // IDEMPOTENCY CHECK: If payment is already resolved, return success immediately without re-processing
            if (in_array($payment->status, ['paid', 'success', 'failed', 'refunded'])) {
                Log::info("[PaymentGateway:Webhook] Duplicate webhook received for already settled transaction: {$transactionId}");
                return [
                    'success' => true,
                    'message' => 'Transaction already processed (Idempotent match)',
                    'status' => $payment->status,
                ];
            }

            $order = $payment->order;
            $normalizedStatus = in_array(strtolower($status), ['valid', 'success', 'successful', 'paid']) ? 'paid' : 'failed';

            // Update payment record
            $payment->update([
                'status' => $normalizedStatus,
                'gateway_response' => json_encode($gatewayPayload),
            ]);

            // Synchronize order state
            if ($normalizedStatus === 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => $order->status === 'pending' ? 'confirmed' : $order->status,
                ]);
            } else {
                $payment->update(['status' => 'failed']);
            }

            Log::info("[PaymentGateway:Webhook] Transaction {$transactionId} settled as {$normalizedStatus} for Order #{$order->order_number}");

            return [
                'success' => true,
                'message' => 'Payment settled successfully',
                'order_number' => $order->order_number,
                'status' => $normalizedStatus,
            ];
        });
    }
}
