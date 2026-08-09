<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteRequestController extends Controller
{
    /**
     * POST /api/v1/quote-requests
     * Creates a B2B project quotation request
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:50',
            'service_type' => 'required|string|max:100',
            'project_description' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $quote = QuoteRequest::create([
                'request_number' => QuoteRequest::generateRequestNumber(),
                'customer_name' => $validated['customer_name'],
                'company_name' => $validated['company_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'service_type' => $validated['service_type'],
                'project_description' => $validated['project_description'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'new',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation request submitted successfully. Our engineering team will contact you shortly.',
                'data' => [
                    'request_number' => $quote->request_number,
                    'customer_name' => $quote->customer_name,
                    'service_type' => $quote->service_type,
                    'status' => $quote->status,
                    'created_at' => $quote->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quote request',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
