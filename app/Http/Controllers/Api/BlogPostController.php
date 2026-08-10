<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;

class BlogPostController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $posts = BlogPost::where('status', 'published')->orderBy('published_at', 'desc')->get();
            return response()->json([
                'success' => true,
                'data' => $posts,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve blog posts',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
