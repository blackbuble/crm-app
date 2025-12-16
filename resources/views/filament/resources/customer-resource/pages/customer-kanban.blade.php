{{-- resources/views/filament/resources/customer-resource/pages/customer-kanban.blade.php --}}
<x-filament-panels::page>
    {{-- Custom Styles for Trello-like Kanban --}}
    <style>
        .kanban-column {
            background: #f1f2f4;
            border-radius: 12px;
            min-height: 600px;
        }
        
        .kanban-column-header {
            padding: 12px 16px;
            font-weight: 600;
            font-size: 14px;
            border-radius: 12px 12px 0 0;
        }
        
        .kanban-card {
            background: white;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            cursor: grab;
            transition: all 0.2s ease;
            border-left: 4px solid;
        }
        
        .kanban-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .kanban-card:active {
            cursor: grabbing;
        }
        
        .kanban-card.dragging {
            opacity: 0.5;
            transform: rotate(2deg);
        }
        
        .kanban-card-title {
            font-weight: 600;
            font-size: 14px;
            color: #172b4d;
            margin-bottom: 8px;
        }
        
        .kanban-card-meta {
            font-size: 12px;
            color: #5e6c84;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 4px;
        }
        
        .kanban-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            margin-right: 4px;
            margin-bottom: 4px;
        }
        
        .kanban-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }
        
        .kanban-action-btn {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .kanban-action-btn:hover {
            background: #f1f2f4;
        }
        
        .add-card-btn {
            width: 100%;
            padding: 8px;
            text-align: left;
            color: #5e6c84;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 14px;
        }
        
        .add-card-btn:hover {
            background: #e2e8f0;
            color: #172b4d;
        }
        
        .drop-zone-active {
            background: #e2e8f0;
            border: 2px dashed #94a3b8;
        }
        
        /* Status Colors */
        .status-lead { border-left-color: #f59e0b; }
        .status-prospect { border-left-color: #3b82f6; }
        .status-customer { border-left-color: #10b981; }
        .status-inactive { border-left-color: #6b7280; }
        
        .header-lead { background: #fef3c7; color: #92400e; }
        .header-prospect { background: #dbeafe; color: #1e40af; }
        .header-customer { background: #d1fae5; color: #065f46; }
        .header-inactive { background: #f3f4f6; color: #374151; }
    </style>

    <div 
        x-data="{
            customers: @js($this->getCustomersByStatus()),
            draggedItem: null,
            
            dragStart(event, customerId, status) {
                this.draggedItem = { id: customerId, status: status };
                event.dataTransfer.effectAllowed = 'move';
                event.target.classList.add('dragging');
            },
            
            dragEnd(event) {
                event.target.classList.remove('dragging');
            },
            
            dragOver(event) {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
            },
            
            dragEnter(event) {
                if (event.target.classList.contains('kanban-drop-zone')) {
                    event.target.classList.add('drop-zone-active');
                }
            },
            
            dragLeave(event) {
                if (event.target.classList.contains('kanban-drop-zone')) {
                    event.target.classList.remove('drop-zone-active');
                }
            },
            
            drop(event, newStatus) {
                event.preventDefault();
                event.target.classList.remove('drop-zone-active');
                
                if (this.draggedItem && this.draggedItem.status !== newStatus) {
                    $wire.updateCustomerStatus(this.draggedItem.id, newStatus);
                    
                    const item = this.customers[this.draggedItem.status].find(c => c.id === this.draggedItem.id);
                    if (item) {
                        this.customers[this.draggedItem.status] = this.customers[this.draggedItem.status].filter(c => c.id !== this.draggedItem.id);
                        this.customers[newStatus].push(item);
                    }
                }
                this.draggedItem = null;
            },
            
            viewCustomer(id) {
                window.location.href = '/admin/customers/' + id + '/edit';
            },
            
            callCustomer(phone, countryCode) {
                if (phone) {
                    const whatsappUrl = 'https://wa.me/' + (countryCode || '+62').replace('+', '') + phone.replace(/^0/, '');
                    window.open(whatsappUrl, '_blank');
                }
            }
        }" 
        class="space-y-4"
        x-on:customer-updated.window="customers = $wire.getCustomersByStatus()"
    >
        
        {{-- Kanban Board Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- LEAD Column --}}
            <div class="kanban-column">
                <div class="kanban-column-header header-lead">
                    <div class="flex items-center justify-between">
                        <span>🎯 Leads</span>
                        <span class="bg-amber-200 px-2 py-0.5 rounded-full text-xs font-bold" x-text="customers.lead.length"></span>
                    </div>
                </div>
                
                <div 
                    @dragover="dragOver($event)"
                    @dragenter="dragEnter($event)"
                    @dragleave="dragLeave($event)"
                    @drop="drop($event, 'lead')"
                    class="p-3 kanban-drop-zone"
                >
                    <template x-for="customer in customers.lead" :key="customer.id">
                        <div 
                            draggable="true"
                            @dragstart="dragStart($event, customer.id, 'lead')"
                            @dragend="dragEnd($event)"
                            class="kanban-card status-lead"
                        >
                            {{-- Card Title --}}
                            <div class="kanban-card-title" x-text="customer.name"></div>
                            
                            {{-- Card Meta --}}
                            <div class="space-y-1">
                                <div class="kanban-card-meta" x-show="customer.email">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.email" class="truncate"></span>
                                </div>
                                <div class="kanban-card-meta" x-show="customer.phone">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span x-text="customer.phone"></span>
                                </div>
                            </div>
                            
                            {{-- Tags --}}
                            <div class="mt-2" x-show="customer.tags.length > 0">
                                <template x-for="tag in customer.tags.slice(0, 2)" :key="tag">
                                    <span class="kanban-tag" style="background: #fef3c7; color: #92400e;" x-text="tag"></span>
                                </template>
                                <span x-show="customer.tags.length > 2" class="kanban-tag" style="background: #e5e7eb; color: #6b7280;" x-text="'+' + (customer.tags.length - 2)"></span>
                            </div>
                            
                            {{-- Footer --}}
                            <div class="kanban-footer">
                                <div class="flex items-center gap-1 text-xs text-gray-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.follow_ups_count"></span>
                                </div>
                                <div class="flex gap-1">
                                    <button @click="viewCustomer(customer.id)" class="kanban-action-btn" title="View">
                                        👁️
                                    </button>
                                    <button @click="callCustomer(customer.phone, customer.country_code)" class="kanban-action-btn" title="WhatsApp" x-show="customer.phone">
                                        📱
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Next Follow-up Badge --}}
                            <div x-show="customer.next_follow_up" class="mt-2 text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="customer.next_follow_up"></span>
                            </div>
                        </div>
                    </template>
                    
                    {{-- Empty State --}}
                    <div x-show="customers.lead.length === 0" class="text-center text-gray-400 py-12">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-sm">No leads yet</p>
                    </div>
                    
                    {{-- Add Card Button --}}
                    <a href="/admin/customers/create" class="add-card-btn block hover:bg-gray-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Lead
                        </span>
                    </a>
                </div>
            </div>

            {{-- PROSPECT Column --}}
            <div class="kanban-column">
                <div class="kanban-column-header header-prospect">
                    <div class="flex items-center justify-between">
                        <span>🎯 Prospects</span>
                        <span class="bg-blue-200 px-2 py-0.5 rounded-full text-xs font-bold" x-text="customers.prospect.length"></span>
                    </div>
                </div>
                
                <div 
                    @dragover="dragOver($event)"
                    @dragenter="dragEnter($event)"
                    @dragleave="dragLeave($event)"
                    @drop="drop($event, 'prospect')"
                    class="p-3 kanban-drop-zone"
                >
                    <template x-for="customer in customers.prospect" :key="customer.id">
                        <div 
                            draggable="true"
                            @dragstart="dragStart($event, customer.id, 'prospect')"
                            @dragend="dragEnd($event)"
                            class="kanban-card status-prospect"
                        >
                            <div class="kanban-card-title" x-text="customer.name"></div>
                            
                            <div class="space-y-1">
                                <div class="kanban-card-meta" x-show="customer.email">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.email" class="truncate"></span>
                                </div>
                                <div class="kanban-card-meta" x-show="customer.phone">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span x-text="customer.phone"></span>
                                </div>
                            </div>
                            
                            <div class="mt-2" x-show="customer.tags.length > 0">
                                <template x-for="tag in customer.tags.slice(0, 2)" :key="tag">
                                    <span class="kanban-tag" style="background: #dbeafe; color: #1e40af;" x-text="tag"></span>
                                </template>
                                <span x-show="customer.tags.length > 2" class="kanban-tag" style="background: #e5e7eb; color: #6b7280;" x-text="'+' + (customer.tags.length - 2)"></span>
                            </div>
                            
                            <div class="kanban-footer">
                                <div class="flex items-center gap-1 text-xs text-gray-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.follow_ups_count"></span>
                                </div>
                                <div class="flex gap-1">
                                    <button @click="viewCustomer(customer.id)" class="kanban-action-btn" title="View">
                                        👁️
                                    </button>
                                    <button @click="callCustomer(customer.phone, customer.country_code)" class="kanban-action-btn" title="WhatsApp" x-show="customer.phone">
                                        📱
                                    </button>
                                </div>
                            </div>
                            
                            <div x-show="customer.next_follow_up" class="mt-2 text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="customer.next_follow_up"></span>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="customers.prospect.length === 0" class="text-center text-gray-400 py-12">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-sm">No prospects yet</p>
                    </div>
                    
                    <a href="/admin/customers/create" class="add-card-btn block hover:bg-gray-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Prospect
                        </span>
                    </a>
                </div>
            </div>

            {{-- CUSTOMER Column --}}
            <div class="kanban-column">
                <div class="kanban-column-header header-customer">
                    <div class="flex items-center justify-between">
                        <span>✅ Customers</span>
                        <span class="bg-green-200 px-2 py-0.5 rounded-full text-xs font-bold" x-text="customers.customer.length"></span>
                    </div>
                </div>
                
                <div 
                    @dragover="dragOver($event)"
                    @dragenter="dragEnter($event)"
                    @dragleave="dragLeave($event)"
                    @drop="drop($event, 'customer')"
                    class="p-3 kanban-drop-zone"
                >
                    <template x-for="customer in customers.customer" :key="customer.id">
                        <div 
                            draggable="true"
                            @dragstart="dragStart($event, customer.id, 'customer')"
                            @dragend="dragEnd($event)"
                            class="kanban-card status-customer"
                        >
                            <div class="kanban-card-title" x-text="customer.name"></div>
                            
                            <div class="space-y-1">
                                <div class="kanban-card-meta" x-show="customer.email">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.email" class="truncate"></span>
                                </div>
                                <div class="kanban-card-meta" x-show="customer.phone">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span x-text="customer.phone"></span>
                                </div>
                            </div>
                            
                            <div class="mt-2" x-show="customer.tags.length > 0">
                                <template x-for="tag in customer.tags.slice(0, 2)" :key="tag">
                                    <span class="kanban-tag" style="background: #d1fae5; color: #065f46;" x-text="tag"></span>
                                </template>
                                <span x-show="customer.tags.length > 2" class="kanban-tag" style="background: #e5e7eb; color: #6b7280;" x-text="'+' + (customer.tags.length - 2)"></span>
                            </div>
                            
                            <div class="kanban-footer">
                                <div class="flex items-center gap-1 text-xs text-gray-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.follow_ups_count"></span>
                                </div>
                                <div class="flex gap-1">
                                    <button @click="viewCustomer(customer.id)" class="kanban-action-btn" title="View">
                                        👁️
                                    </button>
                                    <button @click="callCustomer(customer.phone, customer.country_code)" class="kanban-action-btn" title="WhatsApp" x-show="customer.phone">
                                        📱
                                    </button>
                                </div>
                            </div>
                            
                            <div x-show="customer.next_follow_up" class="mt-2 text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="customer.next_follow_up"></span>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="customers.customer.length === 0" class="text-center text-gray-400 py-12">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">No customers yet</p>
                    </div>
                    
                    <a href="/admin/customers/create" class="add-card-btn block hover:bg-gray-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Customer
                        </span>
                    </a>
                </div>
            </div>

            {{-- INACTIVE Column --}}
            <div class="kanban-column">
                <div class="kanban-column-header header-inactive">
                    <div class="flex items-center justify-between">
                        <span>💤 Inactive</span>
                        <span class="bg-gray-300 px-2 py-0.5 rounded-full text-xs font-bold" x-text="customers.inactive.length"></span>
                    </div>
                </div>
                
                <div 
                    @dragover="dragOver($event)"
                    @dragenter="dragEnter($event)"
                    @dragleave="dragLeave($event)"
                    @drop="drop($event, 'inactive')"
                    class="p-3 kanban-drop-zone"
                >
                    <template x-for="customer in customers.inactive" :key="customer.id">
                        <div 
                            draggable="true"
                            @dragstart="dragStart($event, customer.id, 'inactive')"
                            @dragend="dragEnd($event)"
                            class="kanban-card status-inactive"
                        >
                            <div class="kanban-card-title" x-text="customer.name"></div>
                            
                            <div class="space-y-1">
                                <div class="kanban-card-meta" x-show="customer.email">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.email" class="truncate"></span>
                                </div>
                                <div class="kanban-card-meta" x-show="customer.phone">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span x-text="customer.phone"></span>
                                </div>
                            </div>
                            
                            <div class="mt-2" x-show="customer.tags.length > 0">
                                <template x-for="tag in customer.tags.slice(0, 2)" :key="tag">
                                    <span class="kanban-tag" style="background: #f3f4f6; color: #374151;" x-text="tag"></span>
                                </template>
                                <span x-show="customer.tags.length > 2" class="kanban-tag" style="background: #e5e7eb; color: #6b7280;" x-text="'+' + (customer.tags.length - 2)"></span>
                            </div>
                            
                            <div class="kanban-footer">
                                <div class="flex items-center gap-1 text-xs text-gray-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="customer.follow_ups_count"></span>
                                </div>
                                <div class="flex gap-1">
                                    <button @click="viewCustomer(customer.id)" class="kanban-action-btn" title="View">
                                        👁️
                                    </button>
                                    <button @click="callCustomer(customer.phone, customer.country_code)" class="kanban-action-btn" title="WhatsApp" x-show="customer.phone">
                                        📱
                                    </button>
                                </div>
                            </div>
                            
                            <div x-show="customer.next_follow_up" class="mt-2 text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="customer.next_follow_up"></span>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="customers.inactive.length === 0" class="text-center text-gray-400 py-12">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-sm">No inactive customers</p>
                    </div>
                    
                    <a href="/admin/customers/create" class="add-card-btn block hover:bg-gray-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Customer
                        </span>
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</x-filament-panels::page>