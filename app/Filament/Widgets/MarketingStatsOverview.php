<?php

namespace App\Filament\Widgets;

use App\Models\AdSpend;
use App\Models\Quotation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class MarketingStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected ?string $heading = 'Marketing Overview';

    protected function getStats(): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $totalSpend = AdSpend::whereBetween('date', [$startDate, $endDate])->sum('amount');
        
        // Sources that are considered "Paid Marketing"
        $adSources = [
            'Meta Ads', 
            'Google Ads', 
            'TikTok Ads', 
            'LinkedIn Ads', 
            'Twitter Ads', 
            'Other'
        ];
        
        $revenue = Quotation::where('status', 'accepted')
            ->whereBetween('quotation_date', [$startDate, $endDate])
            ->whereHas('customer', function ($query) use ($adSources) {
                $query->whereIn('source', $adSources);
            })
            ->sum('total');

        $roas = $totalSpend > 0 ? ($revenue / $totalSpend) : 0;
        $leads = AdSpend::whereBetween('date', [$startDate, $endDate])->sum('leads');

        return [
            Stat::make('Total Ad Spend', format_currency($totalSpend))
                ->description('This Month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),

            Stat::make('Ad Revenue', format_currency($revenue))
                ->description('From Ad Sources (This Month)')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('ROAS', number_format($roas, 2) . 'x')
                ->description('Return on Ad Spend')
                ->color($roas >= 4 ? 'success' : ($roas >= 2 ? 'warning' : 'danger')),
            
            Stat::make('Total Leads', $leads)
                ->description('From Ads (This Month)')
                ->color('info'),
        ];
    }
}
