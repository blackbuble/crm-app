<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Quotation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCustomers = Customer::count();
        $newThisMonth = Customer::whereMonth('created_at', now()->month)->count();
        $lastMonthCount = Customer::whereMonth('created_at', now()->subMonth()->month)->count();
        $customerGrowth = $lastMonthCount > 0 
            ? round((($newThisMonth - $lastMonthCount) / $lastMonthCount) * 100, 1) 
            : 100;

        // Status breakdown
        $leads = Customer::where('status', 'lead')->count();
        $prospects = Customer::where('status', 'prospect')->count();
        $activeCustomers = Customer::where('status', 'customer')->count();
        
        // Follow-ups
        $pendingFollowUps = FollowUp::where('status', 'pending')
            ->where('follow_up_date', '>=', now())
            ->count();
        $overdueFollowUps = FollowUp::where('status', 'pending')
            ->where('follow_up_date', '<', now())
            ->count();

        // Quotations
        $totalQuotations = Quotation::sum('total');
        $acceptedQuotations = Quotation::where('status', 'accepted')->sum('total');
        $conversionRate = $totalQuotations > 0 
            ? round(($acceptedQuotations / $totalQuotations) * 100, 1) 
            : 0;

        return [
            Stat::make('Total Customers', $totalCustomers)
                ->description($customerGrowth >= 0 ? "{$customerGrowth}% increase" : "{$customerGrowth}% decrease")
                ->descriptionIcon($customerGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($customerGrowth >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
            
            Stat::make('Pipeline', $leads + $prospects)
                ->description("Leads: {$leads} | Prospects: {$prospects}")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            
            Stat::make('Follow-ups', $pendingFollowUps)
                ->description($overdueFollowUps > 0 ? "{$overdueFollowUps} overdue" : "All up to date")
                ->descriptionIcon($overdueFollowUps > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($overdueFollowUps > 0 ? 'warning' : 'success'),
            
            Stat::make('Total Quotations', 'Rp ' . number_format($totalQuotations, 0, ',', '.'))
                ->description("{$conversionRate}% conversion rate")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([3, 5, 7, 8, 9, 12, 15, 18]),
        ];
    }
}
