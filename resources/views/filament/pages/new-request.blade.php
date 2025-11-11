<x-filament-panels::page>
    <form wire:submit.prevent="submit" class="space-y-4">
        {{ $this->form }}

        <div class="flex items-center gap-2">
            <x-filament::button type="submit" icon="heroicon-o-paper-airplane" color="success">
                Submit Request
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
