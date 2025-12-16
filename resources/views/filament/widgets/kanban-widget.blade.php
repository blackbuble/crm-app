<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span>📊 Customer Pipeline</span>
                <a href="/admin/customers/kanban" class="text-xs text-gray-500 hover:text-gray-700 ml-auto">
                    View Full Kanban →
                </a>
            </div>
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $stats = $this->getCustomerStats();
                $recent = $this->getRecentCustomers();
                
                $columns = [
                    'lead' => ['label' => 'Leads', 'color' => 'amber', 'emoji' => '🎯'],
                    'prospect' => ['label' => 'Prospects', 'color' => 'blue', 'emoji' => '🎯'],
                    'customer' => ['label' => 'Customers', 'color' => 'green', 'emoji' => '✅'],
                    'inactive' => ['label' => 'Inactive', 'color' => 'gray', 'emoji' => '💤'],
                ];
            @endphp

            @foreach($columns as $status => $config)
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ $config['emoji'] }} {{ $config['label'] }}
                        </span>
                        <span class="bg-{{ $config['color'] }}-100 dark:bg-{{ $config['color'] }}-900 text-{{ $config['color'] }}-700 dark:text-{{ $config['color'] }}-300 px-2 py-0.5 rounded-full text-xs font-bold">
                            {{ $stats[$status] }}
                        </span>
                    </div>

                    {{-- Recent Customers --}}
                    <div class="space-y-2">
                        @forelse($recent[$status] as $customer)
                            <div class="bg-white dark:bg-gray-700 p-2 rounded border-l-2 border-{{ $config['color'] }}-500">
                                <div class="text-xs font-medium text-gray-900 dark:text-white truncate">
                                    {{ $customer['name'] }}
                                </div>
                                @if($customer['email'])
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $customer['email'] }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-gray-400 text-xs py-4">
                                No {{ strtolower($config['label']) }}
                            </div>
                        @endforelse

                        @if(count($recent[$status]) > 0 && $stats[$status] > 3)
                            <div class="text-center text-xs text-gray-500 pt-2">
                                +{{ $stats[$status] - 3 }} more
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
