<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Filament\Resources\CustomerResource;

class TestNotification extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    
    protected static ?string $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 99;
    
    protected static string $view = 'filament.pages.test-notification';
    
    protected static ?string $title = 'Test Notifications';
    
    public static function canAccess(): bool
    {
        // Only for super_admin
        return auth()->user()->hasRole('super_admin');
    }
    
    public function sendTestNotification()
    {
        Notification::make()
            ->title('Test Notification')
            ->body('This is a test notification to verify the notification system is working.')
            ->icon('heroicon-o-bell')
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->label('View Dashboard')
                    ->url(route('filament.admin.pages.dashboard'))
                    ->button(),
            ])
            ->sendToDatabase(auth()->user())
            ->send();
            
        Notification::make()
            ->title('Success!')
            ->body('Test notification has been sent to your notifications.')
            ->success()
            ->send();
    }
    
    public function sendToAllUsers()
    {
        $users = \App\Models\User::all();
        
        foreach ($users as $user) {
            Notification::make()
                ->title('System Notification')
                ->body('This is a broadcast notification to all users.')
                ->icon('heroicon-o-megaphone')
                ->iconColor('info')
                ->sendToDatabase($user);
        }
        
        Notification::make()
            ->title('Success!')
            ->body('Notification sent to ' . $users->count() . ' users.')
            ->success()
            ->send();
    }
}
