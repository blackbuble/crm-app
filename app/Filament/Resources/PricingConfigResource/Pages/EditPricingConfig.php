<?php

namespace App\Filament\Resources\PricingConfigResource\Pages;

use App\Filament\Resources\PricingConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPricingConfig extends EditRecord
{
    protected static string $resource = PricingConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert config array to JSON string for editing
        if (isset($data['config']) && is_array($data['config'])) {
            $data['config'] = json_encode($data['config'], JSON_PRETTY_PRINT);
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert JSON string back to array
        if (is_string($data['config'])) {
            $data['config'] = json_decode($data['config'], true);
        }
        
        return $data;
    }
}
