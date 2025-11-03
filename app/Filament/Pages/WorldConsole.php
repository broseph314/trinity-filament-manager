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
        'server info',
        'help',
        'uptime',
         'account onlinelist',  // add as needed
         'server motd',         // read-only
    ];

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
            $this->output = $svc->run($cmd);
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
