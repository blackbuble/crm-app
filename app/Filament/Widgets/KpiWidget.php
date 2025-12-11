<?php
// app/Filament/Widgets/KpiWidget.php - Updated with role-based visibility
namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Quotation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class KpiWidget extends BaseWidget
{
    use HasWidgetShield;
    
    protected static ?int $sort = 0;
    protected ?string $heading = 'Key Performance Indicators';
    
    protected function getStats(): array
    {
        $user = auth()->user();
        
        // Date ranges
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        
        // Customer KPIs
        $totalCustomers = $this->getCustomerQuery()->count();
        $newThisMonth = $this->getCustomerQuery()
            ->where('created_at', '>=', $thisMonth)
            ->count();
        $newLastMonth = $this->getCustomerQuery()
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->count();
        
        $customerGrowth = $newLastMonth > 0 
            ? round((($newThisMonth - $newLastMonth) / $newLastMonth) * 100, 1) 
            : 100;

        // Conversion Rate (Lead -> Customer)
        $totalLeads = $this->getCustomerQuery()
            ->where('status', 'lead')
            ->where('created_at', '>=', $thisMonth)
            ->count();
        $convertedCustomers = $this->getCustomerQuery()
            ->where('status', 'customer')
            ->where('updated_at', '>=', $thisMonth)
            ->count();
        $conversionRate = $totalLeads > 0 
            ? round(($convertedCustomers / $totalLeads) * 100, 1) 
            : 0;

        // Follow-up KPIs
        $pendingFollowUps = $this->getFollowUpQuery()
            ->where('status', 'pending')
            ->where('follow_up_date', '>=', $today)
            ->count();
        $overdueFollowUps = $this->getFollowUpQuery()
            ->where('status', 'pending')
            ->where('follow_up_date', '<', $today)
            ->count();
        $todayFollowUps = $this->getFollowUpQuery()
            ->where('status', 'pending')
            ->whereDate('follow_up_date', $today)
            ->count();
        
        // Response Time (avg days to complete follow-up)
        $avgResponseTime = $this->getFollowUpQuery()
            ->where('status', 'completed')
            ->where('completed_at', '>=', $thisMonth)
            ->selectRaw('AVG(DATEDIFF(completed_at, created_at)) as avg_days')
            ->value('avg_days');
        $avgResponseTime = round($avgResponseTime ?? 0, 1);

        // Quotation KPIs
        $totalQuotationsValue = $this->getQuotationQuery()
            ->where('quotation_date', '>=', $thisMonth)
            ->sum('total');
        $acceptedQuotationsValue = $this->getQuotationQuery()
            ->where('status', 'accepted')
            ->where('quotation_date', '>=', $thisMonth)
            ->sum('total');
        $quotationCount = $this->getQuotationQuery()
            ->where('quotation_date', '>=', $thisMonth)
            ->count();
        $acceptedCount = $this->getQuotationQuery()
            ->where('status', 'accepted')
            ->where('quotation_date', '>=', $thisMonth)
            ->count();
        
        $winRate = $quotationCount > 0 
            ? round(($acceptedCount / $quotationCount) * 100, 1) 
            : 0;
        
        // Average Deal Size
        $avgDealSize = $acceptedCount > 0 
            ? $acceptedQuotationsValue / $acceptedCount 
            : 0;

        // Pipeline Value (all non-rejected quotations)
        $pipelineValue = $this->getQuotationQuery()
            ->whereIn('status', ['draft', 'sent'])
            ->sum('total');

        return [
            Stat::make('Total Customers', number_format($totalCustomers))
                ->description($customerGrowth >= 0 ? "{$customerGrowth}% increase" : "{$customerGrowth}% decrease")
                ->descriptionIcon($customerGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($customerGrowth >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
            
            Stat::make('Conversion Rate', $conversionRate . '%')
                ->description("This month: {$convertedCustomers} converted")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($conversionRate >= 20 ? 'success' : 'warning'),
            
            Stat::make('Follow-ups Today', $todayFollowUps)
                ->description($overdueFollowUps > 0 ? "{$overdueFollowUps} overdue" : "All up to date")
                ->descriptionIcon($overdueFollowUps > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($overdueFollowUps > 0 ? 'danger' : 'success'),
            
            Stat::make('Avg Response Time', $avgResponseTime . ' days')
                ->description("This month")
                ->descriptionIcon('heroicon-m-clock')
                ->color($avgResponseTime <= 3 ? 'success' : 'warning'),
            
            Stat::make('Win Rate', $winRate . '%')
                ->description("{$acceptedCount} of {$quotationCount} quotes")
                ->descriptionIcon('heroicon-m-trophy')
                ->color($winRate >= 30 ? 'success' : 'warning'),
            
            Stat::make('Avg Deal Size', format_currency($avgDealSize))
                ->description("This month")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
            
            Stat::make('Pipeline Value', format_currency($pipelineValue))
                ->description("Active quotations")
                ->descriptionIcon('heroicon-m-funnel')
                ->color('info')
                ->chart([5, 7, 9, 12, 15, 18, 22, 25]),
            
            Stat::make('Revenue This Month', format_currency($acceptedQuotationsValue))
                ->description("From {$acceptedCount} deals")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([3, 5, 7, 8, 9, 12, 15, 18]),
        ];
    }

    protected function getCustomerQuery()
    {
        $user = auth()->user();
        $query = Customer::query();
        
        // Show all customers for admin/manager roles
        if (!$user->hasAnyRole(['super_admin', 'sales_manager'])) {
            $query->where('assigned_to', $user->id);
        }
        
        return $query;
    }

    protected function getFollowUpQuery()
    {
        $user = auth()->user();
        $query = FollowUp::query();
        
        if (!$user->hasAnyRole(['super_admin', 'sales_manager'])) {
            $query->where('user_id', $user->id);
        }
        
        return $query;
    }

    protected function getQuotationQuery()
    {
        $user = auth()->user();
        $query = Quotation::query();
        
        if (!$user->hasAnyRole(['super_admin', 'sales_manager'])) {
            $query->where('user_id', $user->id);
        }
        
        return $query;
    }
}