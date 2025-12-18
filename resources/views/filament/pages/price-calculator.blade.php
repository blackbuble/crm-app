<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="calculate">
            {{ $this->form }}
        </form>

        @if($this->activeConfig && !empty($this->calculation))
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-2 border-primary-500">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Price Estimation
                </h2>

                <!-- Breakdown -->
                <div class="space-y-4 mb-6">
                    @if(!empty($this->calculation['breakdown']['packages']))
                        <div>
                            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">📦 Selected Packages:</h3>
                            <ul class="space-y-1 ml-4">
                                @foreach($this->calculation['breakdown']['packages'] as $package)
                                    <li class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ $package['name'] }}</span>
                                        <span class="font-medium">{{ format_currency($package['price']) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($this->calculation['breakdown']['addons']))
                        <div>
                            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">➕ Selected Add-ons:</h3>
                            <ul class="space-y-1 ml-4">
                                @foreach($this->calculation['breakdown']['addons'] as $addon)
                                    <li class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ $addon['name'] }}</span>
                                        <span class="font-medium">{{ format_currency($addon['price']) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Summary -->
                <div class="border-t-2 border-gray-200 dark:border-gray-700 pt-4 space-y-3">
                    <div class="flex justify-between text-lg">
                        <span class="text-gray-700 dark:text-gray-300">Subtotal:</span>
                        <span class="font-semibold">{{ format_currency($this->calculation['subtotal']) }}</span>
                    </div>

                    @if($this->calculation['auto_discount'] > 0)
                        <div class="flex justify-between text-green-600 dark:text-green-400">
                            <span>🎉 Auto Discount:</span>
                            <span class="font-semibold">- {{ format_currency($this->calculation['auto_discount']) }}</span>
                        </div>
                    @endif

                    @if($this->calculation['custom_discount'] > 0)
                        <div class="flex justify-between text-blue-600 dark:text-blue-400">
                            <span>💰 Additional Discount:</span>
                            <span class="font-semibold">- {{ format_currency($this->calculation['custom_discount']) }}</span>
                        </div>
                    @endif

                    @if($this->calculation['total_discount'] > 0)
                        <div class="flex justify-between text-lg font-semibold text-red-600 dark:text-red-400">
                            <span>Total Discount:</span>
                            <span>- {{ format_currency($this->calculation['total_discount']) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-2xl font-bold text-primary-600 dark:text-primary-400 pt-3 border-t-2 border-primary-200 dark:border-primary-800">
                        <span>TOTAL:</span>
                        <span>{{ format_currency($this->calculation['total']) }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex gap-3">
                    <button type="button" 
                            onclick="window.print()" 
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                    <button type="button" 
                            wire:click="$refresh" 
                            class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Recalculate
                    </button>
                </div>
            </div>
        @endif

        @if(!$this->activeConfig)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-200">
                            No active pricing configuration found. Please create one in 
                            <a href="{{ route('filament.admin.resources.pricing-configs.index') }}" class="font-medium underline">
                                Marketing Operations > Pricing Configs
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
