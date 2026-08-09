<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    /**
     * POST /api/v1/service-requests
     * Submits a field service / refilling / maintenance request
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'service_category' => 'required|string|max:100',
            'location_address' => 'required|string',
            'equipment_details' => 'nullable|string',
            'urgency' => 'nullable|string|in:low,normal,high,emergency',
        ]);

        try {
            $serviceRequest = ServiceRequest::create([
                'request_number' => ServiceRequest::generateRequestNumber(),
                'customer_name' => $validated['customer_name'],
                'company_name' => $validated['company_name'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'service_category' => $validated['service_category'],
                'location_address' => $validated['location_address'],
                'equipment_details' => $validated['equipment_details'] ?? null,
                'urgency' => $validated['urgency'] ?? 'normal',
                'status' => 'pending_review',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service request logged successfully. A service engineer will be assigned.',
                'data' => [
                    'request_number' => $serviceRequest->request_number,
                    'customer_name' => $serviceRequest->customer_name,
                    'service_category' => $serviceRequest->service_category,
                    'urgency' => $serviceRequest->urgency,
                    'status' => $serviceRequest->status,
                    'created_at' => $serviceRequest->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit service request',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/v1/service-requests/{request_number}
     */
    public function show(string $requestNumber): JsonResponse
    {
        try {
            $service = ServiceRequest::where('request_number', $requestNumber)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'request_number' => $service->request_number,
                    'customer_name' => $service->customer_name,
                    'service_category' => $service->service_category,
                    'status' => $service->status,
                    'scheduled_visit_date' => $service->scheduled_visit_date?->toDateString(),
                    'created_at' => $service->created_at->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service request not found',
            ], 404);
        }
    }
}
