<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    public function index(): JsonResponse
    {
        $setting = SiteSetting::first();

        if (!$setting) {
            return response()->json([
                'data' => [
                    'address' => "GA-85(Gr Floor), Middle Badda, Gulshan, Dhaka",
                    'phone_primary' => "+880 1711 135 731",
                    'phone_secondary' => "+880 1670 236 785",
                    'telephone' => "+88-02-9882326",
                    'fax' => "+88-02-9882326",
                    'emails' => ["info@niengineeringbd.com", "sales@niengineeringbd.com"],
                    'company_profile_pdf' => "/wp-content/uploads/2017/11/Company_Profile.pdf"
                ]
            ]);
        }

        return response()->json(['data' => $setting]);
    }
}
