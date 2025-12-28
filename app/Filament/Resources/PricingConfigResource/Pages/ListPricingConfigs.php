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
            \Filament\Actions\Action::make('sync_api')
                ->label('Sync with Viding API')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(function () {
                    try {
                        $response = \Illuminate\Support\Facades\Http::get('https://global.viding.co/api/id/products?locale=id');
                        
                        if (!$response->successful()) {
                            throw new \Exception('Failed to fetch data from API');
                        }

                        $data = $response->json()['data'] ?? [];
                        
                        if (empty($data)) {
                            throw new \Exception('No data found in API response');
                        }

                        $packages = [];
                        foreach ($data as $product) {
                            $packages[] = [
                                'id' => 'viding_' . $product['id'],
                                'name' => $product['category_name'] . ' - ' . $product['name'],
                                'description' => $product['name'] . ' package for ' . $product['category_name'],
                                'price' => (float) $product['discounted_price'],
                                'features' => collect($product['features'] ?? [])->pluck('name')->toArray(),
                                'category' => $product['category_name']
                            ];
                        }

                        $addons = [
                            ['id' => 'addon_usher', 'name' => 'Usher / PIC Acara', 'price' => 800000],
                            ['id' => 'addon_tablet', 'name' => 'Tablet Buku Tamu', 'price' => 550000],
                            ['id' => 'addon_printer', 'name' => 'Printer Label', 'price' => 250000],
                            ['id' => 'addon_tv', 'name' => 'TV Display', 'price' => 650000],
                        ];

                        $discountRules = [
                            [
                                'type' => 'minimum_spend',
                                'minimum' => 10000000,
                                'discount_type' => 'percentage',
                                'discount_value' => 5,
                                'description' => '5% off for orders above 10M'
                            ],
                        ];

                        $configData = [
                            'packages' => $packages,
                            'addons' => $addons,
                            'discount_rules' => $discountRules
                        ];

                        \App\Models\PricingConfig::updateOrCreate(
                            ['name' => 'Viding Official API Pricing'],
                            [
                                'description' => 'Pricing configuration synced from global.viding.co API',
                                'config' => $configData,
                                'is_active' => true,
                                'uploaded_by' => auth()->id()
                            ]
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Successfully synced from Viding API')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error syncing from API')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}
