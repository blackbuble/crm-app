<?php

namespace App\Filament\Resources\AdSpendResource\Pages;

use App\Filament\Resources\AdSpendResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdSpends extends ListRecords
{
    protected static string $resource = AdSpendResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_leads')
                ->label('Sync Leads')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $service = new \App\Services\MarketingService();
                    $service->syncLocalConversions();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Synced Successfully')
                        ->body('Lead counts have been updated from Customer data.')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
