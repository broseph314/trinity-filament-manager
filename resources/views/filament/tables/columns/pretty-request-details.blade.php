@php
    /** @var \App\Models\RequestLine $record */
    use App\Models\Trinity\World\ItemTemplate;use Filament\Facades\Filament;
    $action = $record->action;
    $p = (array) $record->params;

    $pill = fn(string $text) => "<span class=\"px-2 py-0.5 rounded-full text-xs bg-gray-800/70 text-gray-100 border border-white/10\">{$text}</span>";
@endphp
<div {{ $getExtraAttributeBag() }}>
    {{ $getState() }}
    <div class="flex flex-wrap items-center gap-2">

        @if ($action === 'send_money')
            @php
                $c = (int) ($p['copper'] ?? 0);
                $g = intdiv($c, 10000);
                $s = intdiv($c % 10000, 100);
                $k = $c % 100;
            @endphp

            <span class="text-sm text-gray-300">Money:</span>

            @if($g)
                <div class="flex items-center gap-1">
                    <span>{{ $g }}</span>
                    <span class="inline-block w-3 h-3 rounded-full bg-yellow-500 border border-yellow-300"></span>
                </div>
            @endif

            @if($s)
                <div class="flex items-center gap-1">
                    <span>{{ $s }}</span>
                    <span class="inline-block w-3 h-3 rounded-full bg-gray-500 border border-gray-300"></span>
                </div>
            @endif

            @if($k || (!$g && !$s))
                <div class="flex items-center gap-1">
                    <span>{{ $k }}</span>
                    <span class="inline-block w-3 h-3 rounded-full bg-amber-900 border border-amber-800"></span>
                </div>
            @endif

        @elseif ($action === 'send_item')
            @php
                // dear reader pls dont judge
                //These are absolute war crimes (N+1, added libraries, etc. etc.)
                //but the intention is to only ever use these sparingly in a single page
                // and more importantly, it looks cool so sue me

                $entry = (int) ($p['entry'] ?? 0);
                $template = ItemTemplate::query()->find($entry);
                $qty   = (int) ($p['qty'] ?? 1);
                $label = $p['label'] ?? ($entry ? "Item #{$entry}" : 'Item');
                    $url = $template
                        ? Filament::getResourceUrl(ItemTemplate::class, 'view', ['record' => $template])
                        : '#';
            @endphp

            <a
                class="flex items-center gap-2 rounded-xl border border-white/10 bg-gray-900/60 px-2 py-2 m-2
           hover:bg-gray-800/70 hover:border-white/20 transition-all duration-150 ease-in-out"
                href="{{$url}}"
            >
                @if ($template)
                    @include('filament.tables.columns.item-template-card', [
                        'record' => $template,
                    ])
                @else
                    <div class="flex items-center">
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-100">{{ $label }}</span>
                            <span class="text-xs text-gray-400">Entry #{{ $entry }}</span>
                        </div>
                    </div>
                @endif
            </a>
            <span class="ml-2 px-2 py-0.5 rounded-md text-xs bg-gray-800/80 text-gray-200 border border-white/10">
                    × {{ $qty }}
            </span>
        @else
            {{-- Fallback: compact JSON --}}
            <code class="text-xs text-gray-300 bg-gray-900/50 px-2 py-1 rounded">
                {{ json_encode($p, JSON_UNESCAPED_UNICODE) }}
            </code>
        @endif
    </div>
</div>
