<x-filament-panels::page>
    <div x-data @lead-captured.window="$nextTick(() => { $refs.nameInput?.focus() })">
        <form wire:submit="create">
            {{ $this->form }}

            <div class="mt-6 flex justify-end">
                <x-filament::button type="submit" size="xl" class="w-full md:w-auto">
                    Save & Add Next Visitor (Enter)
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
