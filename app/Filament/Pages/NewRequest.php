<?php

namespace App\Filament\Pages;

use App\Models\Request;
use App\Models\RequestLine;
use App\Models\Trinity\Characters\Character;
use App\Models\Trinity\World\ItemTemplate;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NewRequest extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.new-request';

    protected static string|null|\BackedEnum $navigationIcon  = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationLabel = 'New Request';
    protected static ?string $title           = 'New Request';
    protected static string|null|\UnitEnum $navigationGroup = 'Player';
    protected static ?int    $navigationSort  = 10;

    // ------- form state -------
    public ?int   $character_guid = null;
    public string $character_name = '';   // auto-filled
    public string $category       = 'items'; // default
    public array  $items          = [];   // [{item_entry, qty}]
    public ?int   $gold           = 0;
    public ?int   $silver         = 0;
    public ?int   $copper         = 0;
    public ?int   $level          = null;
    public ?string $pack          = null;
    public ?string $perm          = null;
    public ?string $note          = null;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check(); // player-only, visible to any logged-in user
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Request Details')->schema([
                Grid::make(12)->schema([
                    // CHARACTER
                    Select::make('character_guid')
                        ->label('Character')
                        ->searchable()
                        ->searchDebounce(200)
                        ->native(false)
                        ->required()
                        ->reactive()
                        ->getSearchResultsUsing(function (string $search) {
                            if ($search === '') return [];
                            // If you link Trinity accounts to app users, filter here by owner!
                            return Character::query()
                                ->selectRaw('guid, name')
                                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                                ->orderBy('name')
                                ->limit(30)
                                ->get()
                                ->mapWithKeys(fn ($c) => [$c->guid => "{$c->name} (GUID {$c->guid})"])
                                ->all();
                        })
                        ->getOptionLabelUsing(function ($value): ?string {
                            $c = Character::find($value);
                            return $c ? "{$c->name} (GUID {$c->guid})" : null;
                        })
                        ->afterStateUpdated(function ($state) {
                            $c = $state ? Character::find($state) : null;
                            $this->character_name = $c?->name ?? '';
                        })
                        ->columnSpan(6),

                    // CATEGORY
                    Select::make('category')
                        ->label('Category')
                        ->options([
                            'items' => 'Items',
                            'money' => 'Money',
//                            'level' => 'Level',
//                            'pack'  => 'Pack',
//                            'perm'  => 'Permission',
                        ])
                        ->required()
                        ->reactive()
                        ->columnSpan(6),
                ]),
            ]),

            // ITEMS
            Section::make('Items')
                ->visible(fn (Get $get) => $get('category') === 'items')
                ->schema([
                    Repeater::make('items')
                        ->label('Requested Items')
                        ->minItems(1)->columns(8)
                        ->schema([
                            Select::make('item_entry')
                                ->label('Item')
                                ->searchable()
                                ->native(false)
                                ->required()
                                ->columnSpan(6)
                                ->getSearchResultsUsing(function (string $search) {
                                    if ($search === '') return [];
                                    return ItemTemplate::query()
                                        ->selectRaw('entry, name, quality')
                                        ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                                        ->orderBy('quality', 'desc')
                                        ->orderBy('name')
                                        ->limit(50)->get()
                                        ->mapWithKeys(fn ($i) => [$i->entry => "{$i->name} [#{$i->entry}]"])
                                        ->all();
                                })
                                ->getOptionLabelUsing(function ($value): ?string {
                                    $i = ItemTemplate::find($value);
                                    return $i ? "{$i->name} [#{$i->entry}]" : null;
                                }),
                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()->minValue(1)->maxValue(100)->default(1)->required()
                                ->columnSpan(2),
                        ]),
                ]),

            // MONEY
            Section::make('Money')
                ->visible(fn (Get $get) => $get('category') === 'money')
                ->schema([
                    Grid::make(6)->schema([
                        TextInput::make('gold')->numeric()->minValue(0)->maxValue(99999)->default(0)->columnSpan(2),
                        TextInput::make('silver')->numeric()->minValue(0)->maxValue(99)->default(0)->columnSpan(2),
                        TextInput::make('copper')->numeric()->minValue(0)->maxValue(99)->default(0)->columnSpan(2),
                    ]),
                ]),

            // LEVEL
            Section::make('Level')
                ->visible(fn (Get $get) => $get('category') === 'level')
                ->schema([
                    TextInput::make('level')->numeric()->minValue(1)->maxValue(80)->required()
                        ->helperText('Target level (1–80).'),
                ]),

            // PACK
            Section::make('Pack')
                ->visible(fn (Get $get) => $get('category') === 'pack')
                ->schema([
                    Select::make('pack')
                        ->label('Choose a pack')
                        ->options(collect(config('packs', []))->keys()->mapWithKeys(fn ($k) => [$k => $k])->all())
                        ->searchable()
                        ->required(),
                ]),

            // PERMISSION
            Section::make('Permission')
                ->visible(fn (Get $get) => $get('category') === 'perm')
                ->schema([
                    Select::make('perm')
                        ->label('Permission')
                        // Example permissions; replace with your own source
                        ->options(collect(config('permissions.keys', [
                            'mount_310' => '310% Mount Speed',
                            'bank_slots' => 'Add Bank Slots',
                        ])))
                        ->searchable()
                        ->required(),
                ]),

            Section::make('Notes')
                ->schema([
                    Textarea::make('note')->label('Why do you need this?')->rows(3)->maxLength(1000),
                ]),
        ];
    }

    public function submit(): void
    {
        $this->validatePayload();

        DB::transaction(function () {
            // Resolve character for denormalized name
            $char = Character::find($this->character_guid);
            if (!$char) {
                throw ValidationException::withMessages(['character_guid' => 'Character not found.']);
            }

            $request = Request::create([
                'user_id'        => auth()->id(),
                'character_guid' => $char->guid,
                'character_name' => $char->name,
                'category'       => $this->category,
                'status'         => 'pending',
                'meta'           => [
                    'note' => $this->note,
                    // add realmId, etc. here if you track it
                ],
            ]);

            // Build lines from the chosen category
            foreach ($this->buildLines() as $line) {
                RequestLine::create([
                    'request_id' => $request->id,
                    'action'     => $line['action'],
                    'params'     => $line['params'],
                    'status'     => 'pending',
                ]);
            }
        });

        Notification::make()
            ->title('Request submitted')
            ->body('Your request was created and is now pending review.')
            ->success()
            ->send();

        // Reset minimal state (keep character & category so UX is friendly)
        $this->items = [];
        $this->gold = $this->silver = $this->copper = 0;
        $this->level = null;
        $this->pack = null;
        $this->perm = null;
        $this->note = null;
    }

    private function validatePayload(): void
    {
        // Basic server-side guardrails per category
        if (!$this->character_guid) {
            throw ValidationException::withMessages(['character_guid' => 'Pick a character.']);
        }

        (match ($this->category) {
            'items' => function () {
                if (!is_array($this->items) || count($this->items) === 0) {
                    throw ValidationException::withMessages(['items' => 'Add at least one item.']);
                }
                foreach ($this->items as $i => $line) {
                    if (!isset($line['item_entry'])) {
                        throw ValidationException::withMessages(["items.$i.item_entry" => 'Item is required.']);
                    }
                    $qty = (int) ($line['qty'] ?? 0);
                    if ($qty < 1 || $qty > 100) {
                        throw ValidationException::withMessages(["items.$i.qty" => 'Quantity must be 1–100.']);
                    }
                }
            },
            'money' => function () {
                $copper = $this->asCopper();
                if ($copper <= 0) {
                    throw ValidationException::withMessages(['money' => 'Enter a positive amount.']);
                }
                if ($copper > 999999 * 10000) { // cap example: 500g
                    throw ValidationException::withMessages(['money' => 'Max 500g per request.']);
                }
            },
            'level' => function () {
                $lvl = (int) $this->level;
                if ($lvl < 1 || $lvl > 80) {
                    throw ValidationException::withMessages(['level' => 'Level must be 1–80.']);
                }
            },
            'pack' => function () {
                $packs = config('packs', []);
                if (!$this->pack || !array_key_exists($this->pack, $packs)) {
                    throw ValidationException::withMessages(['pack' => 'Choose a valid pack.']);
                }
            },
            'perm' => function () {
                if (!$this->perm) {
                    throw ValidationException::withMessages(['perm' => 'Choose a permission.']);
                }
            },
            default => function () {
                throw ValidationException::withMessages(['category' => 'Unknown category.']);
            },
        })();
    }

    /** Build array of ['action' => string, 'params' => array] lines */
    private function buildLines(): array
    {
        return match ($this->category) {
            'items' => collect($this->items)->map(function ($line) {
                // Store item name now for admin readability (optional)
                $tpl = ItemTemplate::find($line['item_entry']);
                return [
                    'action' => 'send_item',
                    'params' => [
                        'entry' => (int) $line['item_entry'],
                        'qty'   => (int) ($line['qty'] ?? 1),
                        'label' => $tpl?->name, // optional
                    ],
                ];
            })->all(),

            'money' => [[
                'action' => 'send_money',
                'params' => ['copper' => $this->asCopper()],
            ]],

            'level' => [[
                'action' => 'set_level',
                'params' => ['level' => (int) $this->level],
            ]],

            'pack' => $this->expandPackLines($this->pack),

            'perm' => [[
                'action' => 'grant_perm',
                'params' => ['perm' => (string) $this->perm],
            ]],
        };
    }

    private function asCopper(): int
    {
        $g = max(0, (int) ($this->gold   ?? 0));
        $s = max(0, (int) ($this->silver ?? 0));
        $c = max(0, (int) ($this->copper ?? 0));
        return $g * 10000 + $s * 100 + $c;
    }

    private function expandPackLines(?string $key): array
    {
        $packs = config('packs', []);
        if (!$key || !isset($packs[$key])) return [];
        // Packs are arrays of ['action' => ..., 'params' => ...]
        return array_values(array_map(function ($row) {
            return [
                'action' => (string) $row['action'],
                'params' => (array) $row['params'],
            ];
        }, $packs[$key]));
    }

}
