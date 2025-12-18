<?php

namespace App\Filament\Resources\PricingConfigResource\Pages;

use App\Filament\Resources\PricingConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePricingConfig extends CreateRecord
{
    protected static string $resource = PricingConfigResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = auth()->id();
        
        // Ensure config is valid JSON
        if (is_string($data['config'])) {
            $data['config'] = json_decode($data['config'], true);
        }
        
        return $data;
    }
}
