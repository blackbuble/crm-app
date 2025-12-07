{{-- resources/views/filament/resources/customer-resource/pages/customer-kanban.blade.php --}}
<x-filament-panels::page>
    <div 
        x-data="{
            customers: @js($this->getCustomersByStatus()),
            draggedItem: null,
            
            dragStart(event, customerId, status) {
                this.draggedItem = { id: customerId, status: status };
                event.dataTransfer.effectAllowed = 'move';
                event.target.classList.add('opacity-50');
            },
            
            dragEnd(event) {
                event.target.classList.remove('opacity-50');
            },
            
            dragOver(event) {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
            },
            
            drop(event, newStatus) {
                event.preventDefault();
                if (this.draggedItem && this.draggedItem.status !== newStatus) {
                    // Call Livewire method to update in database
                    $wire.updateCustomerStatus(this.draggedItem.id, newStatus);
                    
                    // Move item visually
                    const item = this.customers[this.draggedItem.status].find(c => c.id === this.draggedItem.id);
                    if (item) {
                        this.customers[this.draggedItem.status] = this.customers[this.draggedItem.status].filter(c => c.id !== this.draggedItem.id);
                        this.customers[newStatus].push(item);
                    }
                }
                this.draggedItem = null;
            },
            
            getStatusColor(status) {
                const colors = {
                    'lead': 'bg-yellow-500',
                    'prospect': 'bg-blue-500',
                    'customer': 'bg-green-500',
                    'inactive': 'bg-red-500'
                };
                return colors[status] || 'bg-gray-500';
            },
            
            getStatusLabel(status) {
                const labels = {
                    'lead': 'Leads',
                    'prospect': 'Prospects',
                    'customer': 'Customers',
                    'inactive': 'Inactive'
                };
                return labels[status] || status;
            }
        }" 
        class="space-y-4"
        x-on:customer-updated.window="customers = $wire.getCustomersByStatus()"
    >
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Lead Column --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="bg-yellow-500 text-white px-4 py-3 font-semibold">
                    <div class="flex items-center justify-between">
                        <span>Leads</span>
                        <span class="bg-yellow-600 px-2 py-1 rounded text-sm" x-text="customers.lead.length"></span>
                    </div>
                </div>
                <div 
                    @dragover="dragOver($event)"
                    @drop="drop($event, 'lead')"
                    class="p-4 space-y-3 min-h-[500px] bg-gray-50 dark:bg-gray-900"
                >
                    <template x-for="customer in customers.lead" :key="customer.id">
                        <div 
                            draggable="true"
                            @dragstart="dragStart($event, customer.id, 'lead')"
                            @dragend="dragEnd($event)"
                            class="bg-white dark:bg-gray-700 p-4 rounded-lg cursor-move hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600"
                        >
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-1" x-text="customer.name"></h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1" x-show="customer.email" x-text="customer.email"></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2" x-show="customer.phone" x-text="customer.phone"></p>
                            
                            <div class="mt-2 flex flex-wrap gap-1" x-show="customer.tags.length > 0">
                                <template x-for="tag in customer.tags" :key="tag">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300" x-text="tag"></span>
                                </template>
                            </div>
                            
                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.follow_ups_count + ' follow-ups'"></span>
                                </span>
                                <span x-show="customer.next_follow_up" class="text-blue-600 dark:text-blue-400" x-text="customer.next_follow_up"></span>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="customers.lead.length === 0" class="text-center text-gray-500 py-8">
                        No leads yet
                    </div>
                </div>
            </div>

            {{-- Prospect Column --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="bg-blue-500 text-white px-4 py-3 font-semibold">
                    <div class="flex items-center justify-between">
                        <span>Prospects</span>
                        <span class="bg-blue-600 px-2 py-1 rounded text-sm" x-text="customers.prospect.length"></span>
                    </div>
                </div>
                <div 
                    @dragover="dragOver($event)"
                    @drop="drop($event, 'prospect')"
                    class="p-4 space-y-3 min-h-[500px] bg-gray-50 dark:bg-gray-900"
                >
                    <template x-for="customer in customers.prospect" :key="customer.id">
                        <div 
                            draggable="true"
                            @dragstart="dragStart($event, customer.id, 'prospect')"
                            @dragend="dragEnd($event)"
                            class="bg-white dark:bg-gray-700 p-4 rounded-lg cursor-move hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600"
                        >
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-1" x-text="customer.name"></h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1" x-show="customer.email" x-text="customer.email"></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2" x-show="customer.phone" x-text="customer.phone"></p>
                            
                            <div class="mt-2 flex flex-wrap gap-1" x-show="customer.tags.length > 0">
                                <template x-for="tag in customer.tags" :key="tag">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300" x-text="tag"></span>
                                </template>
                            </div>
                            
                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.follow_ups_count + ' follow-ups'"></span>
                                </span>
                                <span x-show="customer.next_follow_up" class="text-blue-600 dark:text-blue-400" x-text="customer.next_follow_up"></span>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="customers.prospect.length === 0" class="text-center text-gray-500 py-8">
                        No prospects yet
                    </div>
                </div>
            </div>

            {{-- Customer Column --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="bg-green-500 text-white px-4 py-3 font-semibold">
                    <div class="flex items-center justify-between">
                        <span>Customers</span>
                        <span class="bg-green-600 px-2 py-1 rounded text-sm" x-text="customers.customer.length"></span>
                    </div>
                </div>
                <div 
                    @dragover="dragOver($event)"
                    @drop="drop($event, 'customer')"
                    class="p-4 space-y-3 min-h-[500px] bg-gray-50 dark:bg-gray-900"
                >
                    <template x-for="customer in customers.customer" :key="customer.id">
                        <div 
                            draggable="true"
                            @dragstart="dragStart($event, customer.id, 'customer')"
                            @dragend="dragEnd($event)"
                            class="bg-white dark:bg-gray-700 p-4 rounded-lg cursor-move hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600"
                        >
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-1" x-text="customer.name"></h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1" x-show="customer.email" x-text="customer.email"></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2" x-show="customer.phone" x-text="customer.phone"></p>
                            
                            <div class="mt-2 flex flex-wrap gap-1" x-show="customer.tags.length > 0">
                                <template x-for="tag in customer.tags" :key="tag">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300" x-text="tag"></span>
                                </template>
                            </div>
                            
                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.follow_ups_count + ' follow-ups'"></span>
                                </span>
                                <span x-show="customer.next_follow_up" class="text-blue-600 dark:text-blue-400" x-text="customer.next_follow_up"></span>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="customers.customer.length === 0" class="text-center text-gray-500 py-8">
                        No customers yet
                    </div>
                </div>
            </div>

            {{-- Inactive Column --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="bg-red-500 text-white px-4 py-3 font-semibold">
                    <div class="flex items-center justify-between">
                        <span>Inactive</span>
                        <span class="bg-red-600 px-2 py-1 rounded text-sm" x-text="customers.inactive.length"></span>
                    </div>
                </div>
                <div 
                    @dragover="dragOver($event)"
                    @drop="drop($event, 'inactive')"
                    class="p-4 space-y-3 min-h-[500px] bg-gray-50 dark:bg-gray-900"
                >
                    <template x-for="customer in customers.inactive" :key="customer.id">
                        <div 
                            draggable="true"
                            @dragstart="dragStart($event, customer.id, 'inactive')"
                            @dragend="dragEnd($event)"
                            class="bg-white dark:bg-gray-700 p-4 rounded-lg cursor-move hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600"
                        >
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-1" x-text="customer.name"></h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1" x-show="customer.email" x-text="customer.email"></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2" x-show="customer.phone" x-text="customer.phone"></p>
                            
                            <div class="mt-2 flex flex-wrap gap-1" x-show="customer.tags.length > 0">
                                <template x-for="tag in customer.tags" :key="tag">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300" x-text="tag"></span>
                                </template>
                            </div>
                            
                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.follow_ups_count + ' follow-ups'"></span>
                                </span>
                                <span x-show="customer.next_follow_up" class="text-blue-600 dark:text-blue-400" x-text="customer.next_follow_up"></span>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="customers.inactive.length === 0" class="text-center text-gray-500 py-8">
                        No inactive customers
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>