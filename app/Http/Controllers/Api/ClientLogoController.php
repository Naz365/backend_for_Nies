<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientLogo;
use Illuminate\Http\JsonResponse;

class ClientLogoController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $logos = ClientLogo::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logos,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve client logos',
            ], 500);
        }
    }
}
