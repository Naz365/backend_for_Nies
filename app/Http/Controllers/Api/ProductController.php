<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::where('status', 'published')->get();

        if ($products->isEmpty()) {
            return response()->json([
                'data' => [
                    [
                        'id' => 1,
                        'title' => "SUPPRESSION SYSTEM",
                        'slug' => "suppression-system",
                        'category_slug' => "suppression-system",
                        'category_name' => "SUPPRESSION SYSTEM",
                        'image' => "/wp-content/uploads/2017/11/fire-suppression-system.jpg",
                        'description' => "Automatic FM-200 and Novec fire suppression system."
                    ],
                    [
                        'id' => 2,
                        'title' => "FIRE DETECTION ALARM SYSTEM",
                        'slug' => "fire-detection-alarm-system",
                        'category_slug' => "alarm-systems",
                        'category_name' => "ALARM SYSTEMS",
                        'image' => "/wp-content/uploads/2017/05/fire-detection-alarm-system.jpg",
                        'description' => "Addressable fire alarm control panels and smoke detectors."
                    ]
                ]
            ]);
        }

        return response()->json(['data' => $products]);
    }
}
