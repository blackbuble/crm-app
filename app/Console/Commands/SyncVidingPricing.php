<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PricingConfig;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class SyncVidingPricing extends Command
{
    protected $signature = 'viding:sync-pricing';
    protected $description = 'Sync pricing from Official Viding API';

    public function handle()
    {
        $this->info('Fetching data from Viding API...');
        
        $response = Http::get('https://global.viding.co/api/id/products?locale=id');
        
        if (!$response->successful()) {
            $this->error('Failed to fetch data from API');
            return 1;
        }

        $data = $response->json()['data'] ?? [];
        
        if (empty($data)) {
            $this->error('No data found in API response');
            return 1;
        }

        $packages = [];
        foreach ($data as $product) {
            $packages[] = [
                'id' => 'viding_' . $product['id'],
                'name' => $product['category_name'] . ' - ' . $product['name'],
                'description' => $product['name'] . ' package for ' . $product['category_name'],
                'price' => (float) $product['discounted_price'],
                'features' => collect($product['features'] ?? [])->pluck('name')->toArray(),
                'category' => $product['category_name']
            ];
        }

        // New Addons from provided list with Categories
        $addons = [
            ['id' => 'addon_live_bronze', 'name' => 'Live Streaming - Bronze', 'price' => 925000, 'category' => 'Live Streaming'],
            ['id' => 'addon_live_silver', 'name' => 'Live Streaming - Silver', 'price' => 1312500, 'category' => 'Live Streaming'],
            ['id' => 'addon_live_gold', 'name' => 'Live Streaming - Gold', 'price' => 1562500, 'category' => 'Live Streaming'],
            ['id' => 'addon_live_zoom', 'name' => 'Live Streaming - Zoom + Operator', 'price' => 1350000, 'category' => 'Live Streaming'],
            ['id' => 'addon_inv_domain', 'name' => 'Digital Invitation - Custom Domain', 'price' => 350000, 'category' => 'Digital Invitation'],
            ['id' => 'addon_inv_egift', 'name' => 'Digital Invitation - E-Gift', 'price' => 350000, 'category' => 'Digital Invitation'],
            ['id' => 'addon_wa_blast', 'name' => 'Auto Blast WA (per 100)', 'price' => 100000, 'category' => 'Marketing'],
            ['id' => 'addon_projector', 'name' => 'Proyektor + Screen', 'price' => 2600000, 'category' => 'Hardware'],
            ['id' => 'addon_livecam_1', 'name' => 'Live Cam Only - 1 Camera', 'price' => 3100000, 'category' => 'Live Cam'],
            ['id' => 'addon_livecam_2', 'name' => 'Live Cam Only - 2 Camera', 'price' => 4100000, 'category' => 'Live Cam'],
            ['id' => 'addon_usher', 'name' => 'Usher (4 Hours, Makeup & Gown)', 'price' => 800000, 'category' => 'Staff'],
            ['id' => 'addon_tab_scanner', 'name' => 'Tab Scanner', 'price' => 555000, 'category' => 'Hardware'],
            ['id' => 'addon_tv_rental', 'name' => 'TV Rental (Select Size)', 'price' => 0, 'category' => 'Hardware'],
            ['id' => 'addon_tv_label', 'name' => 'TV Print Label', 'price' => 200000, 'category' => 'Hardware'],
            ['id' => 'addon_layar_sapa', 'name' => 'Layar Sapa (Feature Only)', 'price' => 0, 'category' => 'Features'],
            // Combo Items
            ['id' => 'combo_buku_tamu', 'name' => 'Combo Buku Tamu Digital (Akad + Resepsi)', 'price' => 1300000, 'category' => 'Combo'],
            ['id' => 'combo_live_bronze', 'name' => 'Combo Live Streaming Bronze (Akad + Resepsi)', 'price' => 2150000, 'category' => 'Combo'],
            ['id' => 'combo_live_silver', 'name' => 'Combo Live Streaming Silver (Akad + Resepsi)', 'price' => 3050000, 'category' => 'Combo'],
            ['id' => 'combo_live_gold', 'name' => 'Combo Live Streaming Gold (Akad + Resepsi)', 'price' => 3750000, 'category' => 'Combo'],
        ];

        $discountRules = [
            [
                'type' => 'minimum_spend',
                'minimum' => 10000000,
                'discount_type' => 'percentage',
                'discount_value' => 5,
                'description' => '5% off for orders above 10M'
            ],
            [
                'type' => 'package_count',
                'minimum_packages' => 2,
                'discount_type' => 'fixed',
                'discount_value' => 500000,
                'description' => '500k off when buying 2+ packages'
            ]
        ];

        $configData = [
            'packages' => $packages,
            'addons' => $addons,
            'discount_rules' => $discountRules
        ];

        $admin = User::first();

        $config = PricingConfig::updateOrCreate(
            ['name' => 'Viding Official API Pricing'],
            [
                'description' => 'Pricing configuration synced from global.viding.co API',
                'config' => $configData,
                'is_active' => true,
                'uploaded_by' => $admin?->id
            ]
        );

        // Deactivate others? Maybe not, user might want to switch
        // PricingConfig::where('id', '!=', $config->id)->update(['is_active' => false]);

        $this->info('Successfully synced ' . count($packages) . ' packages from Viding API.');
        return 0;
    }
}
