<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        {{-- Single Link Result --}}
        @if($generatedUrl && empty($data['bulk_urls']))
            <x-filament::section>
                <x-slot name="heading">
                    Generated URL
                </x-slot>

                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg break-all dark:bg-gray-800 dark:border-gray-700">
                        <code class="text-sm text-primary-600 dark:text-primary-400">
                            {{ $generatedUrl }}
                        </code>
                    </div>

                    <div class="flex gap-3">
                        <x-filament::button
                            icon="heroicon-o-clipboard-document"
                            x-data="{
                                url: @js($generatedUrl),
                                copy() {
                                    window.navigator.clipboard.writeText(this.url);
                                    $tooltip('Copied to clipboard!', { timeout: 1500 });
                                    new FilamentNotification()
                                        .title('Copied to clipboard')
                                        .success()
                                        .send();
                                }
                            }"
                            x-on:click="copy"
                        >
                            Copy URL
                        </x-filament::button>

                        <x-filament::button
                            color="gray"
                            tag="a"
                            href="{{ $generatedUrl }}"
                            target="_blank"
                            icon="heroicon-o-arrow-top-right-on-square"
                        >
                            Test URL
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @endif

        {{-- Bulk Results --}}
        @if(!empty($bulkGeneratedUrls))
            <x-filament::section>
                <x-slot name="heading">
                    Bulk Generated URLs ({{ count($bulkGeneratedUrls) }})
                </x-slot>

                <div class="space-y-4">
                    <div class="max-h-96 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Original URL</th>
                                    <th scope="col" class="px-6 py-3">Generated UTM Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bulkGeneratedUrls as $url)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4 truncate max-w-xs">
                                            {{ explode('?', $url)[0] }}
                                        </td>
                                        <td class="px-6 py-4 break-all">
                                            <code class="text-primary-600 dark:text-primary-400">{{ $url }}</code>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @php
                        $urlsAsString = implode("\n", $bulkGeneratedUrls);
                    @endphp

                    <div class="flex gap-3">
                        <x-filament::button
                            icon="heroicon-o-clipboard-document-list"
                            color="success"
                            x-data="{
                                urls: @js($urlsAsString),
                                copy() {
                                    window.navigator.clipboard.writeText(this.urls);
                                    $tooltip('All URLs copied!', { timeout: 1500 });
                                    new FilamentNotification()
                                        .title('All URLs copied to clipboard')
                                        .success()
                                        .send();
                                }
                            }"
                            x-on:click="copy"
                        >
                            Copy All URLs
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
