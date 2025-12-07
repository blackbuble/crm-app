<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use Filament\Resources\Pages\Page;

class CustomerKanban extends Page
{
    protected static string $resource = CustomerResource::class;
    protected static string $view = 'filament.resources.customer-resource.pages.customer-kanban';
    protected static ?string $title = 'Customer Pipeline';
    protected static ?string $navigationLabel = 'Kanban Board';

    public function getCustomersByStatus(): array
    {
        $statuses = ['lead', 'prospect', 'customer', 'inactive'];
        $data = [];

        foreach ($statuses as $status) {
            $data[$status] = Customer::with(['followUps', 'tags'])
                ->where('status', $status)
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->display_name ?? $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'tags' => $customer->tags->pluck('name')->toArray(),
                        'follow_ups_count' => $customer->followUps()->count(),
                        'next_follow_up' => $customer->followUps()
                            ->where('status', 'pending')
                            ->where('follow_up_date', '>=', now())
                            ->orderBy('follow_up_date')
                            ->first()?->follow_up_date?->format('M d, Y'),
                    ];
                })->toArray();
        }

        return $data;
    }

    public function updateCustomerStatus(int $customerId, string $newStatus): void
    {
        Customer::find($customerId)->update(['status' => $newStatus]);
        $this->dispatch('customer-updated');
    }
}