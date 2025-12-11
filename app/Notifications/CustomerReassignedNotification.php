<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerReassignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Customer $customer,
        public string $type, // 'old', 'new', or 'manager'
        public ?string $oldUserName = null,
        public ?string $newUserName = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $data = [
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'icon' => 'heroicon-o-arrow-path',
            'iconColor' => 'info',
            'actions' => [
                [
                    'label' => 'View Customer',
                    'url' => route('filament.admin.resources.customers.edit', ['record' => $this->customer->id]),
                ],
            ],
        ];

        if ($this->type === 'old') {
            $data['title'] = 'Customer Reassigned';
            $data['body'] = "{$this->customer->name} has been reassigned to {$this->newUserName}";
        } elseif ($this->type === 'new') {
            $data['title'] = 'New Customer Assigned';
            $data['body'] = "You have been assigned: {$this->customer->name}";
            $data['iconColor'] = 'success';
        } else { // manager
            $data['title'] = 'Customer Reassigned';
            $data['body'] = "{$this->customer->name} reassigned from {$this->oldUserName} to {$this->newUserName}";
        }

        return $data;
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
