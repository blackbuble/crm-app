<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Test Notification System
            </x-slot>
            
            <x-slot name="description">
                Use these buttons to test if the notification system is working correctly.
            </x-slot>
            
            <div class="space-y-4">
                <div>
                    <x-filament::button wire:click="sendTestNotification" color="success" icon="heroicon-o-bell">
                        Send Test Notification to Me
                    </x-filament::button>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        This will send a test notification to your account. Check the bell icon in the navbar.
                    </p>
                </div>
                
                <div class="border-t pt-4">
                    <x-filament::button wire:click="sendToAllUsers" color="warning" icon="heroicon-o-megaphone">
                        Send Notification to All Users
                    </x-filament::button>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        This will send a notification to all users in the system.
                    </p>
                </div>
            </div>
        </x-filament::section>
        
        <x-filament::section>
            <x-slot name="heading">
                Notification Configuration
            </x-slot>
            
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="font-medium">Database Notifications:</span>
                    <span class="text-green-600 dark:text-green-400">✓ Enabled</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Polling Interval:</span>
                    <span>30 seconds</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Notifiable Model:</span>
                    <span>App\Models\User</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Notifications Table:</span>
                    <span class="text-green-600 dark:text-green-400">✓ Exists</span>
                </div>
            </div>
        </x-filament::section>
        
        <x-filament::section>
            <x-slot name="heading">
                How to Check Notifications
            </x-slot>
            
            <div class="prose dark:prose-invert max-w-none">
                <ol>
                    <li>Click the "Send Test Notification to Me" button above</li>
                    <li>Look for the bell icon (🔔) in the top-right navbar</li>
                    <li>You should see a red badge with the number of unread notifications</li>
                    <li>Click the bell icon to view your notifications</li>
                    <li>Click on a notification to mark it as read</li>
                </ol>
                
                <h4>Troubleshooting</h4>
                <ul>
                    <li>If no bell icon appears, check that <code>databaseNotifications()</code> is enabled in AdminPanelProvider</li>
                    <li>If notifications don't appear, check the database <code>notifications</code> table</li>
                    <li>Make sure the User model uses the <code>Notifiable</code> trait</li>
                    <li>Clear cache: <code>php artisan cache:clear</code></li>
                </ul>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
