<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class CustomerInactiveNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Customer $customer,
        public ?string $assignedUserName = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Customer Inactive',
            'body' => "{$this->customer->name} has been marked as inactive (not interested)",
            'icon' => 'heroicon-o-exclamation-triangle',
            'iconColor' => 'warning',
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'actions' => [
                [
                    'label' => 'View Customer',
                    'url' => route('filament.admin.resources.customers.edit', ['record' => $this->customer->id]),
                ],
            ],
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
