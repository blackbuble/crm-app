<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Exhibition;
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
    protected static ?string $navigationGroup = 'Exhibitions';
    protected static ?string $title = 'Quick Lead Entry';
    protected static ?int $navigationSort = 5;
    
    protected static string $view = 'filament.pages.exhibition-kiosk';

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_ExhibitionKiosk');
    }

    public ?array $data = [];

    public function mount(): void
    {
        // Try to find active exhibition for today
        // Try to find active exhibition for today
        $query = Exhibition::whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());

        if (! auth()->user()->hasAnyRole(['super_admin', 'sales_manager', 'country_manager'])) {
            $query->where('created_by', auth()->id());
        }

        $activeExhibition = $query->first();

        $this->form->fill([
            'exhibition_id' => $activeExhibition?->id,
            'source' => 'Exhibition',
            'status' => 'lead',
        ]);
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
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\Select::make('est_invitation_pkg')
                                                            ->label('Digital Invitation')
                                                            ->options(collect($this->getPricingData()['packages']['Digital Invitation'])->pluck('package_name', 'product_id'))
                                                            ->live(),
                                                        
                                                        Forms\Components\Select::make('est_guestbook_pkg')
                                                            ->label('Buku Tamu Digital')
                                                            ->options(collect($this->getPricingData()['packages']['Buku Tamu Digital'])->pluck('package_name', 'product_id'))
                                                            ->live(),
                                                            
                                                        Forms\Components\Select::make('est_streaming_pkg')
                                                            ->label('Live Streaming')
                                                            ->options(collect($this->getPricingData()['packages']['Live Streaming'])->pluck('package_name', 'product_id'))
                                                            ->live(),
                                                    ]),

                                                Forms\Components\Fieldset::make('Add-ons')
                                                    ->schema(function () {
                                                        $addons = $this->getPricingData()['addons'];
                                                        $fields = [];
                                                        foreach ($addons as $key => $addon) {
                                                            $fields[] = Forms\Components\TagsInput::make('addon_' . $key)
                                                                ->label($addon['label'] . ' (' . number_format($addon['price']) . '/' . $addon['unit'] . ')')
                                                                ->placeholder('Qty')
                                                                ->suggestions(['1', '2', '3'])
                                                                ->separator(',')
                                                                ->live(); 
                                                            // Using TagsInput as a hack for simple array/qty input or just use TextInput with numeric
                                                        }
                                                        // Better: Use Repeater or simple TextInputs for Quantity
                                                        return array_map(function($key, $addon) {
                                                            return Forms\Components\TextInput::make('addon_' . $key)
                                                                ->label($addon['label'])
                                                                ->numeric()
                                                                ->default(0)
                                                                ->suffix($addon['unit'])
                                                                ->helperText('Rp ' . number_format($addon['price']))
                                                                ->live();
                                                        }, array_keys($addons), $addons);
                                                    })
                                                    ->columns(2),

                                                Forms\Components\Placeholder::make('total_estimation')
                                                    ->label('Total Estimated Price')
                                                    ->content(function (Forms\Get $get) {
                                                        $data = $this->getPricingData();
                                                        $total = 0;
                                                        $details = [];

                                                        // Packages
                                                        foreach (['est_invitation_pkg' => 'Digital Invitation', 'est_guestbook_pkg' => 'Buku Tamu Digital', 'est_streaming_pkg' => 'Live Streaming'] as $field => $category) {
                                                            $selectedId = $get($field);
                                                            if ($selectedId) {
                                                                $pkg = collect($data['packages'][$category])->firstWhere('product_id', $selectedId);
                                                                if ($pkg) {
                                                                    $price = $pkg['prices']['discounted']; // Use discounted for exhibition
                                                                    $total += $price;
                                                                    $details[] = "{$pkg['package_name']}: " . number_format($price);
                                                                }
                                                            }
                                                        }

                                                        // Addons
                                                        foreach ($data['addons'] as $key => $addon) {
                                                            $qty = (int) $get('addon_' . $key);
                                                            if ($qty > 0) {
                                                                $subtotal = $qty * $addon['price'];
                                                                $total += $subtotal;
                                                                $details[] = "{$addon['label']} ({$qty}x): " . number_format($subtotal);
                                                            }
                                                        }

                                                        $formattedTotal = number_format($total);
                                                        
                                                        return new \Illuminate\Support\HtmlString("
                                                            <div class='text-right'>
                                                                <div class='text-3xl font-black text-primary-600'>Rp {$formattedTotal}</div>
                                                                <div class='text-xs text-gray-500'>" . implode('<br>', $details) . "</div>
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
                                            
                                        Forms\Components\Textarea::make('wa_message')
                                            ->label('Custom Greeting Message')
                                            ->visible(fn (Forms\Get $get) => $get('send_instant_wa'))
                                            ->default("Hi! Terima kasih sudah mampir ke booth kami. Berikut price list spesial pameran untuk kakak.")
                                            ->rows(2),
                                            
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
                    $customer = Customer::where('email', $data['email'])
                        ->orWhere('phone', $data['phone'])
                        ->lockForUpdate() // Still keep row lock for good measure inside transaction
                        ->first();

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
                    $customer = Customer::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
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
                     $msg = $data['wa_message'] ?? 'Hi! Terima kasih sudah berkunjung.';
                     
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
                'send_instant_wa' => true,
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
