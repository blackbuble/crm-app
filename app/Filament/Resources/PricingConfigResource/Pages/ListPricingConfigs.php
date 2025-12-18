<?php

namespace App\Filament\Resources\PricingConfigResource\Pages;

use App\Filament\Resources\PricingConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPricingConfigs extends ListRecords
{
    protected static string $resource = PricingConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
