<?php
// app/Models/KpiTarget.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class KpiTarget extends Model
{
    // Boot method removed - logic moved to KpiTargetObserver

    protected $fillable = [
        'user_id',
        'created_by',
        'period_type',
        'year',
        'period',
        'revenue_target',
        'new_customers_target',
        'quotations_target',
        'conversion_rate_target',
        'followups_target',
        'win_rate_target',
        'actual_revenue',
        'actual_new_customers',
        'actual_quotations',
        'actual_conversion_rate',
        'actual_followups',
        'actual_win_rate',
        'achievement_percentage',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'period' => 'integer',
        'revenue_target' => 'decimal:2',
        'new_customers_target' => 'integer',
        'quotations_target' => 'integer',
        'conversion_rate_target' => 'decimal:2',
        'followups_target' => 'integer',
        'win_rate_target' => 'decimal:2',
        'actual_revenue' => 'decimal:2',
        'actual_new_customers' => 'integer',
        'actual_quotations' => 'integer',
        'actual_conversion_rate' => 'decimal:2',
        'actual_followups' => 'integer',
        'actual_win_rate' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPeriodLabelAttribute(): string
    {
        return match($this->period_type) {
            'monthly' => Carbon::create($this->year, $this->period)->format('F Y'),
            'quarterly' => "Q{$this->period} {$this->year}",
            'yearly' => (string) $this->year,
        };
    }

    public function getDateRangeAttribute(): array
    {
        return match($this->period_type) {
            'monthly' => [
                Carbon::create($this->year, $this->period)->startOfMonth(),
                Carbon::create($this->year, $this->period)->endOfMonth(),
            ],
            'quarterly' => [
                Carbon::create($this->year, ($this->period - 1) * 3 + 1)->startOfMonth(),
                Carbon::create($this->year, $this->period * 3)->endOfMonth(),
            ],
            'yearly' => [
                Carbon::create($this->year, 1)->startOfYear(),
                Carbon::create($this->year, 12)->endOfYear(),
            ],
        };
    }

    public function calculateActuals(): void
    {
        [$start, $end] = $this->date_range;
        
        // Revenue
        $this->actual_revenue = Quotation::where('user_id', $this->user_id)
            ->where('status', 'accepted')
            ->whereBetween('quotation_date', [$start, $end])
            ->sum('total');
        
        // New Customers
        $this->actual_new_customers = Customer::where('assigned_to', $this->user_id)
            ->whereBetween('created_at', [$start, $end])
            ->count();
        
        // Quotations
        $this->actual_quotations = Quotation::where('user_id', $this->user_id)
            ->whereBetween('quotation_date', [$start, $end])
            ->count();
        
        // Follow-ups
        $this->actual_followups = FollowUp::where('user_id', $this->user_id)
            ->where('status', 'completed')
            ->whereBetween('follow_up_date', [$start, $end])
            ->count();
        
        // Conversion Rate
        $totalLeads = Customer::where('assigned_to', $this->user_id)
            ->where('status', 'lead')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        
        $convertedCustomers = Customer::where('assigned_to', $this->user_id)
            ->where('status', 'customer')
            ->whereBetween('updated_at', [$start, $end])
            ->count();
        
        $this->actual_conversion_rate = $totalLeads > 0 
            ? round(($convertedCustomers / $totalLeads) * 100, 2) 
            : 0;
        
        // Win Rate
        $totalQuotes = Quotation::where('user_id', $this->user_id)
            ->whereBetween('quotation_date', [$start, $end])
            ->count();
        
        $acceptedQuotes = Quotation::where('user_id', $this->user_id)
            ->where('status', 'accepted')
            ->whereBetween('quotation_date', [$start, $end])
            ->count();
        
        $this->actual_win_rate = $totalQuotes > 0 
            ? round(($acceptedQuotes / $totalQuotes) * 100, 2) 
            : 0;
        
        // Calculate overall achievement
        $achievements = [];
        
        if ($this->revenue_target > 0) {
            $achievements[] = ($this->actual_revenue / $this->revenue_target) * 100;
        }
        if ($this->new_customers_target > 0) {
            $achievements[] = ($this->actual_new_customers / $this->new_customers_target) * 100;
        }
        if ($this->quotations_target > 0) {
            $achievements[] = ($this->actual_quotations / $this->quotations_target) * 100;
        }
        
        $this->achievement_percentage = count($achievements) > 0 
            ? round(array_sum($achievements) / count($achievements), 2) 
            : 0;
        
        $this->save();
    }
}