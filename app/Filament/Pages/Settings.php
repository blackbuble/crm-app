<?php
// app/Filament/Pages/Settings.php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 11;
    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    // Only admins can access settings
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public function mount(): void
    {
        // Load settings safely
        $formData = $this->loadSettings();
        $this->form->fill($formData);
    }
    
    private function loadSettings(): array
    {
        $defaults = [
            'company_name' => config('app.name', 'Your Company'),
            'company_email' => 'hello@example.com',
            'company_phone' => '+1234567890',
            'company_address' => '',
            'tax_id' => '',
            'company_logo' => null,
            'bank_accounts' => [],
            'quotation_terms' => '',
            'quotation_footer' => '',
        ];
        
        try {
            $settings = DB::table('settings')
                ->where('group', 'general')
                ->get()
                ->mapWithKeys(function ($item) {
                    $payload = json_decode($item->payload, true);
                    return [$item->name => $payload['value'] ?? null];
                })
                ->toArray();
            
            return array_merge($defaults, $settings);
        } catch (\Exception $e) {
            return $defaults;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Company Information')
                    ->schema([
                        FileUpload::make('company_logo')
                            ->label('Company Logo')
                            ->image()
                            ->disk('public')
                            ->directory('logo')
                            ->visibility('public')
                            ->helperText('Upload your company logo (max 2MB). Recommended: 300x300px')
                            ->maxSize(2048)
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('300')
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file) => 'company-logo.' . $file->getClientOriginalExtension()
                            )
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->panelAspectRatio('1:1')
                            ->panelLayout('integrated')
                            ->removeUploadedFileButtonPosition('center bottom')
                            ->uploadButtonPosition('center bottom')
                            ->uploadProgressIndicatorPosition('center bottom')
                            ->imagePreviewHeight('150')
                            ->loadingIndicatorPosition('center')
                            ->downloadable(),
                        
                        TextInput::make('company_name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('company_email')
                            ->label('Company Email')
                            ->email()
                            ->required(),
                        
                        TextInput::make('company_phone')
                            ->label('Company Phone')
                            ->tel()
                            ->required(),
                        
                        Textarea::make('company_address')
                            ->label('Company Address')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        TextInput::make('tax_id')
                            ->label('Tax ID / NPWP')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Bank Accounts')
                    ->description('Add your company bank accounts for quotations')
                    ->schema([
                        Repeater::make('bank_accounts')
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Bank Name')
                                    ->required()
                                    ->placeholder('e.g., BCA'),
                                
                                TextInput::make('account_number')
                                    ->label('Account Number')
                                    ->required(),
                                
                                TextInput::make('account_name')
                                    ->label('Account Name')
                                    ->required()
                                    ->placeholder('e.g., PT. Your Company'),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add Bank Account')
                            ->columnSpanFull()
                            ->collapsible(),
                    ]),

                Section::make('Quotation Settings')
                    ->schema([
                        Textarea::make('quotation_terms')
                            ->label('Quotation Terms & Conditions')
                            ->rows(6)
                            ->helperText('Default terms and conditions for quotations')
                            ->columnSpanFull(),
                        
                        Textarea::make('quotation_footer')
                            ->label('Quotation Footer')
                            ->rows(3)
                            ->helperText('Footer text for quotations')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        // Handle file upload separately
        $logoPath = null;
        if (isset($data['company_logo']) && $data['company_logo']) {
            $logoPath = $data['company_logo'];
        }
        
        // Save all settings except logo first
        foreach ($data as $name => $value) {
            if ($name === 'company_logo') {
                if ($logoPath) {
                    DB::table('settings')
                        ->updateOrInsert(
                            [
                                'group' => 'general',
                                'name' => $name,
                            ],
                            [
                                'payload' => json_encode(['value' => $logoPath]),
                                'updated_at' => now(),
                            ]
                        );
                }
            } else {
                DB::table('settings')
                    ->updateOrInsert(
                        [
                            'group' => 'general',
                            'name' => $name,
                        ],
                        [
                            'payload' => json_encode(['value' => $value]),
                            'updated_at' => now(),
                        ]
                    );
            }
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }
}