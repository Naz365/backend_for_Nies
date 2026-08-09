<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $categories = Category::query()
                ->where('is_active', true)
                ->withCount(['products' => function ($query) {
                    $query->where('status', 'published');
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(string $slug): JsonResponse
    {
        try {
            $category = Category::query()
                ->where('slug', $slug)
                ->where('is_active', true)
                ->with(['products' => function ($query) {
                    $query->where('status', 'published')->orderBy('is_featured', 'desc');
                }])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $category,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }
    }
}
