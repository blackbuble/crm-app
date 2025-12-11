<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class CustomerStatusWidget extends ChartWidget
{
    use HasWidgetShield;
    
    protected static ?string $heading = 'Customer by Status';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $leads = Customer::where('status', 'lead')->count();
        $prospects = Customer::where('status', 'prospect')->count();
        $customers = Customer::where('status', 'customer')->count();
        $inactive = Customer::where('status', 'inactive')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => [$leads, $prospects, $customers, $inactive],
                    'backgroundColor' => [
                        '#fbbf24',
                        '#60a5fa',
                        '#34d399',
                        '#f87171',
                    ],
                ],
            ],
            'labels' => ['Leads', 'Prospects', 'Customers', 'Inactive'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
