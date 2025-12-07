{{-- resources/views/filament/actions/contact-customer.blade.php --}}
<div class="space-y-4 p-4">
    <div class="text-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            {{ $record->customer->name }}
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Choose how you want to contact this customer
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @if($record->customer->phone)
            {{-- WhatsApp Button --}}
            <a 
                href="{{ $whatsappUrl = 'https://wa.me/' . preg_replace('/[^0-9]/', '', str_replace('0', '62', $record->customer->phone)) . '?text=' . urlencode('Hi ' . $record->customer->name . ', this is a follow-up regarding our previous discussion.') }}"
                target="_blank"
                class="flex flex-col items-center justify-center p-6 bg-green-50 dark:bg-green-900/20 border-2 border-green-500 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors cursor-pointer"
            >
                <svg class="w-8 h-8 text-green-600 mb-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                <span class="text-sm font-semibold text-green-700 dark:text-green-400">WhatsApp</span>
                <span class="text-xs text-green-600 dark:text-green-500 mt-1">{{ $record->customer->phone }}</span>
            </a>

            {{-- Phone Call Button --}}
            <a 
                href="tel:{{ $record->customer->phone }}"
                class="flex flex-col items-center justify-center p-6 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-500 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors cursor-pointer"
            >
                <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                <span class="text-sm font-semibold text-blue-700 dark:text-blue-400">Phone Call</span>
                <span class="text-xs text-blue-600 dark:text-blue-500 mt-1">{{ $record->customer->phone }}</span>
            </a>
        @endif

        @if($record->customer->email)
            {{-- Email Button --}}
            <a 
                href="mailto:{{ $record->customer->email }}?subject={{ urlencode('Follow-up: ' . $record->customer->name) }}&body={{ urlencode('Dear ' . $record->customer->name . ',

I hope this email finds you well. I wanted to follow up on our previous discussion.

Best regards') }}"
                class="flex flex-col items-center justify-center p-6 bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-500 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors cursor-pointer"
            >
                <svg class="w-8 h-8 text-yellow-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm font-semibold text-yellow-700 dark:text-yellow-400">Email</span>
                <span class="text-xs text-yellow-600 dark:text-yellow-500 mt-1">{{ $record->customer->email }}</span>
            </a>
        @endif
    </div>

    @if(!$record->customer->phone && !$record->customer->email)
        <div class="text-center py-8">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">No contact information available for this customer.</p>
        </div>
    @endif
</div>