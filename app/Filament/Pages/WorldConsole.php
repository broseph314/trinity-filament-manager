<?php

namespace App\Filament\Pages;

use App\Services\Trinity\CommandService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;

class WorldConsole extends Page implements HasForms
{
    use \Filament\Forms\Concerns\InteractsWithForms;

    protected string $view = 'filament.pages.world-console';
    protected static ?string $navigationLabel = 'World Console';
    protected static ?string $title           = 'World Console';
    protected static string|null|\UnitEnum $navigationGroup = 'Trinity Tools';
    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    /** Current command + output */
    public ?string $command = null;
    public ?string $output  = null;

    /** Keep this tight and safe. Extend as you gain confidence. */
    private const WHITELIST = [
        'revive smella',
        'tele name bamuel ScarletMonastery',
        'tele name gothmog Stormwind',
        'tele name saurontwo Stormwind',
        'tele name tranquilos Stormwind',
        'tele name fannychmela Stormwind',
        'tele name smella Stormwind',
        'tele name jub Stormwind',
        'repairitems bamuel',
        'send money Saurontwo "Grats" "Have fun with dual spec" 10540000',
        'server info',
        'help ',
        'character level smella 20',
        'lookup tele scarlet',
        'tele name bamuel Stormwind',
        'tele name jub Stormwind',
        'tele name Fannychmela Stormwind',
        'tele name smella Stormwind',
        'tele name gothmog Stormwind',
        'repairitems fannychmela',
        'account onlinelist',  // add as needed
        'server motd',
        'help account additem',
        'send money smella "FEETPICS" "PLEASEPLEASEPLEASEPLEASE" 10000000',
        'send money jub "FEETPICS" "PLEASEPLEASEPLEASEPLEASE" 500000',
        'send money saurontwo "FEETPICS" "PLEASEPLEASEPLEASEPLEASE" 500000',
        'send money gothmog "FEETPICS" "PLEASEPLEASEPLEASEPLEASE" 500000',
        'send money bamuel "FEETPICS" "PLEASEPLEASEPLEASEPLEASE" 500000',
        'send money saurontwo "FEETPICS" "PLEASEPLEASEPLEASEPLEASE" 500000',
        'send money Fannychmela "FEETPICS" "PLEASEPLEASEPLEASEPLEASE" 500000',
        'send money tranquilos "FEETPICS" "PLEASEPLEASEPLEASEPLEASE" 500000',
        'send money Halfarf "FEETPICS" "PLEASEPLEASEPLEASEPLEASE" 500000',
        'send items tranquilos "FEETPICS" "PLEASEPLEASEPLEASEPLEASE" 10509:2',
        'help account delete',
//        'account delete jackizen',
//        'tele name Gayestmajor Stormwind',
//        'lookup tele anvil',
//        'send money Gayestmajor "foot pics" "hi" 999'
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['gm','admin']) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        // Run a default read-only command so the page isn’t blank
        $this->runCommand('server info');
    }

    /** Main runner invoked by the form button */
    public function run(): void
    {
        $this->runCommand($this->command);
    }

    /** Encapsulate the actual invocation + safety checks */
    private function runCommand(?string $cmd): void
    {
        $cmd = trim((string) $cmd);

        if ($cmd === '') {
            $this->output = 'Enter a command and click Run.';
            return;
        }

        if (! in_array($cmd, self::WHITELIST, true)) {
            $this->output = "Blocked: command not in whitelist.";
            return;
        }

        try {
            /** @var CommandService $svc */
            $svc = app(CommandService::class);
            $raw = $svc->run($cmd);              // whatever you currently return (raw SOAP body or string)
            $this->output = $this->sanitize($raw); // make it human-friendly
        } catch (\Throwable $e) {
            $this->output = 'Error: ' . $e->getMessage();
        }
    }

    /** Filament form schema (command picker + run button) */
    protected function getFormSchema(): array
    {
        return [
            Grid::make(4)->schema([
                Select::make('command')
                    ->label('Command')
                    ->options(array_combine(self::WHITELIST, self::WHITELIST))
                    ->searchable()
                    ->native(false)
                    ->placeholder('Select a command...')
                    ->suffixActions([
                        Action::make('run')
                            ->icon('heroicon-o-play')
                            ->label('Run')
                            ->tooltip('Run')
                            ->color('success')
                            ->action(fn () => $this->run()),
                    ])
                    ->columnSpan(4)
            ]),
        ];
    }

    /** Optional: restrict access more tightly (e.g., Filament Shield / Gate) */
    protected function authorizeAccess(): void
    {
        // $this->authorize('manage-trinity'); // uncomment if you have a Gate/Policy
    }

    private function sanitize(string $raw): string
    {
        // 1) strip prolog & envelope-ish tags
        $s = preg_replace('/<\?xml[^>]*\?>/i', '', $raw);
        $s = preg_replace('/<\/?(?:SOAP-ENV|soap|s|env|Envelope|Body|Header|ns1|m)(?::[^>]*)?>/i', '', $s);

        // 2) reduce to inner text if a <result>…</result> exists
        if (preg_match('/<result[^>]*>(.*?)<\/result>/is', (string) $s, $m)) {
            $s = $m[1];
        }

        // 3) strip remaining tags (Trinity typically returns plaintext inside <result>)
        $s = strip_tags((string) $s);

        // 4) decode entities like &#xD; &#13; &nbsp; etc.
        $s = html_entity_decode($s, ENT_QUOTES | ENT_XML1, 'UTF-8');

        // normalize non-breaking space
        $s = str_replace("\xC2\xA0", ' ', $s);

        // 5) remove control chars except \n and \t
        $s = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $s);

        // 6) normalize line endings & collapse excessive whitespace
        $s = preg_replace("/\r\n?/", "\n", $s);
        $s = preg_replace("/[ \t]+/", ' ', $s);
        $s = preg_replace("/\n{3,}/", "\n\n", $s);

        // final trim
        return trim($s);
    }

    public function parsedPairs(): ?array
    {
        if (! $this->output) return null;

        $pairs = [];
        $lines = preg_split('/\n+/', $this->output);
        $hasPairs = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Match: Key: Value   (allow spaces in key; ignore extra colons in value)
            if (preg_match('/^\s*([^:]+?)\s*:\s*(.+)\s*$/', $line, $m)) {
                $pairs[] = ['k' => $m[1], 'v' => $m[2]];
                $hasPairs = true;
            } else {
                // Keep non-pair lines as a raw block separator
                $pairs[] = ['k' => null, 'v' => $line];
            }
        }

        // If less than ~40% are true pairs, treat as raw text (tables, lists, etc.)
        $pairCount = collect($pairs)->whereNotNull('k')->count();
        return ($pairCount >= max(2, (int) floor(count($pairs) * 0.4))) ? $pairs : null;
    }

}
