<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Tags\HasTags;

class FollowUp extends Model
{
    use HasTags;

    protected $fillable = [
        'customer_id', 'user_id', 'type', 'follow_up_date',
        'follow_up_time', 'notes', 'status', 'completed_at'
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'follow_up_time' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
