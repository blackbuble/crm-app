<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Customer $customer,
        public string $type // 'assigned' or 'manager'
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
            'icon' => 'heroicon-o-user-plus',
            'iconColor' => 'success',
            'actions' => [
                [
                    'label' => 'View Customer',
                    'url' => route('filament.admin.resources.customers.edit', ['record' => $this->customer->id]),
                ],
            ],
        ];

        if ($this->type === 'assigned') {
            $data['title'] = 'New Customer Assigned';
            $data['body'] = "You have been assigned a new customer: {$this->customer->name}";
        } else { // manager or admin (if unassigned)
            $data['title'] = 'New Customer Created';
            if ($this->customer->assignedUser) {
                $data['title'] = 'Customer Assigned to Team Member';
                $data['body'] = "{$this->customer->name} has been assigned to {$this->customer->assignedUser->name}";
            } else {
                $data['body'] = "New unassigned customer created: {$this->customer->name}";
            }
            $data['iconColor'] = 'info';
        }

        return $data;
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
