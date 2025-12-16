<?php
// app/Models/Customer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Tags\HasTags;

class Customer extends Model
{
    use SoftDeletes, HasTags, \App\Traits\HashIdTrait, \Illuminate\Database\Eloquent\Factories\HasFactory;

    // app/Models/Customer.php
    // Boot method removed - logic moved to CustomerObserver

    protected $fillable = [
        'type', 'name', 'email', 'phone', 'address',
        'company_name', 'tax_id', 'first_name', 'last_name',
        'notes', 'status', 'assigned_to', 'assigned_at',
        'country', 'country_code', 'source',
        'utm_source', 'utm_medium', 'utm_campaign', 
        'utm_term', 'utm_content', 'gclid', 'fbclid',
        'gad_source', 'gad_campaign', 'gbraid', 'wbraid',
        'exhibition_id',
    ];

    protected $casts = [
        'type' => 'string',
        'status' => 'string',
    ];

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $val = $this->type === 'company' 
            ? $this->company_name 
            : "{$this->first_name} {$this->last_name}";
            
        return trim((string) $val) ?: (string) $this->name;
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CustomerAssignment::class);
    }
}
