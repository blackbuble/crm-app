<x-filament-panels::page>
    <form wire:submit="transfer">
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" color="danger" icon="heroicon-o-arrow-path">
                Process Transfer & Offboarding
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
