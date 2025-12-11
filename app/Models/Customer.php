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
    use SoftDeletes, HasTags;

    // app/Models/Customer.php
    // Boot method removed - logic moved to CustomerObserver

    protected $fillable = [
        'type', 'name', 'email', 'phone', 'address',
        'company_name', 'tax_id', 'first_name', 'last_name',
        'notes', 'status', 'assigned_to', 'assigned_at',
    ];

    protected $casts = [
        'type' => 'string',
        'status' => 'string',
    ];

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
        return $this->type === 'company' 
            ? $this->company_name 
            : "{$this->first_name} {$this->last_name}";
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
