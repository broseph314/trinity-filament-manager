<?php

namespace App\Filament\Pages;

use App\Models\Trinity\Characters\Character;
use App\Models\Trinity\World\GameTele;
use App\Services\Trinity\CommandService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class TeleportAdmin extends Page implements HasForms
{
    use \Filament\Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationLabel = 'Teleport';
    protected static ?string $title           = 'Teleport';
    protected static string|null|\UnitEnum $navigationGroup = 'Trinity Tools';
    protected static string|null|\BackedEnum $navigationIcon  = Heroicon::OutlinedMapPin;
    protected static ?int    $navigationSort  = 21;

    protected string $view = 'filament.pages.teleport-admin';

    // form state
    public array  $characterGuids   = [];   // multi
    public ?int   $teleId           = null; // GameTele id (or string if your PK is name)
    public string $manualTele       = '';   // fallback manual name
    public int    $delayMs          = 250;
    public bool   $dryRun           = false;

    // live log
    public array $log = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['gm','admin','moderator']) ?? false;
    }
    public static function shouldRegisterNavigation(): bool { return static::canAccess(); }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(1)->schema([
                TextInput::make('delayMs')
                    ->label('Delay (ms)')
                    ->numeric()
                    ->minValue(0)
                    ->default(250)
                    ->helperText('Pause between calls to avoid flooding.')
                    ->columnSpan(4)
                    ->visible(fn () => auth()->user()?->hasRole('admin')),

                Toggle::make('dryRun')
                    ->label('Dry run')
                    ->columnSpan(2)
                    ->visible(fn () => auth()->user()?->hasRole('admin')),

                // CHARACTERS — async search (by name)
                Select::make('characterGuids')
                    ->label('Characters')
                    ->multiple()
                    ->searchable()
                    ->searchDebounce(250)
                    ->native(false)
                    ->getSearchResultsUsing(function (string $search) {
                        if ($search === '') return [];
                        return Character::query()
                            ->selectRaw('guid, name')
                            ->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                            ->orderBy('name')
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->guid => "{$c->name} (GUID {$c->guid})"])
                            ->all();
                    })
                    ->getOptionLabelsUsing(function (array $values): array {
                        return Character::query()
                            ->whereIn('guid', $values)
                            ->pluck('name', 'guid')
                            ->map(fn ($name, $guid) => "{$name} (GUID {$guid})")
                            ->all();
                    })
                    ->columnSpan(4),

                // TELEPORT — async search from game_tele
                Select::make('teleId')
                    ->label('Teleport (lookup)')
                    ->searchable()
                    ->searchDebounce(250)
                    ->native(false)
                    ->reactive() // watch for changes
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state) {
                            // user chose a lookup — clear manual
                            $set('manualTele', '');
                        }
                    })
                    ->disabled(fn (Get $get) => filled($get('manualTele')))
                    ->getSearchResultsUsing(function (string $search) {
                        if ($search === '') return [];
                        return GameTele::query()
                            ->selectRaw('id, name, map, position_x, position_y, position_z')
                            ->where('name', 'like', "%{$search}%")
                            ->orderBy('name')
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(function ($t) {
                                $meta = "map {$t->map} @ {$t->position_x},{$t->position_y},{$t->position_z}";
                                return [$t->id => "{$t->name}"];
                            })
                            ->all();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $t = GameTele::find($value);
                        return $t ? "{$t->name} (map {$t->map})" : null;
                    })
                ->columnSpan(2),

                TextInput::make('manualTele')
                    ->label('Or manual teleport name')
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if (filled($state)) {
                            // user typed manual — clear lookup
                            $set('teleId', null);
                        }
                    })
                    ->disabled(fn (Get $get) => filled($get('teleId')))
                    ->placeholder('e.g. Stormwind')
                    ->columnSpan(2),
            ]),
        ];
    }

    public function runTeleports(): void
    {
        $this->delayMs = max(0, (int) $this->delayMs);

        $teleName = $this->resolveTeleportName();
        if (! $teleName)  return;
        if (empty($this->characterGuids)) return;

        $svc = app(CommandService::class);

        // Resolve character names in one query
        $chars = Character::query()->whereIn('guid',$this->characterGuids)->pluck('name','guid');

        foreach ($chars as $guid => $name) {
            $cmd = $this->buildCommand($name, $teleName);

            if ($this->dryRun) {
                $this->push('dry-run', $cmd);
            } else {
                try {
                    $raw = $svc->run($cmd);
                    $this->push('ok', $this->sanitize($raw) ?: "OK: {$cmd}");
                } catch (\Throwable $e) {
                    $this->push('error', "ERR {$name}: ".$e->getMessage());
                }
                if ($this->delayMs > 0) usleep($this->delayMs * 1000);
            }
        }
    }

    private function resolveTeleportName(): ?string
    {
        if ($this->teleId) {
            $t = GameTele::find($this->teleId);
            if ($t) return $t->name;   // Trinity expects the .tele NAME
        }
        $m = trim($this->manualTele);
        return $m !== '' ? $m : null;
    }

    private function buildCommand(string $char, string $location): string
    {
        // Defensive quoting for spaces/specials
        $c = trim($char, "\"'");
        $l = trim($location, "\"'");
        return sprintf('tele name %s %s', $c, $l);
    }

    private function push(string $level, string $msg): void
    {
        $this->log[] = ['t' => now()->format('H:i:s'), 'lv' => $level, 'msg' => $msg];
    }

    private function sanitize(?string $raw): string
    {
        if ($raw === null) return '';
        $s = preg_replace('/<\?xml[^>]*\?>/i', '', $raw);
        $s = preg_replace('/<\/?(?:SOAP-ENV|soap|s|env|Envelope|Body|Header|ns1|m)(?::[^>]*)?>/i', '', $s);
        if (preg_match('/<result[^>]*>(.*?)<\/result>/is', (string) $s, $m)) $s = $m[1];
        $s = strip_tags((string) $s);
        $s = html_entity_decode($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $s = str_replace("\xC2\xA0", ' ', $s);
        $s = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $s);
        $s = preg_replace("/\r\n?/", "\n", $s);
        $s = preg_replace("/[ \t]+/", ' ', $s);
        $s = preg_replace("/\n{3,}/", "\n\n", $s);
        return trim($s);
    }
}
