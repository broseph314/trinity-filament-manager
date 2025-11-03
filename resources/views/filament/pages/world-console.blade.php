<x-filament-panels::page>
    <div class="space-y-4">
        {{ $this->form }}

        <x-filament::section>
            <x-slot name="heading">Output</x-slot>

            @php($pairs = $this->parsedPairs())

            @if ($pairs)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
                    @foreach ($pairs as $row)
                        @if ($row['k'] === null)
                            <div class="col-span-full border-t border-white/10 my-2"></div>
                            <div class="col-span-full text-sm text-gray-300">{{ $row['v'] }}</div>
                        @else
                            <div class="text-sm text-gray-400 font-medium">{{ $row['k'] }}</div>
                            <div class="text-sm text-gray-100">{{ $row['v'] }}</div>
                        @endif
                    @endforeach
                </div>
            @else
                <pre class="text-sm overflow-auto p-4 bg-gray-900 text-gray-100 rounded-lg min-h-48 max-h-[65vh] whitespace-pre-wrap">
{{ $output }}
                </pre>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
