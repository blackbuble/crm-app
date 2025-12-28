<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Exhibition;
use App\Models\PricingConfig;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ExhibitionKiosk extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationGroup = 'Sales Operations';
    protected static ?string $title = 'Quick Lead Entry';
    protected static ?int $navigationSort = 4;
    
    protected static string $view = 'filament.pages.exhibition-kiosk';

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_ExhibitionKiosk');
    }

    public ?array $data = [];
    public ?PricingConfig $activeConfig = null;
    public array $calculation = [
        'subtotal' => 0,
        'total' => 0,
        'auto_discount' => 0,
        'custom_discount' => 0,
        'package_discount' => 0,
        'package_discount_percent' => 0,
        'total_discount' => 0,
        'breakdown' => ['packages' => [], 'addons' => []]
    ];

    public function mount(): void
    {
        // Try to find active exhibition for today
        $query = Exhibition::whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());

        if (! auth()->user()->hasAnyRole(['super_admin', 'sales_manager', 'country_manager'])) {
            $query->where('created_by', auth()->id());
        }

        $activeExhibition = $query->first();

        $this->activeConfig = PricingConfig::where('is_active', true)->first();

        $defaultTemplate = auth()->user()->waTemplates()->where('is_active', true)->first()?->message ?? "Hi! Terima kasih sudah mampir ke booth kami. Berikut price list spesial pameran untuk kakak.";

        $this->form->fill([
            'exhibition_id' => $activeExhibition?->id,
            'source' => 'Exhibition',
            'status' => 'lead',
            'config_id' => $this->activeConfig?->id,
            'selected_packages' => [],
            'selected_addons' => [],
            'custom_discount' => 0,
            'package_discount' => 0,
            'wa_message' => $defaultTemplate,
            'send_instant_wa' => true,
        ]);

        $this->calculate();
    }

    public function calculate(): void
    {
        $formData = $this->form->getRawState();
        $configId = $formData['config_id'] ?? null;
        
        $config = $configId ? PricingConfig::find($configId) : $this->activeConfig;
        
        if (!$config) return;

        $this->calculation = $config->calculateTotal(
            $formData['selected_packages'] ?? [],
            $formData['selected_addons'] ?? [],
            floatval($formData['custom_discount'] ?? 0),
            floatval($formData['package_discount'] ?? 0)
        );
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Visitor Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Select::make('exhibition_id')
                                    ->label('Current Exhibition')
                                    ->options(function () {
                                        $query = Exhibition::query();
                                        
                                        if (! auth()->user()->hasAnyRole(['super_admin', 'sales_manager', 'country_manager'])) {
                                            $query->where('created_by', auth()->id());
                                        }

                                        return $query->orderBy('start_date', 'desc')->take(10)->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable(),

                                Forms\Components\TextInput::make('name')
                                    ->label('Visitor Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus(),

                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required(),
                                
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->required(),
                            ]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('Smart Scoring (Closing Potential)')
                                    ->description('Check all that apply to calculate lead quality.')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Checkbox::make('is_decision_maker')
                                                    ->label('👔 Decision Maker')
                                                    ->helperText('Authority to buy (20%)')
                                                    ->live(),
                                                    
                                                Forms\Components\Checkbox::make('has_budget')
                                                    ->label('💰 Budget Ready')
                                                    ->helperText('Money is available (20%)')
                                                    ->live(),
                                                
                                                Forms\Components\Checkbox::make('urgent_need')
                                                    ->label('🔥 Urgent Need')
                                                    ->helperText('Need < 1 Month (20%)')
                                                    ->live(),

                                                Forms\Components\Checkbox::make('request_demo')
                                                    ->label('💻 Request Demo')
                                                    ->helperText('Wants to see features (15%)')
                                                    ->live(),
                                            ]),

                                        Forms\Components\Checkbox::make('request_quotation')
                                            ->label('📄 Request Quotation (High Intent)')
                                            ->helperText('Asking for official price offer (25%)')
                                            ->live()
                                            ->columnSpanFull(),

                                        Forms\Components\Placeholder::make('lead_quality')
                                            ->label('Closing Probability Score')
                                            ->content(function (Forms\Get $get) {
                                                $score = 0;
                                                
                                                // BANT Standard
                                                if ($get('is_decision_maker')) $score += 15;
                                                if ($get('has_budget')) $score += 15;
                                                
                                                // Activity
                                                if ($get('request_demo')) $score += 10;
                                                if ($get('request_quotation')) $score += 20;

                                                // --- WEDDING SPECIFIC SCORING ---
                                                $visitorType = $get('visitor_type');
                                                if ($visitorType === 'couple_parents') $score += 30; // Parents usually pay -> High Close
                                                if ($visitorType === 'couple') $score += 15; 

                                                $weddingTime = $get('wedding_timeline');
                                                if ($weddingTime === 'urgent') $score += 25; // < 3 Months
                                                if ($weddingTime === 'this_year') $score += 15;

                                                // Cap at 100
                                                $score = min($score, 100);

                                                $color = match(true) {
                                                    $score >= 75 => 'text-success-600 font-bold',
                                                    $score >= 40 => 'text-warning-600 font-bold',
                                                    default => 'text-gray-500',
                                                };
                                                
                                                $label = match(true) {
                                                    $score >= 80 => '💍 GOLDEN TICKET (Ready to DP)',
                                                    $score >= 60 => '🔥 HOT PROSPECT',
                                                    $score >= 40 => '⚡ POTENTIAL',
                                                    default => '❄️ BROWSING',
                                                };

                                                return new \Illuminate\Support\HtmlString(
                                                    "<div class='flex items-center gap-2 p-2 bg-gray-50 rounded border'>
                                                        <span class='text-2xl font-black'>{$score}%</span>
                                                        <span class='text-sm {$color}'>{$label}</span>
                                                    </div>"
                                                );
                                            }),
                                    ]),

                                Forms\Components\Section::make('👰 Wedding Profile Analysis')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('visitor_type')
                                                    ->label('Siapa yang datang?')
                                                    ->options([
                                                        'cpw' => 'CPW (Bride Only) - Surveying',
                                                        'cpp' => 'CPP (Groom Only) - Surveying',
                                                        'couple' => 'Couple (Berdua) - Discussing',
                                                        'couple_parents' => 'Couple + Parents - DECISION MAKING',
                                                        'wo' => 'WO / Vendor - Partnership',
                                                    ])
                                                    ->live()
                                                    ->required(),
                                                
                                                Forms\Components\Select::make('wedding_timeline')
                                                    ->label('Kapan Acaranya?')
                                                    ->options([
                                                        'urgent' => 'Urgent (< 3 Bulan) - Panic Buying',
                                                        'this_year' => 'Tahun Ini (3-6 Bulan) - Serius',
                                                        'next_year' => 'Tahun Depan (> 1 Tahun) - Sanctai/Nabung',
                                                        'undecided' => 'Belum Tahu Tanggal',
                                                    ])
                                                    ->live(),
                                                
                                                Forms\Components\Select::make('pax_estimate')
                                                    ->label('Estimasi Undangan (Pax)')
                                                    ->options([
                                                        'intimate' => 'Intimate (< 100 pax)',
                                                        'medium' => 'Standard (300-800 pax)',
                                                        'grand' => 'Grand Wedding (> 1000 pax)',
                                                    ])
                                                    ->live(),

                                                Forms\Components\Checkbox::make('venue_booked')
                                                    ->live()
                                                    ->label('🏢 Venue sudah booking?')
                                                    ->helperText('Jika belom, tawarkan paket All-in!'),
                                            ]),

                                        Forms\Components\Placeholder::make('suggested_package')
                                            ->label('📦 Recommended Package')
                                            ->content(function (Forms\Get $get) {
                                                $pax = $get('pax_estimate');
                                                $venue = $get('venue_booked');
                                                $timeline = $get('wedding_timeline');
                                                $budget = $get('has_budget');
                                                
                                                if (! $pax) return new \Illuminate\Support\HtmlString("<span class='text-gray-400 italic'>Complete profile to see suggestion...</span>");
                                                
                                                $package = match($pax) {
                                                    'intimate' => 'Intimate Wedding Package',
                                                    'medium' => 'Classic Silver/Gold Package',
                                                    'grand' => 'Royal Platinum Package',
                                                    default => 'Standard Package',
                                                };
                                                
                                                $extras = [];
                                                if (! $venue) $extras[] = 'Include Venue';
                                                
                                                if ($timeline === 'urgent') {
                                                    $extras[] = 'Express Service';
                                                } elseif ($timeline === 'next_year') {
                                                    $extras[] = 'Early Bird Rate';
                                                }

                                                $color = 'text-primary-600';
                                                if ($budget && $pax === 'grand') {
                                                    $extras[] = '✨ VVIP Handling Service';
                                                    $color = 'text-purple-600';
                                                }
                                                
                                                $suggestion = $package;
                                                if (count($extras) > 0) {
                                                    $suggestion .= ' <span class="text-sm font-normal text-gray-600">(' . implode(' + ', $extras) . ')</span>';
                                                }
                                                
                                                return new \Illuminate\Support\HtmlString("
                                                    <div class='mt-2 p-3 bg-primary-50 rounded-lg border border-primary-100'>
                                                        <div class='font-bold {$color} text-lg'>{$suggestion}</div>
                                                        <div class='text-xs text-gray-500 mt-1'>Based on: {$pax} pax" . ($venue ? ", venue booked" : ", no venue") . "</div>
                                                    </div>
                                                ");
                                            }),

                                        Forms\Components\Section::make('💰 Quick Price Estimator')
                                            ->collapsed()
                                            ->schema([
                                                Forms\Components\Select::make('config_id')
                                                    ->label('Pricing Configuration')
                                                    ->options(fn() => PricingConfig::where('is_active', true)->pluck('name', 'id'))
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state) {
                                                        $this->activeConfig = PricingConfig::find($state);
                                                        $this->calculate();
                                                    }),

                                                Forms\Components\Select::make('selected_packages')
                                                    ->label('Packages')
                                                    ->multiple()
                                                    ->searchable()
                                                    ->options(function (Forms\Get $get) {
                                                        $configId = $get('config_id');
                                                        if (!$configId) return [];
                                                        $config = PricingConfig::find($configId);
                                                        if (!$config) return [];
                                                        return collect($config->getPackages())->values()->mapWithKeys(function($pkg, $index) {
                                                            $id = $pkg['id'] ?? 'pkg_'.$index;
                                                            $name = $pkg['name'] ?? 'Package';
                                                            $price = isset($pkg['price']) ? ' ('.format_currency($pkg['price']).')' : '';
                                                            return [$id => $name . $price];
                                                        })->toArray();
                                                    })
                                                    ->live()
                                                    ->afterStateUpdated(fn () => $this->calculate()),

                                                Forms\Components\TextInput::make('package_discount')
                                                    ->label('Package Discount %')
                                                    ->numeric()
                                                    ->suffix('%')
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->maxValue(100)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn () => $this->calculate()),

                                                Forms\Components\Repeater::make('selected_addons')
                                                    ->label('Add-ons')
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
                                                                        $hasStreaming = collect($packageNames)->contains(fn($pn) => str_contains($pn, 'live') || str_contains($pn, 'streaming') || str_contains($pn, 'livestream'));

                                                                        if ($category === 'Live Streaming' || $category === 'Live Cam') return $hasStreaming;
                                                                        if ($category === 'Combo') {
                                                                            if (str_contains($addonName, 'live streaming') || str_contains($addonName, 'streaming')) {
                                                                                if (!$hasStreaming) return false;
                                                                                if (str_contains($addonName, 'bronze')) return collect($packageNames)->contains(fn($pn) => str_contains($pn, 'bronze'));
                                                                                if (str_contains($addonName, 'silver')) return collect($packageNames)->contains(fn($pn) => str_contains($pn, 'silver'));
                                                                                if (str_contains($addonName, 'gold'))   return collect($packageNames)->contains(fn($pn) => str_contains($pn, 'gold'));
                                                                            }
                                                                            return !empty($packageNames);
                                                                        }
                                                                        return true;
                                                                    })
                                                                    ->groupBy('category')
                                                                    ->map(fn($items) => $items->values()->mapWithKeys(function($a, $i) {
                                                                        $id = $a['id'] ?? 'addon_'.$i;
                                                                        $name = $a['name'] ?? 'Add-on';
                                                                        $price = isset($a['price']) ? ' ('.format_currency($a['price']).')' : '';
                                                                        return [$id => $name . $price];
                                                                    }))
                                                                    ->toArray();
                                                            })
                                                            ->required()
                                                            ->live()
                                                            ->afterStateUpdated(fn () => $this->calculate()),

                                                        Forms\Components\TextInput::make('quantity')
                                                            ->label(fn(Forms\Get $get) => str_contains(strtolower($get('addon_id') ?? ''), 'wa_blast') ? 'Multiplier (100msg)' : 'Qty')
                                                            ->numeric()
                                                            ->default(1)
                                                            ->minValue(1)
                                                            ->visible(function (Forms\Get $get) {
                                                                $id = strtolower($get('addon_id') ?? '');
                                                                if (!$id) return false;
                                                                foreach (['livecam', 'egift', 'domain', 'layar_sapa'] as $t) if (str_contains($id, $t)) return false;
                                                                return true;
                                                            })
                                                            ->live()
                                                            ->afterStateUpdated(fn () => $this->calculate()),

                                                        Forms\Components\Select::make('tv_size')
                                                            ->label('Size')
                                                            ->options(['50' => '50"', '60' => '60"', '65' => '65"'])
                                                            ->visible(fn (Forms\Get $get) => str_contains(strtolower($get('addon_id') ?? ''), 'tv'))
                                                            ->live()
                                                            ->afterStateUpdated(fn () => $this->calculate()),
                                                    ])->columns(3)
                                                    ->live()
                                                    ->itemLabel(function (array $state, Forms\Get $get): ?string {
                                                        $configId = $get('config_id');
                                                        $config = $configId ? PricingConfig::find($configId) : null;
                                                        return collect($config?->getAddons() ?? [])->firstWhere('id', $state['addon_id'] ?? null)['name'] ?? null;
                                                    })
                                                    ->afterStateUpdated(fn () => $this->calculate()),

                                                Forms\Components\TextInput::make('custom_discount')
                                                    ->label('Extra Discount')
                                                    ->numeric()
                                                    ->prefix(get_currency_symbol())
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn () => $this->calculate()),

                                                Forms\Components\Placeholder::make('total_estimation')
                                                    ->label('Price Estimation')
                                                    ->content(function () {
                                                        $calc = $this->calculation;
                                                        $formattedTotal = format_currency($calc['total']);
                                                        
                                                        $details = [];
                                                        foreach ($calc['breakdown']['packages'] as $pkg) $details[] = "• {$pkg['name']}";
                                                        foreach ($calc['breakdown']['addons'] as $addon) $details[] = "• {$addon['name']} (x{$addon['quantity']})";
                                                        
                                                        if ($calc['package_discount'] > 0) $details[] = "🎁 Package Disc: -" . format_currency($calc['package_discount']);
                                                        if ($calc['auto_discount'] > 0) $details[] = "✨ Auto Disc: -" . format_currency($calc['auto_discount']);
                                                        if ($calc['custom_discount'] > 0) $details[] = "💰 Extra Disc: -" . format_currency($calc['custom_discount']);

                                                        return new \Illuminate\Support\HtmlString("
                                                            <div class='mt-2 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200'>
                                                                <div class='flex justify-between items-center mb-2'>
                                                                    <span class='text-gray-500 text-sm'>Total Estimate:</span>
                                                                    <span class='text-2xl font-black text-primary-600'>{$formattedTotal}</span>
                                                                </div>
                                                                <div class='text-[10px] text-gray-400 leading-tight'>" . implode(' | ', $details) . "</div>
                                                            </div>
                                                        ");
                                                    }),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('🚀 Closing Booster')
                                    ->description('Lock commitment now to secure the deal.')
                                    ->schema([
                                        Forms\Components\Select::make('promo_locked')
                                            ->label('🔒 Lock Exhibition Promo')
                                            ->options([
                                                'discount_10' => 'Diskon Pameran 10%',
                                                'free_setup' => 'Free Setup / Installation',
                                                'buy_1_get_1' => 'Buy 1 Get 1 (Limited)',
                                                'extended_trial' => 'Extended Trial (30 Days)',
                                            ])
                                            ->placeholder('Select offer to lock...')
                                            ->helperText('Offer valid only if locked now. Use this to push decision.'),
                                        
                                        Forms\Components\DateTimePicker::make('next_follow_up')
                                            ->label('📅 Next Commitment')
                                            ->required()
                                            ->default(now()->addDay()->setHour(10)->setMinute(0))
                                            ->seconds(false)
                                            ->helperText('Ask: "Should I call you tomorrow morning or afternoon?"'),

                                        Forms\Components\Checkbox::make('send_instant_wa')
                                            ->label('📱 Send "Thank You & Promo Details" WA now')
                                            ->default(true)
                                            ->live()
                                            ->helperText('Send immediate greeting to lock relationship.'),
                                            
                                        Forms\Components\Select::make('wa_template_id')
                                            ->label('Select Message Template')
                                            ->visible(fn (Forms\Get $get) => $get('send_instant_wa'))
                                            ->options(function() {
                                                return auth()->user()->waTemplates()
                                                    ->where('is_active', true)
                                                    ->pluck('category', 'id')
                                                    ->toArray();
                                            })
                                            ->live()
                                            ->afterStateUpdated(function($state, $set) {
                                                if ($state) {
                                                    $template = \App\Models\WaTemplate::find($state);
                                                    if ($template) {
                                                        $set('wa_message', $template->message);
                                                    }
                                                }
                                            }),
                                            
                                        Forms\Components\Textarea::make('wa_message')
                                            ->label('Custom Greeting Message')
                                            ->visible(fn (Forms\Get $get) => $get('send_instant_wa'))
                                            ->rows(3),
                                            
                                        Forms\Components\Select::make('wa_attachment_id')
                                            ->label('Attach Price List / Brochure')
                                            ->visible(fn (Forms\Get $get) => $get('send_instant_wa'))
                                            ->options(\App\Models\MarketingMaterial::whereIn('type', ['price_list', 'brochure'])
                                                ->where('is_active', true)
                                                ->pluck('title', 'id'))
                                            ->searchable()
                                            ->placeholder('Select file to send...'),
                                    ]),
                                
                                Forms\Components\Textarea::make('notes')
                                    ->label('Additional Notes / Specific Features Asked')
                                    ->rows(3)
                                    ->placeholder('Asked about API integration, bulk discount, etc...'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        // HOTFIX: Validate critical fields for accurate lead scoring (ISSUE-H002)
        if (empty($data['visitor_type'])) {
            Notification::make()
                ->danger()
                ->title('Validation Error')
                ->body('Please select "Who Visited" to continue. This helps us provide better service.')
                ->persistent()
                ->send();
            return;
        }
        
        if (empty($data['wedding_timeline'])) {
            Notification::make()
                ->warning()
                ->title('Missing Information')
                ->body('Please select "Wedding Timeline" for accurate quotation and lead scoring.')
                ->send();
            // Don't return - allow submission but warn user
        }

        // Calculate Weighted Score
        $score = 0;
        $qualifications = [];
        
        // --- 1. BANT Standard (Weighted Lower) ---
        if ($data['is_decision_maker'] ?? false) { $score += 15; $qualifications[] = 'Auth:Yes'; }
        if ($data['has_budget'] ?? false) { $score += 15; $qualifications[] = 'Budget:Yes'; }
        if ($data['request_demo'] ?? false) { $score += 10; $qualifications[] = 'Action:Demo'; }
        if ($data['request_quotation'] ?? false) { $score += 20; $qualifications[] = 'Action:Quote'; }

        // --- 2. Wedding Specifics (High Impact) ---
        $visitorType = $data['visitor_type'] ?? '';
        if ($visitorType === 'couple_parents') { $score += 30; $qualifications[] = 'Type:Parents(DecisionMaker)'; }
        elseif ($visitorType === 'couple') { $score += 15; $qualifications[] = 'Type:Couple'; }
        elseif ($visitorType === 'wo') { $score += 10; $qualifications[] = 'Type:WO'; }

        $weddingTime = $data['wedding_timeline'] ?? '';
        if ($weddingTime === 'urgent') { $score += 25; $qualifications[] = 'Time:Urgent(<3Mo)'; }
        elseif ($weddingTime === 'this_year') { $score += 15; $qualifications[] = 'Time:ThisYear'; }

        if (! ($data['venue_booked'] ?? false)) { $qualifications[] = 'Venue:NotYet(Upsell)'; }

        // Cap Score
        $score = min($score, 100);

        // Determine Status & Tag based on Score
        $status = ($score >= 75) ? 'prospect' : 'lead'; 
        
        $qualityTag = match(true) {
            $score >= 80 => 'Golden Ticket',
            $score >= 60 => 'Hot Prospect',
            $score >= 40 => 'Potential Lead',
            default => 'Browsing',
        };

        // Append analysis to notes
        $analysisNote = "\n\n[Wedding Analysis]: {$qualityTag} (Score: {$score}%)\n";
        $analysisNote .= "• Visitor: " . ucwords(str_replace('_', ' ', $visitorType)) . "\n";
        $analysisNote .= "• Timeline: " . ucwords(str_replace('_', ' ', $weddingTime)) . "\n";
        $analysisNote .= "• Pax: " . ucwords($data['pax_estimate'] ?? '-') . "\n";
        $analysisNote .= "• Venue: " . ($data['venue_booked'] ? 'Booked' : 'Not Yet (Opportunity)') . "\n";
        
        // Promo Locked Note
        if (!empty($data['promo_locked'])) {
            $promoName = ucwords(str_replace('_', ' ', $data['promo_locked']));
            $analysisNote .= "🔒 LOCKED PROMO: {$promoName}\n";
        }
        
        // Price Estimation Note
        if ($this->activeConfig && (!empty($data['selected_packages']) || !empty($data['selected_addons']))) {
            $calc = $this->activeConfig->calculateTotal(
                $data['selected_packages'] ?? [],
                $data['selected_addons'] ?? [],
                floatval($data['custom_discount'] ?? 0)
            );
            
            $analysisNote .= "\n💰 [Pricing Estimation]:\n";
            foreach ($calc['breakdown']['packages'] as $pkg) {
                $analysisNote .= "• Package: {$pkg['name']} (" . format_currency($pkg['price']) . ")\n";
            }
            foreach ($calc['breakdown']['addons'] as $addon) {
                $analysisNote .= "• Add-on: {$addon['name']} (" . format_currency($addon['price']) . ")\n";
            }
            if ($calc['total_discount'] > 0) {
                 $analysisNote .= "• Total Discount: -" . format_currency($calc['total_discount']) . " (" . format_currency($calc['auto_discount']) . " auto + " . format_currency($calc['custom_discount']) . " custom)\n";
            }
            $analysisNote .= "💰 TOTAL ESTIMATE: " . format_currency($calc['total']) . "\n";
        }

        $analysisNote .= "Quals: " . implode(', ', $qualifications);

        $finalNotes = ($data['notes'] ?? '') . $analysisNote;

        // Get Exhibition Name for Tagging
        $exhibition = Exhibition::find($data['exhibition_id']);
        $exhibitionTagName = $exhibition ? $exhibition->name : 'Unknown Exhibition';

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($data, $status, $finalNotes, $qualityTag, $score, $exhibitionTagName) {
                // Prevent Race Condition on Duplicate Entry
                // Use MySQL Named Lock to serialize requests for the same email/phone
                // This is necessary because the table lacks unique constraints and we want to prevent duplicates
                $lockKey = 'customer_upsert_' . md5(trim(strtolower($data['email'])) . '|' . trim($data['phone']));
                $lockAcquired = \Illuminate\Support\Facades\DB::scalar("SELECT GET_LOCK(?, 5)", [$lockKey]);
                
                if (! $lockAcquired) {
                    throw new \Exception('System busy, please try again (Lock timeout).');
                }
                
                try {
                    // HOTFIX: Improved duplicate detection to prevent wrong customer updates (ISSUE-H001)
                    // QA FIX: Added null checks and email normalization for edge cases
                    
                    // Validate that we have at least email or phone
                    if (empty($data['email']) && empty($data['phone'])) {
                        throw new \Exception('Email or phone number is required to create a customer.');
                    }
                    
                    // Normalize email to lowercase for consistent matching
                    $email = !empty($data['email']) ? strtolower(trim($data['email'])) : null;
                    $phone = !empty($data['phone']) ? trim($data['phone']) : null;
                    
                    // Check email and phone separately to avoid updating wrong customer
                    $customerByEmail = $email
                        ? Customer::where('email', $email)->lockForUpdate()->first()
                        : null;
                    
                    $customerByPhone = $phone
                        ? Customer::where('phone', $phone)->lockForUpdate()->first()
                        : null;

                    // Detect conflict: same email and phone exist but belong to different customers
                    if ($customerByEmail && $customerByPhone && $customerByEmail->id !== $customerByPhone->id) {
                        // QA FIX: Escape customer names to prevent XSS
                        throw new \Exception(
                            'Data conflict detected: Email belongs to "' . e($customerByEmail->name) . 
                            '" but phone belongs to "' . e($customerByPhone->name) . 
                            '". Please verify the information.'
                        );
                    }

                    // Use email match first (more reliable), fallback to phone match
                    $customer = $customerByEmail ?? $customerByPhone;

                // Tags to Apply
                $tagsToApply = ['Exhibition Lead', $qualityTag, 'Wedding2025', $exhibitionTagName];

                if ($customer) {
                    // Update existing customer
                    $customer->update([
                        'name' => $data['name'],
                        'notes' => $customer->notes . "\n---\n[New Visit]: " . trim($finalNotes), // Append notes
                        // Don't downgrade status if already prospect/customer
                        'status' => ($customer->status === 'customer' || $customer->status === 'prospect') ? $customer->status : $status,
                        'source' => 'Exhibition', // Update source to latest touchpoint
                    ]);
                    
                    $actionType = 'Updated';
                } else {
                    // Create new
                    // QA FIX: Use normalized email
                    $customer = Customer::create([
                        'name' => $data['name'],
                        'email' => $email,
                        'phone' => $phone,
                        'notes' => trim($finalNotes),
                        'status' => $status,
                        'source' => 'Exhibition',
                        'exhibition_id' => $data['exhibition_id'],
                        'assigned_to' => Auth::id(),
                    ]);
                    $actionType = 'Saved';
                }

                // Apply Tags (Spatie)
                $customer->attachTags($tagsToApply);
                
                // 1. Create Follow-up Task
                $nextFollowUp = \Carbon\Carbon::parse($data['next_follow_up']);
                
                \App\Models\FollowUp::create([
                    'customer_id' => $customer->id,
                    'user_id' => Auth::id(),
                    'follow_up_date' => $nextFollowUp->format('Y-m-d'),
                    'follow_up_time' => $nextFollowUp->format('H:i:s'),
                    'type' => 'phone', 
                    'status' => 'pending',
                    'notes' => "Follow up {$qualityTag} wedding lead. " . ($data['promo_locked'] ? 'Discuss Promo.' : 'Initial consult.'),
                ]);

                // 2. Handle WhatsApp (Personal / Manual)
                $notification = Notification::make()
                    ->success()
                    ->title("{$qualityTag} {$actionType}!")
                    ->body("{$customer->name} scored {$score}%.");

                if ($data['send_instant_wa'] ?? false) {
                     $rawMsg = $data['wa_message'] ?? 'Hi! Terima kasih sudah berkunjung.';
                     $msg = str_replace('{name}', $data['name'] ?? '', $rawMsg);
                     
                     // Attach Link if selected
                     $attachmentId = $data['wa_attachment_id'] ?? null;
                     if ($attachmentId) {
                         $attach = \App\Models\MarketingMaterial::find($attachmentId);
                         if ($attach && $attach->file_path) {
                             // Generate full public URL
                             $link = asset(\Illuminate\Support\Facades\Storage::url($attach->file_path));
                             $msg .= "\n\n📄 Download Brochure/Price List: " . $link;
                         }
                     }

                     // Use Helper to format URL
                     // Ensure get_whatsapp_url is available or fallback
                     $waUrl = function_exists('get_whatsapp_url') 
                        ? get_whatsapp_url($customer->phone, '+62', $msg)
                        : 'https://wa.me/' . preg_replace('/[^0-9]/','', $customer->phone) . '?text=' . urlencode($msg);
                     
                     if ($waUrl) {
                        $notification
                            ->title("Saved! Send WA Now?")
                            ->body("Lead saved. Open WhatsApp to send the greeting & file link.")
                            ->persistent() // User needs time to click
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('send_wa')
                                    ->label('🚀 Open WhatsApp')
                                    ->button()
                                    ->url($waUrl)
                                    ->openUrlInNewTab(),
                            ]);
                     }
                }

                $notification->send();
                
                } finally {
                    \Illuminate\Support\Facades\DB::scalar("SELECT RELEASE_LOCK(?)", [$lockKey]);
                }
            }); // End Transaction

            // Reset form only on success
            $currentExhibition = $data['exhibition_id'];
            $this->form->fill([
                'exhibition_id' => $currentExhibition,
                'source' => 'Exhibition',
                'status' => 'lead',
                'is_decision_maker' => false,
                'has_budget' => false,
                'request_demo' => false,
                'request_quotation' => false,
                'promo_locked' => null,
                'visitor_type' => null,
                'wedding_timeline' => null,
                'pax_estimate' => null,
                'venue_booked' => false,
                'selected_packages' => [],
                'selected_addons' => [],
                'custom_discount' => 0,
                'send_instant_wa' => true,
                'wa_template_id' => null,
                'wa_message' => auth()->user()->waTemplates()->where('is_active', true)->first()?->message ?? "Hi! Terima kasih sudah mampir ke booth kami. Berikut price list spesial pameran untuk kakak.",
            ]);
            
            $this->dispatch('lead-captured');

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Saving Lead')
                ->body('Something went wrong: ' . $e->getMessage())
                ->send();
            
            // Log error for debugging
            \Illuminate\Support\Facades\Log::error('Kiosk Save duplicate/error: ' . $e->getMessage());
        }
    }
    // Unified pricing data from active PricingConfig
}
