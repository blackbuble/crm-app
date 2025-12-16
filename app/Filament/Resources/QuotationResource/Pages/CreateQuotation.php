<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            // Serialize quotation number generation
            $lockKey = 'quotation_creation';
            // Wait up to 10 seconds for the lock
            $acquired = \Illuminate\Support\Facades\DB::scalar("SELECT GET_LOCK(?, 10)", [$lockKey]);
            
            if (! $acquired) {
                throw new \Exception('System busy (Quotation Generator), please try again.');
            }
            
            try {
                // Generate manually inside the lock to ensure sequence integrity
                $data['quotation_number'] = \App\Models\Quotation::generateQuotationNumber();
                
                return static::getModel()::create($data);
            } finally {
                \Illuminate\Support\Facades\DB::scalar("SELECT RELEASE_LOCK(?)", [$lockKey]);
            }
        });
    }
}
