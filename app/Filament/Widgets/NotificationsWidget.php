<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class NotificationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.notifications-widget';
    protected static ?int $sort = -1;
    protected int | string | array $columnSpan = 'full';

    public function getUnreadCount(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function getRecentNotifications()
    {
        return auth()->user()
            ->notifications()
            ->take(5)
            ->get();
    }
}