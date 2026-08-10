<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $settings = SiteSetting::all()->pluck('value', 'key');
            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve site settings',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
