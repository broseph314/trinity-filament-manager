<x-filament-panels::page>
    <form wire:submit.prevent="runTeleports" class="space-y-4">
        {{ $this->form }}

        <div class="flex items-center gap-2">
            <x-filament::button type="submit" color="success" icon="heroicon-o-paper-airplane">
                Run Teleports
            </x-filament::button>
        </div>
    </form>

    @if (!empty($log))
        <div class="mt-6 rounded-xl bg-gray-950 text-gray-100 p-4 text-sm space-y-1">
            @foreach ($log as $entry)
                @php
                    $c = match($entry['lv']) {
                        'ok' => 'text-green-400',
                        'dry-run' => 'text-yellow-300',
                        'error' => 'text-red-400',
                        default => 'text-gray-200',
                    };
                @endphp
                <div class="font-mono">
                    <span class="text-gray-500">[{{ $entry['t'] }}]</span>
                    <span class="{{ $c }}">•</span>
                    <span class="ml-2">{{ $entry['msg'] }}</span>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
