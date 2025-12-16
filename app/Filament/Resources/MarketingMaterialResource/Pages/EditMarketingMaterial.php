<?php

namespace App\Filament\Resources\MarketingMaterialResource\Pages;

use App\Filament\Resources\MarketingMaterialResource;
use Filament\Resources\Pages\EditRecord;

class EditMarketingMaterial extends EditRecord
{
    protected static string $resource = MarketingMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
