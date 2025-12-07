{{-- resources/views/filament/widgets/notifications-widget.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Recent Notifications</h3>
                @if($this->getUnreadCount() > 0)
                    <x-filament::badge color="danger">
                        {{ $this->getUnreadCount() }} new
                    </x-filament::badge>
                @endif
            </div>

            <div class="space-y-2">
                @forelse($this->getRecentNotifications() as $notification)
                    <div class="flex items-start gap-3 p-3 rounded-lg {{ $notification->read_at ? 'bg-gray-50 dark:bg-gray-800' : 'bg-blue-50 dark:bg-blue-900/20' }}">
                        <div class="flex-shrink-0">
                            @if(isset($notification->data['icon']))
                                <x-filament::icon 
                                    :icon="$notification->data['icon']" 
                                    class="w-5 h-5 {{ isset($notification->data['iconColor']) ? 'text-' . $notification->data['iconColor'] . '-500' : 'text-gray-500' }}"
                                />
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                {{ $notification->data['body'] ?? '' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p class="text-sm text-gray-600 dark:text-gray-400">No notifications yet</p>
                    </div>
                @endforelse
            </div>

            @if($this->getRecentNotifications()->count() > 0)
                <div class="text-center">
                    <a href="{{ route('filament.admin.pages.notifications') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                        View all notifications →
                    </a>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>