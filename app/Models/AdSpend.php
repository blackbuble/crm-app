<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSpend extends Model
{
    protected $fillable = [
        'platform',
        'campaign_name',
        'date',
        'amount',
        'impressions',
        'clicks',
        'leads',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'leads' => 'integer',
    ];
}
