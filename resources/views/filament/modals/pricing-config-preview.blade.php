<div class="space-y-6">
    <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-lg p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            {{ $config->name }}
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
            {{ $config->description }}
        </p>
    </div>

    <!-- Packages -->
    <div>
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Packages ({{ count($config->getPackages()) }})
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($config->getPackages() as $package)
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-start mb-2">
                        <h5 class="font-semibold text-gray-900 dark:text-white">{{ $package['name'] }}</h5>
                        <span class="text-primary-600 dark:text-primary-400 font-bold">
                            {{ format_currency($package['price']) }}
                        </span>
                    </div>
                    @if(isset($package['description']))
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $package['description'] }}</p>
                    @endif
                    @if(isset($package['features']))
                        <ul class="text-xs text-gray-500 dark:text-gray-500 space-y-1">
                            @foreach($package['features'] as $feature)
                                <li class="flex items-center gap-1">
                                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Add-ons -->
    @if(count($config->getAddons()) > 0)
        <div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add-ons ({{ count($config->getAddons()) }})
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach($config->getAddons() as $addon)
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-start">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $addon['name'] }}</span>
                            <span class="text-sm text-primary-600 dark:text-primary-400 font-semibold">
                                {{ format_currency($addon['price']) }}
                            </span>
                        </div>
                        @if(isset($addon['description']))
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $addon['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Discount Rules -->
    @if(count($config->getDiscountRules()) > 0)
        <div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Discount Rules ({{ count($config->getDiscountRules()) }})
            </h4>
            <div class="space-y-2">
                @foreach($config->getDiscountRules() as $rule)
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-green-900 dark:text-green-100">
                                {{ $rule['description'] ?? 'Discount Rule' }}
                            </span>
                            <span class="text-xs bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200 px-2 py-1 rounded">
                                @if($rule['discount_type'] === 'percentage')
                                    {{ $rule['discount_value'] }}% OFF
                                @else
                                    {{ format_currency($rule['discount_value']) }} OFF
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
