<?php
// app/Filament/Widgets/SalesRepStatsWidget.php
namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Quotation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class SalesRepStatsWidget extends BaseWidget
{
    use HasWidgetShield;
    
    protected static ?int $sort = 0;
    protected ?string $heading = 'My Performance';
    
    protected function getStats(): array
    {
        $user = auth()->user();
        $thisMonth = Carbon::now()->startOfMonth();
        
        // My Customers
        $myCustomers = Customer::where('assigned_to', $user->id)->count();
        $newThisMonth = Customer::where('assigned_to', $user->id)
            ->where('created_at', '>=', $thisMonth)
            ->count();
        
        // My Follow-ups
        $pendingFollowUps = FollowUp::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $todayFollowUps = FollowUp::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereDate('follow_up_date', Carbon::today())
            ->count();
        
        // My Quotations
        $myQuotations = Quotation::where('user_id', $user->id)
            ->where('quotation_date', '>=', $thisMonth)
            ->sum('total');
        $acceptedQuotations = Quotation::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->where('quotation_date', '>=', $thisMonth)
            ->sum('total');
        
        // My Win Rate
        $totalQuotes = Quotation::where('user_id', $user->id)
            ->where('quotation_date', '>=', $thisMonth)
            ->count();
        $acceptedCount = Quotation::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->where('quotation_date', '>=', $thisMonth)
            ->count();
        $winRate = $totalQuotes > 0 ? round(($acceptedCount / $totalQuotes) * 100, 1) : 0;

        return [
            Stat::make('My Customers', $myCustomers)
                ->description("{$newThisMonth} new this month")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            
            Stat::make('Today\'s Follow-ups', $todayFollowUps)
                ->description("{$pendingFollowUps} total pending")
                ->descriptionIcon('heroicon-m-clock')
                ->color($todayFollowUps > 0 ? 'warning' : 'success'),
            
            Stat::make('My Revenue', format_currency($acceptedQuotations))
                ->description("This month")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            
            Stat::make('My Win Rate', $winRate . '%')
                ->description("{$acceptedCount} of {$totalQuotes} quotes")
                ->descriptionIcon('heroicon-m-trophy')
                ->color($winRate >= 30 ? 'success' : 'warning'),
        ];
    }
}