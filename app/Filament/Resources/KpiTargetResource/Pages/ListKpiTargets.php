<?php

namespace App\Filament\Resources\KpiTargetResource\Pages;

use App\Filament\Resources\KpiTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKpiTargets extends ListRecords
{
    protected static string $resource = KpiTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
