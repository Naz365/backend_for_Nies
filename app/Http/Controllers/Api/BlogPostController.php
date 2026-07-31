<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;

class BlogPostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = BlogPost::where('status', 'published')->latest('published_at')->get();

        if ($posts->isEmpty()) {
            return response()->json([
                'data' => [
                    [
                        'id' => 1,
                        'title' => "Essential Fire Safety Maintenance Rules for Industrial Facilities",
                        'slug' => "essential-fire-safety-maintenance",
                        'summary' => "Regular maintenance and annual refilling of fire extinguishers are critical line-of-defense measures.",
                        'content' => "<p>Fire is a serious threat to physical safety. Regular inspection and refilling using well-equipped workshops guarantee optimal operational readiness.</p>",
                        'thumbnail' => "/wp-content/uploads/2017/05/fire-detection-alarm-system.jpg",
                        'published_at' => "2026-07-01",
                        'author' => "N.I. Safety Team"
                    ],
                    [
                        'id' => 2,
                        'title' => "Why Modern Facilities Need Integrated CCTV & Access Control",
                        'slug' => "integrated-cctv-and-access-control",
                        'summary' => "Learn how combining surveillance cameras with smart door access boosts facility protection.",
                        'content' => "<p>INTEGRATED SOLUTIONS FOR SECURITY & SURVEILLANCE bring together real-time tracking, intruder detection, and central control room management.</p>",
                        'thumbnail' => "/wp-content/uploads/2017/11/AccessControlSystems.jpg",
                        'published_at' => "2026-07-15",
                        'author' => "N.I. Safety Team"
                    ]
                ]
            ]);
        }

        return response()->json(['data' => $posts]);
    }
}
