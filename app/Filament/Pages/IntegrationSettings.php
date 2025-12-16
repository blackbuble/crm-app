<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class IntegrationSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $title = 'Integration Settings';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.integration-settings';

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_IntegrationSettings');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = DB::table('settings')
            ->where('group', 'integrations')
            ->get();

        $formData = [];
        foreach ($settings as $setting) {
            $payload = json_decode($setting->payload, true);
            $value = $payload['value'] ?? null;
            
            // Decrypt confidential fields if they were encrypted
            if ($setting->is_locked && $value) {
                try {
                    $value = Crypt::decryptString($value);
                } catch (\Exception $e) {
                    $value = ''; // Reset if decryption fails
                }
            }
            
            $formData[$setting->name] = $value;
        }

        $this->form->fill($formData);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Integrations')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Meta / Facebook Ads')
                            ->icon('heroicon-m-globe-alt')
                            ->schema([
                                Forms\Components\TextInput::make('meta_app_id')
                                    ->label('App ID')
                                    ->password()
                                    ->revealable(),
                                Forms\Components\TextInput::make('meta_app_secret')
                                    ->label('App Secret')
                                    ->password()
                                    ->revealable(),
                                Forms\Components\TextInput::make('meta_access_token')
                                    ->label('Permanant Access Token')
                                    ->password()
                                    ->revealable()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('meta_ad_account_id')
                                    ->label('Ad Account ID')
                                    ->placeholder('act_123456789')
                                    ->helperText('Start with act_'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Google Ads')
                            ->icon('heroicon-m-magnifying-glass')
                            ->schema([
                                Forms\Components\TextInput::make('google_client_id')
                                    ->label('Client ID')
                                    ->password()
                                    ->revealable(),
                                Forms\Components\TextInput::make('google_client_secret')
                                    ->label('Client Secret')
                                    ->password()
                                    ->revealable(),
                                Forms\Components\TextInput::make('google_developer_token')
                                    ->label('Developer Token')
                                    ->password()
                                    ->revealable(),
                                Forms\Components\TextInput::make('google_customer_id')
                                    ->label('Customer ID')
                                    ->placeholder('123-456-7890'),
                                Forms\Components\TextInput::make('google_refresh_token')
                                    ->label('Refresh Token')
                                    ->password()
                                    ->revealable()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('TikTok Ads')
                            ->icon('heroicon-m-video-camera')
                            ->schema([
                                Forms\Components\TextInput::make('tiktok_app_id')
                                    ->label('App ID')
                                    ->password()
                                    ->revealable(),
                                Forms\Components\TextInput::make('tiktok_secret')
                                    ->label('Secret')
                                    ->password()
                                    ->revealable(),
                                Forms\Components\TextInput::make('tiktok_access_token')
                                    ->label('Access Token')
                                    ->password()
                                    ->revealable()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('tiktok_advertiser_id')
                                    ->label('Advertiser ID'),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $group = 'integrations';

        // Fields that should be encrypted
        $encryptedFields = [
            'meta_app_secret', 'meta_access_token',
            'google_client_secret', 'google_developer_token', 'google_refresh_token',
            'tiktok_secret', 'tiktok_access_token'
        ];

        foreach ($data as $name => $value) {
            if ($value === null) continue;

            $isLocked = in_array($name, $encryptedFields);
            $storedValue = $value;

            if ($isLocked) {
                $storedValue = Crypt::encryptString($value);
            }

            // Upsert setting
            DB::table('settings')->updateOrInsert(
                ['group' => $group, 'name' => $name],
                [
                    'payload' => json_encode(['value' => $storedValue]),
                    'is_locked' => $isLocked,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
