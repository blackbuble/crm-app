<x-filament-panels::page>
    <div x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">
        
        {{-- Tabs Navigation --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                @foreach(\App\Models\MarketingMaterial::getTypes() as $key => $label)
                    <button 
                        @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}' 
                            ? 'border-primary-500 text-primary-600 dark:text-primary-400' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Content Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($this->materials as $material)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                    
                    {{-- Thumbnail / Icon --}}
                    <div class="h-40 bg-gray-100 dark:bg-gray-900 flex items-center justify-center relative group">
                        @if($material->thumbnail_path)
                            <img src="{{ Storage::url($material->thumbnail_path) }}" class="w-full h-full object-cover">
                        @else
                            <x-heroicon-o-document-text class="w-16 h-16 text-gray-400" />
                        @endif

                        {{-- Hover Overlay for Files --}}
                         @if($material->file_path)
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="bg-white text-gray-900 px-4 py-2 rounded-lg font-bold shadow-lg hover:bg-gray-50 flex items-center gap-2">
                                    <x-heroicon-s-arrow-down-tray class="w-5 h-5"/>
                                    Download
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Body --}}
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $material->title }}</h3>
                        
                        @if($material->description)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 line-clamp-2">
                                {{ $material->description }}
                            </p>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2 mt-auto">
                            @if($material->type === 'script')
                                <button 
                                    x-data
                                    @click="window.navigator.clipboard.writeText(`{{ addslashes($material->content) }}`); $tooltip('Copied!', { timeout: 1500 });"
                                    class="w-full py-2 px-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2"
                                >
                                    <x-heroicon-o-clipboard class="w-4 h-4"/>
                                    Copy Script
                                </button>
                            @elseif($material->type === 'calculator')
                                <a href="{{ $material->content }}" target="_blank" class="w-full py-2 px-4 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors flex items-center justify-center gap-2">
                                    <x-heroicon-o-calculator class="w-4 h-4"/>
                                    Open Calculator
                                </a>
                            @elseif($material->file_path)
                                <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="w-full py-2 px-4 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2">
                                    <x-heroicon-o-eye class="w-4 h-4"/>
                                    Preview
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                        <x-heroicon-o-folder-open class="w-8 h-8 text-gray-400"/>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No assets found</h3>
                    <p class="text-gray-500 dark:text-gray-400">Select a different tab or ask Admin to upload materials.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
