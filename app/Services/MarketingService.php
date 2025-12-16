<?php

namespace App\Services;

use App\Models\AdSpend;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MarketingService
{
    /**
     * Sync local conversion data (Leads) from Customers table to AdSpends table.
     * Matches based on Date and Source/Platform.
     */
    public function syncLocalConversions()
    {
        // 1. Get all AdSpends
        $adSpends = AdSpend::all();

        foreach ($adSpends as $spend) {
            $date = Carbon::parse($spend->date)->toDateString();
            
            // Map Platform names to UTM Sources
            $sources = $this->mapPlatformToUtmSource($spend->platform);
            
            // Query Customers count
            $leadsCount = Customer::whereDate('created_at', $date)
                ->where(function($q) use ($sources, $spend) {
                    // Match by UTM Source mapping
                    $q->whereIn('utm_source', $sources)
                      ->orWhere('source', 'LIKE', "%{$spend->platform}%"); // Fallback to 'source' column
                      
                    // Optional: If campaign name is set in AdSpend, try to match it too
                    if (!empty($spend->campaign_name)) {
                        $q->orWhere('utm_campaign', $spend->campaign_name)
                          ->orWhere('gad_campaign', $spend->campaign_name);
                    }
                })
                ->count();

            // Update record if different
            if ($spend->leads !== $leadsCount) {
                $spend->update(['leads' => $leadsCount]);
            }
        }
    }

    private function mapPlatformToUtmSource(string $platform): array
    {
        $platform = strtolower($platform);
        
        if (str_contains($platform, 'meta') || str_contains($platform, 'facebook') || str_contains($platform, 'instagram')) {
            return ['facebook', 'instagram', 'meta', 'fb', 'ig'];
        }
        
        if (str_contains($platform, 'google')) {
            return ['google', 'youtube', 'gdn', 'search'];
        }
        
        if (str_contains($platform, 'tiktok')) {
            return ['tiktok'];
        }
        
        if (str_contains($platform, 'linkedin')) {
            return ['linkedin'];
        }
        
        if (str_contains($platform, 'twitter') || str_contains($platform, 'x')) {
            return ['twitter', 'x'];
        }

        return [strtolower($platform)];
    }
}
