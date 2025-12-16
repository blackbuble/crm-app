<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Illuminate\Support\HtmlString;

class PriceCalculator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Sales Toolkit';
    protected static ?string $title = 'Price Calculator';
    protected static ?int $navigationSort = 1;
    
    protected static string $view = 'filament.pages.price-calculator';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Package Selection')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('selected_packages')
                            ->label('Select Packages')
                            ->multiple()
                            ->options(function () {
                                $data = $this->getPricingData()['packages'];
                                $options = [];
                                foreach ($data as $category => $packages) {
                                    foreach ($packages as $pkg) {
                                        $options[$pkg['product_id']] = "{$category} - {$pkg['package_name']} (Rp " . number_format($pkg['prices']['discounted']) . ")";
                                    }
                                }
                                return $options;
                            })
                            ->searchable()
                            ->live()
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('package_discount_percentage')
                            ->label('Package Discount (%)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->live(),
                    ]),

                Forms\Components\Section::make('Add-ons Configuration')
                    ->columns(2)
                    ->schema(function () {
                        $addons = $this->getPricingData()['addons'];
                        return array_map(function($key, $addon) {
                            return Forms\Components\TextInput::make('addon_' . $key)
                                ->label($addon['label'])
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->suffix($addon['unit'])
                                ->helperText('Rp ' . number_format($addon['price']))
                                ->live();
                        }, array_keys($addons), $addons);
                    }),

                Forms\Components\Section::make('Total Estimation')
                    ->schema([
                        Forms\Components\Placeholder::make('total_display')
                            ->hiddenLabel()
                            ->content(function (Forms\Get $get) {
                                $data = $this->getPricingData();
                                $total = 0;
                                $details = [];

                                // Calculate Packages
                                $packageTotal = 0;
                                $selectedIds = $get('selected_packages') ?? [];
                                $discountPct = (float) $get('package_discount_percentage');

                                // Flatten packages for easy lookup
                                $allPackages = collect($data['packages'])->flatten(1);

                                foreach ($selectedIds as $id) {
                                    $pkg = $allPackages->firstWhere('product_id', $id);
                                    if ($pkg) {
                                        $price = $pkg['prices']['discounted'];
                                        $packageTotal += $price;
                                        $details[] = [
                                            'label' => $pkg['package_name'],
                                            'price' => $price,
                                            'type' => 'pkg'
                                        ];
                                    }
                                }
                                
                                // Apply Discount
                                $discountAmount = 0;
                                if ($discountPct > 0 && $packageTotal > 0) {
                                    $discountAmount = ($packageTotal * $discountPct) / 100;
                                    $details[] = [
                                        'label' => "Discount ({$discountPct}%)",
                                        'price' => -$discountAmount,
                                        'type' => 'discount'
                                    ];
                                }
                                
                                $total += ($packageTotal - $discountAmount);

                                // Calculate Addons
                                foreach ($data['addons'] as $key => $addon) {
                                    $qty = (int) $get('addon_' . $key);
                                    if ($qty > 0) {
                                        $subtotal = $qty * $addon['price'];
                                        $total += $subtotal;
                                        $details[] = [
                                            'label' => "{$addon['label']} ({$qty}x)",
                                            'price' => $subtotal,
                                            'type' => 'addon'
                                        ];
                                    }
                                }

                                $formattedTotal = number_format($total);
                                
                                // Generate HTML Table for Breakdown
                                $rows = "";
                                foreach ($details as $item) {
                                    $p = number_format(abs($item['price']));
                                    $sign = $item['price'] < 0 ? '- Rp ' : 'Rp ';
                                    $color = $item['price'] < 0 ? 'text-success-600' : 'text-gray-900';
                                    
                                    $rows .= "<div class='flex justify-between text-sm py-1 border-b border-gray-100'>
                                        <span>{$item['label']}</span>
                                        <span class='font-medium {$color}'>{$sign}{$p}</span>
                                    </div>";
                                }

                                return new HtmlString("
                                    <div class='bg-white rounded-xl p-6 shadow-sm border border-gray-200'>
                                        <div class='space-y-2 mb-4'>
                                            {$rows}
                                        </div>
                                        <div class='flex justify-between items-center pt-4 border-t border-gray-200'>
                                            <span class='text-lg font-bold text-gray-700'>GRAND TOTAL</span>
                                            <span class='text-3xl font-black text-primary-600'>Rp {$formattedTotal}</span>
                                        </div>
                                    </div>
                                ");
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getPricingData(): array
    {
        return [
            "packages" => [
                "Digital Invitation" => [
                    [
                        "package_name" => "Basic",
                        "product_id" => 33,
                        "prices" => ["normal" => 360000, "discounted" => 300000, "base" => 300000],
                        "image" => "landing/Basic - Digital Invitation.png"
                    ],
                    [
                        "package_name" => "Intimate",
                        "product_id" => 34,
                        "prices" => ["normal" => 740000, "discounted" => 670000, "base" => 600000],
                        "image" => "landing/Intimate - Digital Invitation.png"
                    ],
                    [
                        "package_name" => "Royal",
                        "product_id" => 35,
                        "prices" => ["normal" => 900000, "discounted" => 850000, "base" => 760000],
                        "image" => "landing/Royal - Digital Invitation.png"
                    ]
                ],
                "Buku Tamu Digital" => [
                    [
                        "package_name" => "Apps Penerima Tamu",
                        "product_id" => 36,
                        "prices" => ["normal" => 2390000, "discounted" => 2100000, "base" => 1890000],
                        "image" => "landing/Apps Penerima Tamu.png"
                    ]
                ],
                "Live Streaming" => [
                    [
                        "package_name" => "Bronze",
                        "product_id" => 30,
                        "prices" => ["normal" => 3999000, "discounted" => 3700000, "base" => 3290000],
                        "image" => "landing/Bronze - Live Streaming.png"
                    ],
                    [
                        "package_name" => "Silver",
                        "product_id" => 31,
                        "prices" => ["normal" => 7780000, "discounted" => 5250000, "base" => 4690000],
                        "image" => "landing/Silver - Live Streaming.png"
                    ],
                    [
                        "package_name" => "Gold",
                        "product_id" => 32,
                        "prices" => ["normal" => 10190000, "discounted" => 6250000, "base" => 5590000],
                        "image" => "landing/Gold - Live Streaming.png"
                    ]
                ]
            ],
            "addons" => [
                "usher" => ["label" => "Usher / PIC Acara", "price" => 800000, "unit" => "orang"],
                "tablet" => ["label" => "Tablet Buku Tamu", "price" => 550000, "unit" => "unit"],
                "printer" => ["label" => "Printer Label", "price" => 250000, "unit" => "unit"],
                "combo_extend" => ["label" => "Extend Duration / Combo", "price" => 1300000, "unit" => "paket"],
                "tv" => ["label" => "TV Display", "price" => 650000, "unit" => "unit"],
                "domain" => ["label" => "Custom Domain", "price" => 150000, "unit" => "domain / tahun"]
            ]
        ];
    }
}
