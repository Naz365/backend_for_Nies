<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $projects = Project::where('status', 'published')->get();
            return response()->json([
                'success' => true,
                'data' => $projects,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve projects',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
