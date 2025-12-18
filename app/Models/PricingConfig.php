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
     */
    public function calculateTotal(array $selectedPackages, array $selectedAddons, float $customDiscount = 0): array
    {
        $packages = $this->getPackages();
        $addons = $this->getAddons();
        $discountRules = $this->getDiscountRules();

        $subtotal = 0;
        $breakdown = [
            'packages' => [],
            'addons' => [],
        ];

        // Calculate packages
        foreach ($selectedPackages as $packageId) {
            $package = collect($packages)->firstWhere('id', $packageId);
            if ($package) {
                $subtotal += $package['price'];
                $breakdown['packages'][] = [
                    'name' => $package['name'],
                    'price' => $package['price'],
                ];
            }
        }

        // Calculate addons
        foreach ($selectedAddons as $addonId) {
            $addon = collect($addons)->firstWhere('id', $addonId);
            if ($addon) {
                $subtotal += $addon['price'];
                $breakdown['addons'][] = [
                    'name' => $addon['name'],
                    'price' => $addon['price'],
                ];
            }
        }

        // Apply automatic discount rules
        $autoDiscount = 0;
        foreach ($discountRules as $rule) {
            if ($this->matchesRule($rule, $selectedPackages, $selectedAddons, $subtotal)) {
                $autoDiscount = max($autoDiscount, $this->calculateRuleDiscount($rule, $subtotal));
            }
        }

        $totalDiscount = $autoDiscount + $customDiscount;
        $total = max(0, $subtotal - $totalDiscount);

        return [
            'subtotal' => $subtotal,
            'auto_discount' => $autoDiscount,
            'custom_discount' => $customDiscount,
            'total_discount' => $totalDiscount,
            'total' => $total,
            'breakdown' => $breakdown,
        ];
    }

    private function matchesRule(array $rule, array $packages, array $addons, float $subtotal): bool
    {
        $type = $rule['type'] ?? 'none';

        switch ($type) {
            case 'minimum_spend':
                return $subtotal >= ($rule['minimum'] ?? 0);
            
            case 'package_count':
                return count($packages) >= ($rule['minimum_packages'] ?? 0);
            
            case 'specific_package':
                return in_array($rule['package_id'] ?? null, $packages);
            
            default:
                return false;
        }
    }

    private function calculateRuleDiscount(array $rule, float $subtotal): float
    {
        $discountType = $rule['discount_type'] ?? 'percentage';
        $discountValue = $rule['discount_value'] ?? 0;

        if ($discountType === 'percentage') {
            return ($subtotal * $discountValue) / 100;
        }

        return $discountValue; // Fixed amount
    }
}
