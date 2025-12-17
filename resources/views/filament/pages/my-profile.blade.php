<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        @if($this->otp_enabled)
        <div class="p-4 bg-gray-50 border rounded-lg">
            <h3 class="font-bold text-lg mb-2">Two-Factor Authentication is ENABLED</h3>
            <p class="text-sm text-gray-600">Your account is secured with OTP.</p>
        </div>
        @endif

        <div class="flex justify-end">
             <x-filament::button wire:click="submit">
                Save Changes
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
