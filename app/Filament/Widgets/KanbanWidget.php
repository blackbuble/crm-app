<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\Widget;

class KanbanWidget extends Widget
{
    protected static string $view = 'filament.widgets.kanban-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;
    
    public function getCustomerStats(): array
    {
        return [
            'lead' => Customer::where('status', 'lead')->count(),
            'prospect' => Customer::where('status', 'prospect')->count(),
            'customer' => Customer::where('status', 'customer')->count(),
            'inactive' => Customer::where('status', 'inactive')->count(),
        ];
    }
    
    public function getRecentCustomers(): array
    {
        $statuses = ['lead', 'prospect', 'customer', 'inactive'];
        $data = [];
        
        foreach ($statuses as $status) {
            $data[$status] = Customer::where('status', $status)
                ->latest()
                ->take(3)
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->display_name ?? $customer->name,
                        'email' => $customer->email,
                    ];
                })
                ->toArray();
        }
        
        return $data;
    }
}
