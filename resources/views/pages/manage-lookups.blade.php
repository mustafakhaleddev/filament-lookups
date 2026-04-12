<x-filament-panels::page>
    @if($this->selectedType)
        {{ $this->table }}
    @else
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('filament-lookups::lookups.no_types') }}
            </p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
