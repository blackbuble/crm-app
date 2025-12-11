<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Header Actions --}}
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold">Notifications</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    You have {{ auth()->user()->unreadNotifications()->count() }} unread notifications
                </p>
            </div>
            
            @if(auth()->user()->unreadNotifications()->count() > 0)
                <x-filament::button 
                    wire:click="markAllAsRead"
                    color="gray"
                    size="sm"
                >
                    Mark all as read
                </x-filament::button>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="space-y-2">
            @forelse(auth()->user()->notifications()->latest()->get() as $notification)
                <div 
                    class="p-4 rounded-lg border {{ $notification->read_at ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' : 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-700' }}"
                    wire:key="notification-{{ $notification->id }}"
                >
                    <div class="flex items-start gap-4">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 mt-1">
                            @if(isset($notification->data['icon']))
                                @php
                                    $iconColor = $notification->data['iconColor'] ?? 'gray';
                                    $colorClass = match($iconColor) {
                                        'success' => 'text-success-500',
                                        'warning' => 'text-warning-500',
                                        'danger' => 'text-danger-500',
                                        'info' => 'text-info-500',
                                        default => 'text-gray-500',
                                    };
                                @endphp
                                <x-filament::icon 
                                    :icon="$notification->data['icon']" 
                                    class="w-6 h-6 {{ $colorClass }}"
                                />
                            @else
                                <x-filament::icon 
                                    icon="heroicon-o-bell" 
                                    class="w-6 h-6 text-gray-500"
                                />
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $notification->data['title'] ?? 'Notification' }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $notification->data['body'] ?? '' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2">
                                    @if(!$notification->read_at)
                                        <x-filament::button
                                            wire:click="markAsRead('{{ $notification->id }}')"
                                            color="gray"
                                            size="xs"
                                            outlined
                                        >
                                            Mark as read
                                        </x-filament::button>
                                    @endif
                                    
                                    <x-filament::button
                                        wire:click="deleteNotification('{{ $notification->id }}')"
                                        color="danger"
                                        size="xs"
                                        outlined
                                    >
                                        Delete
                                    </x-filament::button>
                                </div>
                            </div>

                            {{-- Action Buttons from Notification --}}
                            @if(isset($notification->data['actions']) && is_array($notification->data['actions']))
                                <div class="flex gap-2 mt-3">
                                    @foreach($notification->data['actions'] as $action)
                                        <a 
                                            href="{{ $action['url'] ?? '#' }}"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                                        >
                                            {{ $action['label'] ?? 'View' }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <x-filament::icon 
                        icon="heroicon-o-bell-slash" 
                        class="w-16 h-16 text-gray-400 mx-auto mb-4"
                    />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No notifications yet
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        You'll see notifications here when you receive them
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
