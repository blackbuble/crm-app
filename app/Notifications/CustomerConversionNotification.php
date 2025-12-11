<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerConversionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Customer $customer,
        public string $salesRepName
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Customer Conversion! 🎉',
            'body' => "{$this->salesRepName} successfully converted {$this->customer->name} to a customer!",
            'icon' => 'heroicon-o-trophy',
            'iconColor' => 'success',
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'sales_rep' => $this->salesRepName,
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
