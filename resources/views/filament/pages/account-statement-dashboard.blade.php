<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->form }}
    </div>

    <div class="flex justify-center mb-6">
        <x-filament::button wire:click="search" icon="heroicon-m-magnifying-glass" size="lg">
            عرض
        </x-filament::button>
    </div>

    @if($isReportGenerated)
        <div>
            {{ $this->table }}
        </div>
    @endif
</x-filament-panels::page>
