<?php

namespace App\Services\Trinity;

class CommandService
{
    public function __construct(private readonly SoapClient $soap) {}

    public static function make(): self
    {
        return new self(SoapClient::makeFromConfig());
    }

    // Generic runner (you can log/authorize per command here)
    public function run(string $command): string
    {
        return $this->soap->command($command);
    }

    // Safe helpers you’ll use in the UI:
    public function serverInfo(): string
    {
        return $this->run('server info');
    }

    public function help(?string $filter = null): string
    {
        return $this->run($filter ? "help {$filter}" : 'help');
    }

    // Example: teleport via direct player command (adjust to your core’s syntax),
    // or keep this for later when you build TeleportService.
    public function summon(string $playerName): string
    {
        return $this->run("summon {$playerName}");
    }

    public function goXYZ(float $x, float $y, float $z, int $map): string
    {
        return $this->run(sprintf('go xyz %F %F %F %d', $x, $y, $z, $map));
    }
}
