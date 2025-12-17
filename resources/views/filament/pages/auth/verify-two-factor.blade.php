<x-filament-panels::page.simple>
    <p class="text-center text-sm text-gray-500 mb-6">
        Please enter the code from your authenticator app to continue.
    </p>

    {{ $this->form }}

    <div class="space-y-4 mt-6">
    <x-filament::button wire:click="authenticate" class="w-full">
        Verify
    </x-filament::button>
    
    <div class="text-center">
        <form action="{{ filament()->getLogoutUrl() }}" method="post">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-900 focus:outline-none underline">
                Sign out
            </button>
        </form>
    </div>
    </div>
</x-filament-panels::page.simple>
