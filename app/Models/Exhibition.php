<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exhibition extends Model
{
    use SoftDeletes, \App\Traits\HashIdTrait, \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'name',
        'location',
        'start_date',
        'end_date',
        'booth_cost',
        'operational_cost',
        'description',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'booth_cost' => 'decimal:2',
        'operational_cost' => 'decimal:2',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalCostAttribute()
    {
        return $this->booth_cost + $this->operational_cost;
    }

    public function getTotalRevenueAttribute()
    {
        return $this->quotations()
            ->where('status', 'accepted')
            ->sum('total');
    }

    public function getRoiAttribute()
    {
        $cost = $this->total_cost;
        if ($cost <= 0) return 0;
        
        return (($this->total_revenue - $cost) / $cost) * 100;
    }
}
