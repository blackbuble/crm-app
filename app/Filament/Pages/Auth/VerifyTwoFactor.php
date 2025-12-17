<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use App\Helpers\TotpHelper;
use Filament\Notifications\Notification;

class VerifyTwoFactor extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Two-Factor Authentication';
    protected static ?string $slug = 'auth/verify-two-factor';
    
    // Use Simple Layout (looks like Login page)
    protected static string $layout = 'filament-panels::components.layout.simple';
    
    protected static string $view = 'filament.pages.auth.verify-two-factor';

    public ?string $code = '';

    public function hasLogo(): bool
    {
        return false;
    }

    public function mount(): void
    {
        if (session('2fa_verified')) {
            redirect()->to(filament()->getHomeUrl());
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Verification Code')
                    ->placeholder('123456')
                    ->required()
                    ->numeric()
                    ->autofocus()
                    ->password()
                    ->revealable(false)
                    ->extraInputAttributes(['class' => 'text-center text-xl tracking-widest style-digits']),
            ])
            ->statePath('data');
    }
    
    public $data = [];

    public function authenticate(): void
    {
        $data = $this->form->getState();
        $code = $data['code'];
        $user = auth()->user();

        if (! $user->otp_secret) {
            // Should not happen if check passed, but safety
             Notification::make()->danger()->title('OTP not configured')->send();
             return;
        }

        if (TotpHelper::verify($user->otp_secret, $code)) {
            session(['2fa_verified' => true]);
            
            Notification::make()->success()->title('Verified')->send();
            
            redirect()->to(filament()->getHomeUrl());
        } else {
            Notification::make()->danger()->title('Invalid Code')->body('Please try again.')->send();
            $this->addError('data.code', 'Invalid code.');
        }
    }
}
