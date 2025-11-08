<x-filament-panels::page>
    {{-- Header infolist --}}
    <div class="space-y-6">
        {{ $this->accountInfo }}

        {{-- Rich characters table (search/sort/pagination) --}}
        <x-filament::section>
            <x-slot name="heading">Characters</x-slot>
            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
