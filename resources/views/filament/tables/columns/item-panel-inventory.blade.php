@php
    /** @var \App\Models\Trinity\World\ItemTemplate $record */
    $record=$getState();
    $qualityColor = $record->qualityColor();
@endphp

<div {{ $getExtraAttributeBag() }} class="p-2">

    <div class="flex items-center gap-3">
        {{-- Icon slot --}}
        <div class="w-10 h-10 rounded-md bg-gray-800/60 border border-white/10 flex items-center justify-center overflow-hidden">
            <span class="text-[10px] text-gray-400">icon</span>
        </div>

        <div class="min-w-0">
            {{-- Name + quality badge --}}
            <div class="flex items-center gap-2">
                <span
                                @class([
                    'truncate font-semibold',
                    'text-gray-400' => $qualityColor === 'gray',
                    'text-emerald-400' => $qualityColor === 'success',
                    'text-sky-400' => $qualityColor === 'info',
                    'text-purple-400' => $qualityColor === 'purple',
                    'text-amber-400' => $qualityColor === 'warning',
                    'text-rose-400' => $qualityColor === 'danger',
                    'text-indigo-400' => $qualityColor === 'primary',
                ])
                >{{ $record->name }}</span>
                <span
                @class([
                    'px-2 py-0.5 rounded text-[11px] border',
                    'bg-gray-800/70 text-gray-200 border-white/10' => $qualityColor === 'gray',
                    'bg-emerald-900/40 text-emerald-200 border-emerald-400/20' => $qualityColor === 'success',
                    'bg-sky-900/40 text-sky-200 border-sky-400/20' => $qualityColor === 'info',
                    'bg-purple-900/40 text-purple-200 border-purple-400/20' => $qualityColor === 'purple',
                    'bg-amber-900/40 text-amber-200 border-amber-400/20' => $qualityColor === 'warning',
                    'bg-rose-900/40 text-rose-200 border-rose-400/20' => $qualityColor === 'danger',
                    'bg-indigo-900/40 text-indigo-200 border-indigo-400/20' => $qualityColor === 'primary',
                ])
            >
                {{ $record->qualityLabel() }}
            </span>
            </div>

            {{-- Meta row: iLvl, Req, Class/Subclass, Inv type --}}
            <div class="mt-0.5 text-xs text-gray-400 flex flex-wrap gap-x-3">
                {{--                <span>iLvl {{ (int) $record->ItemLevel }}</span>--}}
                @if ((int) $record->RequiredLevel > 0)
                    <span>Req {{ (int) $record->RequiredLevel }}</span>
                @endif
                <span>{{ $record->classLabel() }}@if($record->subclassLabel()) · {{ $record->subclassLabel() }}@endif</span>
                <span>{{ $record->inventoryTypeLabel() }}</span>
            </div>
        </div>
    </div>

</div>
