<?php

namespace App\Filament\Pages;

use App\Models\PricingConfig;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;

class PriceCalculator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Sales Operations';
    protected static ?string $title = 'Price Calculator';
    protected static ?int $navigationSort = 5;
    
    protected static string $view = 'filament.pages.price-calculator';

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_PriceCalculator');
    }

    public ?array $data = [];
    public ?PricingConfig $activeConfig = null;
    public array $calculation = [];

    public function mount(): void
    {
        $this->activeConfig = PricingConfig::where('is_active', true)->first();
        
        $this->form->fill([
            'config_id' => $this->activeConfig?->id,
            'selected_packages' => [],
            'selected_addons' => [],
            'custom_discount' => 0,
            'package_discount' => 0,
        ]);
        
        $this->calculate();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Select Configuration')
                    ->schema([
                        Forms\Components\Select::make('config_id')
                            ->label('Pricing Configuration')
                            ->options(fn() => PricingConfig::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->activeConfig = PricingConfig::find($state);
                                $this->form->fill([
                                    'config_id' => $state,
                                    'selected_packages' => [],
                                    'selected_addons' => [],
                                    'custom_discount' => 0,
                                    'package_discount' => 0,
                                ]);
                                $this->calculate();
                            }),
                    ]),

                Forms\Components\Section::make('Packages')
                    ->description('Select packages and apply discounts')
                    ->visible(fn(Forms\Get $get) => filled($get('config_id')))
                    ->schema([
                        Forms\Components\Select::make('selected_packages')
                            ->multiple()
                            ->searchable()
                            ->options(function (Forms\Get $get) {
                                $configId = $get('config_id');
                                if (!$configId) return [];
                                
                                $config = PricingConfig::find($configId);
                                if (!$config) return [];

                                return collect($config->getPackages())->values()->mapWithKeys(function ($pkg, $index) {
                                    $id = $pkg['id'] ?? 'pkg_' . $index;
                                    $price = isset($pkg['price']) ? ' (' . format_currency($pkg['price']) . ')' : '';
                                    return [$id => ($pkg['name'] ?? 'Package ' . ($index + 1)) . $price];
                                })->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(fn () => $this->calculate()),

                        Forms\Components\TextInput::make('package_discount')
                            ->label('Package Specific Discount')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->calculate())
                            ->helperText('Percentage discount applied to packages'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Add-ons')
                    ->description('Add services and set quantities')
                    ->visible(fn(Forms\Get $get) => filled($get('config_id')))
                    ->schema([
                        Forms\Components\Repeater::make('selected_addons')
                            ->schema([
                                Forms\Components\Select::make('addon_id')
                                    ->label('Service')
                                    ->options(function (Forms\Get $get) {
                                        $configId = $get('../../config_id');
                                        if (!$configId) return [];

                                        $config = PricingConfig::find($configId);
                                        if (!$config) return [];

                                        $selectedPackages = $get('../../selected_packages') ?? [];
                                        $packageNames = collect($config->getPackages())
                                            ->whereIn('id', $selectedPackages)
                                            ->pluck('name')
                                            ->map(fn($n) => strtolower($n))
                                            ->all();

                                        return collect($config->getAddons())
                                            ->filter(function ($addon) use ($packageNames) {
                                                $category = $addon['category'] ?? '';
                                                $addonName = strtolower($addon['name'] ?? '');
                                                
                                                // Check if selected packages support live streaming
                                                $hasStreamingPackage = collect($packageNames)->contains(fn($pn) => 
                                                    str_contains($pn, 'live') || 
                                                    str_contains($pn, 'streaming') || 
                                                    str_contains($pn, 'livestream')
                                                );

                                                // 1. Filter categories "Live Streaming" & "Live Cam"
                                                if ($category === 'Live Streaming' || $category === 'Live Cam') {
                                                    return $hasStreamingPackage;
                                                }

                                                // 2. Filter category "Combo"
                                                if ($category === 'Combo') {
                                                    // If it's a live streaming combo, check level and streaming support
                                                    if (str_contains($addonName, 'live streaming') || str_contains($addonName, 'streaming')) {
                                                        if (!$hasStreamingPackage) return false;

                                                        if (str_contains($addonName, 'bronze')) {
                                                            return collect($packageNames)->contains(fn($pn) => str_contains($pn, 'bronze'));
                                                        }
                                                        if (str_contains($addonName, 'silver')) {
                                                            return collect($packageNames)->contains(fn($pn) => str_contains($pn, 'silver'));
                                                        }
                                                        if (str_contains($addonName, 'gold')) {
                                                            return collect($packageNames)->contains(fn($pn) => str_contains($pn, 'gold'));
                                                        }
                                                    }

                                                    // For other combos like Buku Tamu, show if any package is selected
                                                    return !empty($packageNames);
                                                }

                                                return true;
                                            })
                                            ->groupBy('category')
                                            ->map(function ($items) {
                                                return $items->values()->mapWithKeys(function ($addon, $index) {
                                                    $id = $addon['id'] ?? 'addon_' . $index;
                                                    $price = isset($addon['price']) ? ' (' . format_currency($addon['price']) . ')' : '';
                                                    return [$id => ($addon['name'] ?? 'Add-on') . $price];
                                                });
                                            })->toArray();
                                    })
                                    ->required()
                                    ->live(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label(fn(Forms\Get $get) => str_contains(strtolower($get('addon_id') ?? ''), 'wa_blast') ? 'Multiplier (x100 Msg)' : 'Quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->visible(function (Forms\Get $get) {
                                        $id = $get('addon_id');
                                        if (!$id) return false;
                                        
                                        $id = strtolower($id);
                                        // No quantity for these (WA Blast removed from this list)
                                        $noQty = ['livecam', 'egift', 'domain', 'layar_sapa'];
                                        foreach ($noQty as $term) {
                                            if (str_contains($id, $term)) return false;
                                        }
                                        return true;
                                    }),

                                Forms\Components\Select::make('tv_size')
                                    ->label('TV Size')
                                    ->options([
                                        '50' => '50 Inch (1.225.000)',
                                        '60' => '60 Inch (1.555.000)',
                                        '65' => '65 Inch (1.750.000)',
                                    ])
                                    ->visible(fn (Forms\Get $get) => $get('addon_id') && str_contains(strtolower($get('addon_id')), 'tv')),
                            ])->columns(2)
                            ->live()
                            ->afterStateUpdated(fn () => $this->calculate())
                            ->itemLabel(function (array $state, Forms\Get $get): ?string {
                                $configId = $get('config_id');
                                if (!$configId || empty($state['addon_id'])) return null;
                                
                                $config = PricingConfig::find($configId);
                                if (!$config) return null;

                                return collect($config->getAddons())->firstWhere('id', $state['addon_id'])['name'] ?? null;
                            }),
                    ]),

                Forms\Components\Section::make('Global Discounts')
                    ->visible(fn(Forms\Get $get) => filled($get('config_id')))
                    ->schema([
                        Forms\Components\TextInput::make('custom_discount')
                            ->label('Extra Global Discount')
                            ->numeric()
                            ->prefix(get_currency_symbol())
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->calculate())
                            ->helperText('Manual extra discount for the whole deal'),
                    ]),
            ])
            ->statePath('data');
    }

    public function calculate(): void
    {
        if (!$this->activeConfig) {
            $this->calculation = [];
            return;
        }

        $data = $this->form->getState();
        
        $this->calculation = $this->activeConfig->calculateTotal(
            $data['selected_packages'] ?? [],
            $data['selected_addons'] ?? [],
            floatval($data['custom_discount'] ?? 0),
            floatval($data['package_discount'] ?? 0)
        );
    }

    public function getCalculation(): array
    {
        return $this->calculation;
    }
}
