<?php

namespace App\Filament\Resources;

use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationHelper
{
    /**
     * Send notification directly to database in Filament format
     */
    public static function sendToDatabase(
        User|array $recipients,
        string $title,
        string $body,
        string $icon = 'heroicon-o-bell',
        string $iconColor = 'info',
        ?string $url = null,
        ?string $actionLabel = null
    ): void {
        $recipients = is_array($recipients) ? $recipients : [$recipients];
        
        foreach ($recipients as $recipient) {
            // Build notification data in Filament format
            $data = [
                'title' => $title,
                'body' => $body,
                'icon' => $icon,
                'iconColor' => $iconColor,
                'format' => 'filament',
            ];
            
            if ($url && $actionLabel) {
                $data['actions'] = [
                    [
                        'name' => 'view',
                        'label' => $actionLabel,
                        'url' => $url,
                        'button' => true,
                    ],
                ];
            }
            
            // Insert directly to database
            \DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'Filament\\Notifications\\DatabaseNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $recipient->id,
                'data' => json_encode($data),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            \Log::info('✅ Filament notification inserted to database', [
                'recipient_id' => $recipient->id,
                'title' => $title,
            ]);
        }
    }
    
    /**
     * Customer Inactive Notification
     */
    public static function customerInactive(Customer $customer, User $recipient): void
    {
        self::sendToDatabase(
            recipients: $recipient,
            title: 'Customer Inactive',
            body: "{$customer->name} has been marked as inactive (not interested)",
            icon: 'heroicon-o-exclamation-triangle',
            iconColor: 'warning',
            url: CustomerResource::getUrl('edit', ['record' => $customer]),
            actionLabel: 'View Customer'
        );
    }
    
    /**
     * Customer Conversion Notification
     */
    public static function customerConversion(Customer $customer, string $salesRepName, User|array $recipients): void
    {
        self::sendToDatabase(
            recipients: $recipients,
            title: '🎉 Customer Conversion!',
            body: "{$salesRepName} successfully converted {$customer->name} to a customer!",
            icon: 'heroicon-o-trophy',
            iconColor: 'success',
            url: CustomerResource::getUrl('edit', ['record' => $customer]),
            actionLabel: 'View Customer'
        );
    }
    
    /**
     * Customer Created Notification
     */
    public static function customerCreated(Customer $customer, User $recipient, bool $isAssigned = true): void
    {
        if ($isAssigned) {
            self::sendToDatabase(
                recipients: $recipient,
                title: 'New Customer Assigned',
                body: "You have been assigned a new customer: {$customer->name}",
                icon: 'heroicon-o-user-plus',
                iconColor: 'success',
                url: CustomerResource::getUrl('edit', ['record' => $customer]),
                actionLabel: 'View Customer'
            );
        } else {
            self::sendToDatabase(
                recipients: $recipient,
                title: 'Customer Assigned to Team Member',
                body: "{$customer->name} has been assigned to {$customer->assignedUser->name}",
                icon: 'heroicon-o-users',
                iconColor: 'info',
                url: CustomerResource::getUrl('edit', ['record' => $customer]),
                actionLabel: 'View Customer'
            );
        }
    }
    
    /**
     * Customer Reassigned Notification
     */
    public static function customerReassigned(
        Customer $customer, 
        User $recipient, 
        string $type, 
        ?string $oldUserName = null,
        ?string $newUserName = null
    ): void {
        if ($type === 'old') {
            self::sendToDatabase(
                recipients: $recipient,
                title: 'Customer Reassigned',
                body: "{$customer->name} has been reassigned to {$newUserName}",
                icon: 'heroicon-o-arrow-path',
                iconColor: 'warning',
                url: CustomerResource::getUrl('edit', ['record' => $customer]),
                actionLabel: 'View Customer'
            );
        } elseif ($type === 'new') {
            self::sendToDatabase(
                recipients: $recipient,
                title: 'New Customer Assigned',
                body: "You have been assigned: {$customer->name}",
                icon: 'heroicon-o-user-plus',
                iconColor: 'success',
                url: CustomerResource::getUrl('edit', ['record' => $customer]),
                actionLabel: 'View Customer'
            );
        } else { // manager
            self::sendToDatabase(
                recipients: $recipient,
                title: 'Customer Reassigned',
                body: "{$customer->name} reassigned from {$oldUserName} to {$newUserName}",
                icon: 'heroicon-o-arrow-path',
                iconColor: 'info',
                url: CustomerResource::getUrl('edit', ['record' => $customer]),
                actionLabel: 'View Customer'
            );
        }
    }
}
