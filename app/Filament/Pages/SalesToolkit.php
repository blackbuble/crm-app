<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\MarketingMaterial;

class SalesToolkit extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Sales Operations';
    protected static ?string $title = 'Sales Assets Gallery';
    protected static ?int $navigationSort = 6;
    
    protected static string $view = 'filament.pages.sales-toolkit';

    public static function canAccess(): bool
    {
        return auth()->user()->can('view_any_marketing_material');
    }

    public $activeTab = 'brochure';

    public function getMaterialsProperty()
    {
        return MarketingMaterial::where('is_active', true)
            ->where('type', $this->activeTab)
            ->orderBy('sort_order')
            ->get();
    }
}
