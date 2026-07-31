<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Product;
use App\Models\BlogPost;
use App\Models\Customer;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Administrator Account
        User::updateOrCreate(
            ['email' => 'admin@niengineeringbd.com'],
            [
                'name' => 'N.I. Administrator',
                'password' => Hash::make('password123'),
            ]
        );

        // 1. Site Settings Seeder
        SiteSetting::updateOrCreate(
            ['id' => 1],
            [
                'address' => 'GA-85(Gr Floor), Middle Badda, Gulshan, Dhaka',
                'phone_primary' => '+880 1711 135 731',
                'phone_secondary' => '+880 1670 236 785',
                'telephone' => '+88-02-9882326',
                'fax' => '+88-02-9882326',
                'emails' => ['info@niengineeringbd.com', 'sales@niengineeringbd.com'],
                'company_profile_pdf' => '/wp-content/uploads/2017/11/Company_Profile.pdf',
            ]
        );

        // 2. Projects Seeder
        $projects = [
            [
                'title' => 'BTI Tower Fire Safety Installation',
                'slug' => 'bti-tower-fire-safety',
                'category' => 'FIRE EXTINGUISHERS',
                'client' => 'BTI',
                'image' => '/wp-content/uploads/2017/11/BIT-Building-copy.jpg',
                'description' => 'Complete fire protection system design, extinguisher installation, and safety compliance certification for BTI tower.',
                'status' => 'published',
            ],
            [
                'title' => 'BRAC University Surveillance & CCTV',
                'slug' => 'brac-university-cctv',
                'category' => 'CCTV',
                'client' => 'BRAC University',
                'image' => '/wp-content/uploads/2017/11/brac-university.jpg',
                'description' => 'High-definition CCTV and central monitoring security system deployment across multi-building campus.',
                'status' => 'published',
            ],
            [
                'title' => 'BRAC Centre Inn Access Control System',
                'slug' => 'brac-centre-inn-access-control',
                'category' => 'ACCESS CONTROL',
                'client' => 'BRAC Centre Inn',
                'image' => '/wp-content/uploads/2017/11/brac-centre-inn-copy.jpg',
                'description' => 'Convenient RFID and biometric access control integration for hospitality entry points.',
                'status' => 'published',
            ],
        ];

        foreach ($projects as $proj) {
            Project::updateOrCreate(['slug' => $proj['slug']], $proj);
        }

        // 3. Products Seeder
        $products = [
            [
                'title' => 'SUPPRESSION SYSTEM',
                'slug' => 'suppression-system',
                'category_slug' => 'suppression-system',
                'category_name' => 'SUPPRESSION SYSTEM',
                'image' => '/wp-content/uploads/2017/11/fire-suppression-system.jpg',
                'description' => 'Automatic FM-200 and Novec fire suppression system.',
                'specifications' => 'Clean agent fire extinguishing system, zero ozone depletion.',
                'is_featured' => true,
                'status' => 'published',
            ],
            [
                'title' => 'FIRE DETECTION ALARM SYSTEM',
                'slug' => 'fire-detection-alarm-system',
                'category_slug' => 'alarm-systems',
                'category_name' => 'ALARM SYSTEMS',
                'image' => '/wp-content/uploads/2017/05/fire-detection-alarm-system.jpg',
                'description' => 'Addressable fire alarm control panels and smoke detectors.',
                'specifications' => 'EN54 certified addressable panel with dual loop capacity.',
                'is_featured' => true,
                'status' => 'published',
            ],
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(['slug' => $prod['slug']], $prod);
        }

        // 4. Customers Seeder (15 Customer Logos)
        $customers = [
            ['name' => 'Radiant', 'logo_path' => '/wp-content/uploads/2017/11/radiant.png', 'sort_order' => 1],
            ['name' => 'BTI', 'logo_path' => '/wp-content/uploads/2017/11/bti.png', 'sort_order' => 2],
            ['name' => 'BRAC', 'logo_path' => '/wp-content/uploads/2017/11/brac.png', 'sort_order' => 3],
            ['name' => 'BRAC University', 'logo_path' => '/wp-content/uploads/2017/11/bracuni.png', 'sort_order' => 4],
            ['name' => 'Global', 'logo_path' => '/wp-content/uploads/2017/11/global.png', 'sort_order' => 5],
            ['name' => 'Envoy', 'logo_path' => '/wp-content/uploads/2017/11/envoy.png', 'sort_order' => 6],
            ['name' => 'Chung Hua', 'logo_path' => '/wp-content/uploads/2017/11/chunghua.png', 'sort_order' => 7],
            ['name' => 'AFL', 'logo_path' => '/wp-content/uploads/2017/11/afl.png', 'sort_order' => 8],
            ['name' => 'SEEK', 'logo_path' => '/wp-content/uploads/2017/11/seek.png', 'sort_order' => 9],
            ['name' => 'GIS', 'logo_path' => '/wp-content/uploads/2017/11/gis.png', 'sort_order' => 10],
            ['name' => 'Markup', 'logo_path' => '/wp-content/uploads/2017/11/markup.png', 'sort_order' => 11],
            ['name' => 'Meek Sweater', 'logo_path' => '/wp-content/uploads/2017/11/mikey.png', 'sort_order' => 12],
            ['name' => 'Excellent', 'logo_path' => '/wp-content/uploads/2017/11/excelent.png', 'sort_order' => 13],
            ['name' => 'Mondol', 'logo_path' => '/wp-content/uploads/2017/11/mondol.png', 'sort_order' => 14],
            ['name' => 'HQ', 'logo_path' => '/wp-content/uploads/2017/11/hq.png', 'sort_order' => 15],
        ];

        foreach ($customers as $cust) {
            Customer::updateOrCreate(['name' => $cust['name']], $cust);
        }

        // 5. Blog Posts Seeder
        BlogPost::updateOrCreate(
            ['slug' => 'essential-fire-safety-maintenance'],
            [
                'title' => 'Essential Fire Safety Maintenance Rules for Industrial Facilities',
                'summary' => 'Regular maintenance and annual refilling of fire extinguishers are critical line-of-defense measures.',
                'content' => '<p>Fire is a serious threat to physical safety. Regular inspection and refilling using well-equipped workshops guarantee optimal operational readiness.</p>',
                'thumbnail' => '/wp-content/uploads/2017/05/fire-detection-alarm-system.jpg',
                'published_at' => now(),
                'author' => 'N.I. Safety Team',
                'status' => 'published',
            ]
        );
    }
}
