{{-- resources/views/filament/pages/reports.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form wire:submit="generateReport">
                {{ $this->form }}
                
                <div class="mt-4">
                    <x-filament::button type="submit">
                        Generate Report
                    </x-filament::button>
                </div>
            </form>
        </div>

        @php
            $data = $this->getReportData();
        @endphp

        {{-- Overview Report --}}
        @if($reportType === 'overview')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">New Customers</h3>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $data['total_customers'] }}</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Follow-ups</h3>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $data['total_followups'] }}</p>
                    <p class="text-sm text-gray-500">{{ $data['completed_followups'] }} completed</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Quotations</h3>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $data['quotation_count'] }}</p>
                    <p class="text-sm text-gray-500">Rp {{ number_format($data['total_quotations'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Accepted Value</h3>
                    <p class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($data['accepted_quotations'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Customers by Status</h3>
                <div class="grid grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded">
                        <p class="text-2xl font-bold text-yellow-600">{{ $data['customers_by_status']['leads'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Leads</p>
                    </div>
                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded">
                        <p class="text-2xl font-bold text-blue-600">{{ $data['customers_by_status']['prospects'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Prospects</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded">
                        <p class="text-2xl font-bold text-green-600">{{ $data['customers_by_status']['customers'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Customers</p>
                    </div>
                    <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded">
                        <p class="text-2xl font-bold text-red-600">{{ $data['customers_by_status']['inactive'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Inactive</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Customer Report --}}
        @if($reportType === 'customers')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Customer Type Distribution</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Company:</span>
                            <span class="font-semibold">{{ $data['by_type']['company'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Personal:</span>
                            <span class="font-semibold">{{ $data['by_type']['personal'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Top Customers</h3>
                    <div class="space-y-2">
                        @foreach($data['top_customers'] as $customer)
                            <div class="flex justify-between text-sm">
                                <span>{{ $customer->name }}</span>
                                <span class="font-semibold">{{ $customer->quotations_count }} quotes</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Follow-up Report --}}
        @if($reportType === 'followups')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">By Type</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>WhatsApp:</span>
                            <span class="font-semibold">{{ $data['by_type']['whatsapp'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Phone:</span>
                            <span class="font-semibold">{{ $data['by_type']['phone'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Email:</span>
                            <span class="font-semibold">{{ $data['by_type']['email'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">By Status</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Pending:</span>
                            <span class="font-semibold text-yellow-600">{{ $data['by_status']['pending'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Completed:</span>
                            <span class="font-semibold text-green-600">{{ $data['by_status']['completed'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Cancelled:</span>
                            <span class="font-semibold text-red-600">{{ $data['by_status']['cancelled'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Completion Rate</h3>
                    <p class="text-4xl font-bold text-center text-green-600">{{ $data['completion_rate'] }}%</p>
                    <p class="text-center text-sm text-gray-500 mt-2">Total: {{ $data['total'] }}</p>
                </div>
            </div>
        @endif

        {{-- Quotation Report --}}
        @if($reportType === 'quotations')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Value</h3>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($data['total_value'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Value</h3>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($data['average_value'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Conversion Rate</h3>
                    <p class="mt-2 text-4xl font-bold text-center text-green-600">{{ $data['conversion_rate'] }}%</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">By Status</h3>
                <div class="grid grid-cols-4 gap-4">
                    @foreach($data['by_status'] as $status => $count)
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded">
                            <p class="text-2xl font-bold">{{ $count }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($status) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Top Quotations</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2">Customer</th>
                                <th class="text-left py-2">Quotation #</th>
                                <th class="text-right py-2">Amount</th>
                                <th class="text-left py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['top_quotations'] as $quote)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="py-2">{{ $quote->customer->name }}</td>
                                    <td class="py-2">{{ $quote->quotation_number }}</td>
                                    <td class="text-right py-2">Rp {{ number_format($quote->total, 0, ',', '.') }}</td>
                                    <td class="py-2">
                                        <span class="px-2 py-1 rounded text-xs {{ $quote->status === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($quote->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>