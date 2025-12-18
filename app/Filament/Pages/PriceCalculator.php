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
            'selected_packages' => [],
            'selected_addons' => [],
            'custom_discount' => 0,
        ]);
        
        $this->calculate();
    }

    public function form(Form $form): Form
    {
        if (!$this->activeConfig) {
            return $form->schema([
                Forms\Components\Placeholder::make('no_config')
                    ->content('No active pricing configuration found. Please create one in Marketing Operations > Pricing Configs.')
            ]);
        }

        $packages = $this->activeConfig->getPackages();
        $addons = $this->activeConfig->getAddons();

        return $form
            ->schema([
                Forms\Components\Section::make('Select Configuration')
                    ->schema([
                        Forms\Components\Select::make('config_id')
                            ->label('Pricing Configuration')
                            ->options(PricingConfig::where('is_active', true)->pluck('name', 'id'))
                            ->default($this->activeConfig->id)
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->activeConfig = PricingConfig::find($state);
                                $this->form->fill([
                                    'selected_packages' => [],
                                    'selected_addons' => [],
                                    'custom_discount' => 0,
                                ]);
                                $this->calculate();
                            }),
                    ]),

                Forms\Components\Section::make('Packages')
                    ->description('Select one or more packages')
                    ->schema([
                        Forms\Components\CheckboxList::make('selected_packages')
                            ->options(
                                collect($packages)->values()->mapWithKeys(function ($pkg, $index) {
                                    $id = $pkg['id'] ?? 'pkg_' . $index;
                                    return [$id => $pkg['name'] ?? 'Package ' . ($index + 1)];
                                })->toArray()
                            )
                            ->descriptions(
                                collect($packages)->values()->mapWithKeys(function ($pkg, $index) {
                                    $id = $pkg['id'] ?? 'pkg_' . $index;
                                    $price = isset($pkg['price']) ? format_currency($pkg['price']) : 'N/A';
                                    $desc = $pkg['description'] ?? '';
                                    return [$id => $price . ($desc ? ' - ' . $desc : '')];
                                })->toArray()
                            )
                            ->columns(2)
                            ->live()
                            ->afterStateUpdated(fn () => $this->calculate()),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Add-ons')
                    ->description('Optional additional services')
                    ->schema([
                        Forms\Components\CheckboxList::make('selected_addons')
                            ->options(
                                collect($addons)->values()->mapWithKeys(function ($addon, $index) {
                                    $id = $addon['id'] ?? 'addon_' . $index;
                                    return [$id => $addon['name'] ?? 'Add-on ' . ($index + 1)];
                                })->toArray()
                            )
                            ->descriptions(
                                collect($addons)->values()->mapWithKeys(function ($addon, $index) {
                                    $id = $addon['id'] ?? 'addon_' . $index;
                                    $price = isset($addon['price']) ? format_currency($addon['price']) : 'N/A';
                                    $desc = $addon['description'] ?? '';
                                    return [$id => $price . ($desc ? ' - ' . $desc : '')];
                                })->toArray()
                            )
                            ->columns(2)
                            ->live()
                            ->afterStateUpdated(fn () => $this->calculate()),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Discounts')
                    ->schema([
                        Forms\Components\TextInput::make('custom_discount')
                            ->label('Additional Discount')
                            ->numeric()
                            ->prefix(get_currency_symbol())
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->calculate())
                            ->helperText('Manual discount amount'),
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
            floatval($data['custom_discount'] ?? 0)
        );
    }

    public function getCalculation(): array
    {
        return $this->calculation;
    }
}
