<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingMaterial extends Model
{
    use \App\Traits\HashIdTrait, \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'title',
        'type',
        'description',
        'file_path',
        'content',
        'thumbnail_path',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getTypes(): array
    {
        return [
            'brochure' => 'Brochure / Flyer',
            'price_list' => 'Price List',
            'presentation' => 'Presentation Deck',
            'script' => 'Sales Script (Copy/Paste)',
            'contract' => 'Contract Template',
            'calculator' => 'Calculator (Link)',
            'other' => 'Other',
        ];
    }
}
