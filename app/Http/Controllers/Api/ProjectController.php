<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = Project::where('status', 'published')->get();

        if ($projects->isEmpty()) {
            // Manifest fallback format
            return response()->json([
                'data' => [
                    [
                        'id' => 1,
                        'title' => "BTI Tower Fire Safety Installation",
                        'slug' => "bti-tower-fire-safety",
                        'category' => "FIRE EXTINGUISHERS",
                        'client' => "BTI",
                        'image' => "/wp-content/uploads/2017/11/BIT-Building-copy.jpg",
                        'description' => "Complete fire protection system design, extinguisher installation, and safety compliance certification for BTI tower."
                    ],
                    [
                        'id' => 2,
                        'title' => "BRAC University Surveillance & CCTV",
                        'slug' => "brac-university-cctv",
                        'category' => "CCTV",
                        'client' => "BRAC University",
                        'image' => "/wp-content/uploads/2017/11/brac-university.jpg",
                        'description' => "High-definition CCTV and central monitoring security system deployment across multi-building campus."
                    ],
                    [
                        'id' => 3,
                        'title' => "BRAC Centre Inn Access Control System",
                        'slug' => "brac-centre-inn-access-control",
                        'category' => "ACCESS CONTROL",
                        'client' => "BRAC Centre Inn",
                        'image' => "/wp-content/uploads/2017/11/brac-centre-inn-copy.jpg",
                        'description' => "Convenient RFID and biometric access control integration for hospitality entry points."
                    ]
                ]
            ]);
        }

        return response()->json(['data' => $projects]);
    }
}
