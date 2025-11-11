@php
    /** @var \App\Models\RequestLine $record */
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
                $entry = (int) ($p['entry'] ?? 0);
                $qty   = (int) ($p['qty'] ?? 1);
                $label = $p['label'] ?? ($entry ? "Item #{$entry}" : 'Item');
            @endphp

            <div class="flex items-center gap-2 rounded-xl border border-white/10 bg-gray-900/60 px-3 py-1.5">
                <div class="flex flex-col">
                    <span class="text-sm text-gray-100">{{ $label }}</span>
                    <span class="text-xs text-gray-400">Entry #{{ $entry }}</span>
                </div>
                <span class="ml-2 px-2 py-0.5 rounded-md text-xs bg-gray-800/80 text-gray-200 border border-white/10">
                × {{ $qty }}
            </span>
            </div>

        @else
            {{-- Fallback: compact JSON --}}
            <code class="text-xs text-gray-300 bg-gray-900/50 px-2 py-1 rounded">
                {{ json_encode($p, JSON_UNESCAPED_UNICODE) }}
            </code>
        @endif
    </div>
</div>
