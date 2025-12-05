<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quotation_number', 'customer_id', 'user_id', 'quotation_date',
        'valid_until', 'subtotal', 'tax_percentage', 'tax_amount',
        'discount', 'total', 'notes', 'status'
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = self::generateQuotationNumber();
            }

              // Auto-set user_id if not provided
            if (empty($quotation->user_id) && auth()->check()) {
                $quotation->user_id = auth()->id();
            }
        });
    }

    public static function generateQuotationNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastQuotation = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastQuotation ? intval(substr($lastQuotation->quotation_number, -4)) + 1 : 1;
        return "QUO-{$year}{$month}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total');
        $this->tax_amount = ($this->subtotal * $this->tax_percentage) / 100;
        $this->total = $this->subtotal + $this->tax_amount - $this->discount;
        $this->save();
    }
}

