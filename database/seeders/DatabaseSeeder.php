<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Project;
use App\Models\BlogPost;
use App\Models\ClientLogo;
use App\Models\Customer;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Administrator Account
        $adminEmail = env('ADMIN_EMAIL', 'admin@niengineeringbd.com');
        $adminPassword = env('ADMIN_DEFAULT_PASSWORD', 'AdminSecure#NIES2026!');
        
        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'N.I. Administrator',
                'password' => Hash::make($adminPassword),
            ]
        );

        // 1. Site Settings
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

        // 2. Categories Taxonomy
        $categories = [
            [
                'name' => 'Fire Extinguishers',
                'slug' => 'fire-extinguishers',
                'description' => 'ABC Dry Chemical Powder, CO2, and Foam Extinguishers',
                'image' => '/wp-content/uploads/2017/05/fire-extinguishers1.jpg',
                'sort_order' => 1,
            ],
            [
                'name' => 'Suppression Systems',
                'slug' => 'suppression-system',
                'description' => 'Automatic FM-200, Novec 1230, and CO2 Total Flooding Systems',
                'image' => '/wp-content/uploads/2017/11/fire-suppression-system.jpg',
                'sort_order' => 2,
            ],
            [
                'name' => 'Alarm & Detection Systems',
                'slug' => 'alarm-systems',
                'description' => 'Addressable and Conventional Fire Alarm Control Panels & Detectors',
                'image' => '/wp-content/uploads/2017/05/fire-detection-alarm-system.jpg',
                'sort_order' => 3,
            ],
            [
                'name' => 'CCTV Surveillance',
                'slug' => 'cctv-surveillance',
                'description' => 'High-Definition IP CCTV Cameras and NVR Systems',
                'image' => '/wp-content/uploads/2017/11/brac-university.jpg',
                'sort_order' => 4,
            ],
            [
                'name' => 'Access Control',
                'slug' => 'access-control',
                'description' => 'Biometric Fingerprint, RFID Card, and Facial Recognition Systems',
                'image' => '/wp-content/uploads/2017/11/brac-centre-inn-copy.jpg',
                'sort_order' => 5,
            ],
        ];

        $categoryMap = [];
        foreach ($categories as $catData) {
            $cat = Category::updateOrCreate(['slug' => $catData['slug']], $catData);
            $categoryMap[$catData['slug']] = $cat->id;
        }

        // 3. Products Catalog (with authoritative ৳ BDT pricing, SKU, and stock)
        $products = [
            [
                'title' => 'ABC Dry Chemical Powder Extinguisher (6kg)',
                'slug' => 'abc-dry-powder-extinguisher-6kg',
                'sku' => 'EXT-ABC-6KG',
                'category_id' => $categoryMap['fire-extinguishers'] ?? null,
                'category_slug' => 'fire-extinguishers',
                'category_name' => 'Fire Extinguishers',
                'price' => 1450.00,
                'compare_at_price' => 1800.00,
                'stock_quantity' => 120,
                'track_inventory' => true,
                'is_featured' => true,
                'status' => 'published',
                'image' => '/wp-content/uploads/2017/05/fire-extinguishers1.jpg',
                'description' => 'Multipurpose Class A, B, C fire extinguisher filled with 90% MAP powder for commercial and industrial use.',
                'specifications' => '<p>Capacity: 6kg | Working Pressure: 14 Bar | Discharge Duration: 15-18 sec | Certified: BSTI / CE</p>',
            ],
            [
                'title' => 'Carbon Dioxide (CO2) Fire Extinguisher (3kg)',
                'slug' => 'co2-fire-extinguisher-3kg',
                'sku' => 'EXT-CO2-3KG',
                'category_id' => $categoryMap['fire-extinguishers'] ?? null,
                'category_slug' => 'fire-extinguishers',
                'category_name' => 'Fire Extinguishers',
                'price' => 3200.00,
                'compare_at_price' => 3800.00,
                'stock_quantity' => 85,
                'track_inventory' => true,
                'is_featured' => true,
                'status' => 'published',
                'image' => '/wp-content/uploads/2017/05/fire-extinguishers1.jpg',
                'description' => 'Clean gas CO2 extinguisher specially designed for Class B electrical and server room fire protection.',
                'specifications' => '<p>Capacity: 3kg | Cylinder: Seamless Alloy Steel | Valve: Heavy Brass | Non-conductive residue-free</p>',
            ],
            [
                'title' => 'FM-200 Gas Flooding Suppression System',
                'slug' => 'fm200-gas-flooding-suppression-system',
                'sku' => 'SYS-FM200-AUTO',
                'category_id' => $categoryMap['suppression-system'] ?? null,
                'category_slug' => 'suppression-system',
                'category_name' => 'Suppression Systems',
                'price' => 185000.00,
                'compare_at_price' => 210000.00,
                'stock_quantity' => 10,
                'track_inventory' => true,
                'is_featured' => true,
                'status' => 'published',
                'image' => '/wp-content/uploads/2017/11/fire-suppression-system.jpg',
                'description' => 'Automatic clean agent gas flooding suppression system for mission-critical data centers and telecommunication hubs.',
                'specifications' => '<p>Agent: HFC-227ea (FM-200) | Discharge Time: <10 seconds | Zero Ozone Depletion | UL/FM Approved Components</p>',
            ],
            [
                'title' => 'Addressable Fire Alarm Control Panel (4 Loop)',
                'slug' => 'addressable-fire-alarm-control-panel-4-loop',
                'sku' => 'ALM-ADDR-4L',
                'category_id' => $categoryMap['alarm-systems'] ?? null,
                'category_slug' => 'alarm-systems',
                'category_name' => 'Alarm & Detection Systems',
                'price' => 75000.00,
                'compare_at_price' => 88000.00,
                'stock_quantity' => 15,
                'track_inventory' => true,
                'is_featured' => true,
                'status' => 'published',
                'image' => '/wp-content/uploads/2017/05/fire-detection-alarm-system.jpg',
                'description' => 'EN54 certified 4-loop addressable panel supporting up to 1000 device points with LCD touch display and battery backup.',
                'specifications' => '<p>Loops: 4 | Protocol: Digital Protocol | Networkable: Up to 32 panels | Compliance: EN54-2, EN54-4</p>',
            ],
        ];

        foreach ($products as $prodData) {
            Product::updateOrCreate(['slug' => $prodData['slug']], $prodData);
        }

        // 4. Partner Client Logos
        $clientLogos = [
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

        foreach ($clientLogos as $logo) {
            ClientLogo::updateOrCreate(['name' => $logo['name']], $logo);
        }

        // 5. Projects Seeder
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

        // 6. Blog Posts Seeder
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
