<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Account Management';
    protected static ?string $title = 'My Profile';
    protected static ?int $navigationSort = 1;
    
    protected static string $view = 'filament.pages.my-profile';

    public ?array $data = [];
    public bool $otp_enabled = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
        ]);
        
        $this->otp_enabled = (bool) $user->otp_enabled;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Information')
                    ->description('Update your account\'s profile information and email address.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true, table: 'users', column: 'email', ignorable: auth()->user())
                            ->disabled(! auth()->user()->hasRole('super_admin'))
                            ->dehydrated(auth()->user()->hasRole('super_admin')),
                    ])->columns(2),

                Forms\Components\Section::make('Update Password')
                    ->description('Ensure your account is using a long, random password to stay secure.')
                    ->schema([
                        Forms\Components\TextInput::make('new_password')
                            ->password()
                            ->revealable()
                            ->confirmed(),
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->password()
                            ->revealable(),
                    ])->columns(2),

                Forms\Components\Section::make('Two-Factor Authentication (OTP)')
                    ->description('Add additional security to your account using TOTP.')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('toggle_otp')
                                ->label(fn () => $this->otp_enabled ? 'Disable 2FA' : 'Enable 2FA (OTP)')
                                ->color(fn () => $this->otp_enabled ? 'danger' : 'success')
                                ->icon(fn () => $this->otp_enabled ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                                ->requiresConfirmation()
                                ->modalHeading(fn () => $this->otp_enabled ? 'Disable Two-Factor Authentication' : 'Enable Two-Factor Authentication')
                                ->modalDescription(fn () => $this->otp_enabled 
                                    ? 'Are you sure you want to disable 2FA? This will make your account less secure.' 
                                    : 'When 2FA is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.')
                                ->form(function () {
                                    if ($this->otp_enabled) return [];
                                    
                                    // Generate a secret (Base32) using Helper
                                    $secret = \App\Helpers\TotpHelper::generateSecret();
                                    
                                    // Store secret temporarily in component state or session to verify later
                                    // But since this is a modal form action, we can't easily persist state across re-renders of the modal unless passed in hidden field
                                    // Or we just regenerate it. For UX, let's include it in a hidden field.
                                    
                                    return [
                                        Forms\Components\Hidden::make('secret_key')
                                            ->default($secret),
                                            
                                        Forms\Components\Placeholder::make('setup_instructions')
                                            ->content(function (Forms\Get $get) {
                                                $secret = $get('secret_key');
                                                $userEmail = auth()->user()->email;
                                                $company = urlencode(get_company_name()); // Assuming this helper exists, or use config('app.name')
                                                if (!function_exists('get_company_name')) {
                                                    $company = urlencode(config('app.name'));
                                                }
                                                
                                                // Generate OTP Auth URL
                                                $otpAuthUrl = "otpauth://totp/{$company}:{$userEmail}?secret={$secret}&issuer={$company}";
                                                $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpAuthUrl);
                                                
                                                return new \Illuminate\Support\HtmlString("
                                                    <div class='text-center space-y-4'>
                                                        <p class='text-sm text-gray-600'>Scan the QR code below with your authenticator app.</p>
                                                        
                                                        <div class='flex justify-center'>
                                                            <img src='{$qrCodeUrl}' alt='QR Code' class='border rounded p-2 bg-white shadow-sm' />
                                                        </div>

                                                        <div class='text-xs text-gray-500'>
                                                            Or enter this key manually:
                                                        </div>
                                                        <div class='font-mono bg-gray-100 p-2 rounded text-lg font-bold tracking-wider select-all inline-block'>{$secret}</div>
                                                    </div>
                                                ");
                                            }),
                                            
                                        Forms\Components\TextInput::make('code')
                                            ->label('Verification Code')
                                            ->placeholder('e.g. 123456')
                                            ->required()
                                            ->numeric()
                                            ->minLength(6)
                                            ->maxLength(6)
                                            ->rule(function (Forms\Get $get) {
                                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                    $secret = $get('secret_key');
                                                    if (! \App\Helpers\TotpHelper::verify($secret, $value)) {
                                                        $fail('Invalid verification code.');
                                                    }
                                                };
                                            }),
                                    ];
                                })
                                ->action(function (array $data) {
                                    $user = auth()->user();
                                    
                                    if ($this->otp_enabled) {
                                        // Disable
                                        $user->otp_enabled = false;
                                        $user->otp_secret = null;
                                        $user->save();
                                        
                                        Notification::make()->success()->title('2FA Disabled')->send();
                                    } else {
                                        // Enable
                                        $user->otp_enabled = true;
                                        $user->otp_secret = $data['secret_key']; 
                                        $user->save();
                                        
                                        session(['2fa_verified' => true]); // Determine they are verified since they just set it up
                                        Notification::make()->success()->title('2FA Enabled Successfully')->send();
                                    }
                                    
                                    $this->otp_enabled = $user->otp_enabled;
                                }),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        // Update Profile
        $user->name = $data['name'];
        
        // Only update email if admin
        if (auth()->user()->hasRole('super_admin') && isset($data['email'])) {
            $user->email = $data['email'];
        }

        // Update Password
        if (!empty($data['new_password'])) {
            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        Notification::make() 
            ->success()
            ->title('Profile saved')
            ->send();
            
        // Refill form
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'new_password' => '',
            'new_password_confirmation' => '',
        ]);
    }
}
