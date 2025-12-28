<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingConfig extends Model
{
    protected $fillable = [
        'name',
        'description',
        'config',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get packages from config
     */
    public function getPackages(): array
    {
        return $this->config['packages'] ?? [];
    }

    /**
     * Get addons from config
     */
    public function getAddons(): array
    {
        return $this->config['addons'] ?? [];
    }

    /**
     * Get discount rules from config
     */
    public function getDiscountRules(): array
    {
        return $this->config['discount_rules'] ?? [];
    }

    /**
     * Calculate total based on selected items
     * $selectedAddons is now an array of objects: [['addon_id' => '...', 'quantity' => 1, 'tv_size' => '...', ...]]
     */
    public function calculateTotal(array $selectedPackages, array $selectedAddons, float $customDiscount = 0, float $packageDiscount = 0): array
    {
        $packages = $this->getPackages();
        $addons = $this->getAddons();
        $discountRules = $this->getDiscountRules();

        $subtotal = 0;
        $packagesSubtotal = 0;
        $breakdown = [
            'packages' => [],
            'addons' => [],
        ];

        // Calculate packages
        foreach ($selectedPackages as $packageId) {
            $package = collect($packages)->firstWhere('id', $packageId);
            if ($package) {
                $subtotal += $package['price'];
                $packagesSubtotal += $package['price'];
                $breakdown['packages'][] = [
                    'name' => $package['name'],
                    'price' => $package['price'],
                ];
            }
        }

        // Calculate addons
        foreach ($selectedAddons as $selection) {
            $addonId = $selection['addon_id'] ?? null;
            $quantity = intval($selection['quantity'] ?? 1);
            
            if (!$addonId) continue;

            $addon = collect($addons)->firstWhere('id', $addonId);
            if ($addon) {
                $price = floatval($addon['price'] ?? 0);
                $name = $addon['name'];
                $details = "";

                // Special Logic for TV Size
                if (str_contains(strtolower($addonId), 'tv') && !empty($selection['tv_size'])) {
                    $size = $selection['tv_size'];
                    // Based on provided list: 50": 1.225.000, 60": 1.555.000, 65": 1.750.000
                    // We treat 50" as base or use fixed mapping if base is 0
                    $sizePrices = [
                        '50' => 1225000,
                        '60' => 1555000,
                        '65' => 1750000,
                    ];
                    if (isset($sizePrices[$size])) {
                        $price = $sizePrices[$size]; // Use absolute price from list
                    }
                    $details = " ({$size} inch)";
                }

                // Special Logic for WA Blast (Now uses simple multiplier/quantity)
                if (str_contains(strtolower($addonId), 'wa_blast')) {
                    $details = " (per 100 messages)";
                }

                // Special Logic for Combo Items
                if (str_contains(strtolower($addonId), 'combo')) {
                    $details = " (Akad + Resepsi)";
                }

                $addonTotal = $price * $quantity;
                $subtotal += $addonTotal;
                
                $breakdown['addons'][] = [
                    'name' => $name . $details,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $addonTotal,
                ];
            }
        }

        // Apply automatic discount rules (Only applies to Packages)
        $autoDiscount = 0;
        foreach ($discountRules as $rule) {
            if ($this->matchesRule($rule, $selectedPackages, $selectedAddons, $packagesSubtotal)) {
                $autoDiscount = max($autoDiscount, $this->calculateRuleDiscount($rule, $packagesSubtotal));
            }
        }

        // Calculate package discount amount based on percentage
        $packageDiscountAmount = ($packagesSubtotal * floatval($packageDiscount)) / 100;

        $totalDiscount = $autoDiscount + $customDiscount + $packageDiscountAmount;
        $total = max(0, $subtotal - $totalDiscount);

        return [
            'subtotal' => $subtotal,
            'packages_subtotal' => $packagesSubtotal,
            'auto_discount' => $autoDiscount,
            'custom_discount' => $customDiscount,
            'package_discount' => $packageDiscountAmount,
            'package_discount_percent' => $packageDiscount,
            'total_discount' => $totalDiscount,
            'total' => $total,
            'breakdown' => $breakdown,
        ];
    }

    private function matchesRule(array $rule, array $packages, array $addons, float $packagesSubtotal): bool
    {
        $type = $rule['type'] ?? 'none';

        switch ($type) {
            case 'minimum_spend':
                // Rule only applies to packages subtotal
                return $packagesSubtotal >= ($rule['minimum'] ?? 0);
            
            case 'package_count':
                return count($packages) >= ($rule['minimum_packages'] ?? 0);
            
            case 'specific_package':
                return in_array($rule['package_id'] ?? null, $packages);
            
            default:
                return false;
        }
    }

    private function calculateRuleDiscount(array $rule, float $packagesSubtotal): float
    {
        $discountType = $rule['discount_type'] ?? 'percentage';
        $discountValue = $rule['discount_value'] ?? 0;

        if ($discountType === 'percentage') {
            // Percent discount calculated from packages only
            return ($packagesSubtotal * $discountValue) / 100;
        }

        return $discountValue; // Fixed amount
    }
}
