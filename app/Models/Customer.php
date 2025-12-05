<?php
// app/Models/Customer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Tags\HasTags;

class Customer extends Model
{
    use SoftDeletes, HasTags;

    protected $fillable = [
        'type', 'name', 'email', 'phone', 'address',
        'company_name', 'tax_id', 'first_name', 'last_name',
        'notes', 'status'
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
}
