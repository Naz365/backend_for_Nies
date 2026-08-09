<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::query()
                ->where('status', 'published')
                ->with('category:id,name,slug');

            // Category filtering
            if ($request->filled('category')) {
                $categorySlug = $request->input('category');
                $query->where(function ($q) use ($categorySlug) {
                    $q->where('category_slug', $categorySlug)
                      ->orWhereHas('category', function ($cq) use ($categorySlug) {
                          $cq->where('slug', $categorySlug);
                      });
                });
            }

            // Keyword search filtering
            if ($request->filled('q') || $request->filled('search')) {
                $searchTerm = $request->input('q', $request->input('search'));
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('sku', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('specifications', 'like', "%{$searchTerm}%");
                });
            }

            // Featured filtering
            if ($request->boolean('featured')) {
                $query->where('is_featured', true);
            }

            // In-stock filtering
            if ($request->boolean('in_stock')) {
                $query->where(function ($q) {
                    $q->where('track_inventory', false)
                      ->orWhere('stock_quantity', '>', 0);
                });
            }

            // Sorting
            $sort = $request->input('sort', 'featured');
            switch ($sort) {
                case 'price-low':
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price-high':
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'featured':
                default:
                    $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                    break;
            }

            $perPage = (int) $request->input('per_page', 50);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products catalog',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(string $slug): JsonResponse
    {
        try {
            $product = Product::query()
                ->where('status', 'published')
                ->where(function ($q) use ($slug) {
                    $q->where('slug', $slug);
                    if (is_numeric($slug)) {
                        $q->orWhere('id', (int) $slug);
                    }
                })
                ->with('category')
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $product,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
    }
}
