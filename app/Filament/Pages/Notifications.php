<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Notifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    
    protected static string $view = 'filament.pages.notifications';
    
    protected static ?string $title = 'Notifications';
    
    protected static ?string $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 99;
    
    public static function getNavigationBadge(): ?string
    {
        $count = auth()->user()->unreadNotifications()->count();
        return $count > 0 ? (string) $count : null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
    
    public function getHeading(): string | Htmlable
    {
        return 'Notifications';
    }
    
    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }
    
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }
    
    public function deleteNotification($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->delete();
        }
    }
}
