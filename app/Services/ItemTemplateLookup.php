<?php

namespace App\Services;

use App\Models\Trinity\World\ItemTemplate;

final class ItemTemplateLookup
{
    /** @var array<int, array{name:string,quality:int}> */
    private static array $cache = [];

    public static function name(int $entry): ?string
    {
        $row = self::get($entry);
        return $row['name'] ?? null;
    }

    public static function quality(int $entry): ?int
    {
        $row = self::get($entry);
        return $row['quality'] ?? null;
    }

    public static function qualityName(int $entry): ?string
    {
        $q = self::quality($entry);
        return $q === null ? null : ([
            0=>'Poor',1=>'Common',2=>'Uncommon',3=>'Rare',4=>'Epic',5=>'Legendary',6=>'Artifact',7=>'Heirloom',
        ][$q] ?? (string) $q);
    }

    public static function qualityColor(int $entry): string
    {
        $q = self::quality($entry);
        return [
            0=>'gray', 1=>'gray', 2=>'success', 3=>'info', 4=>'purple', 5=>'warning', 6=>'danger', 7=>'primary',
        ][$q] ?? 'gray';
    }

    /** @return array{name?:string,quality?:int} */
    private static function get(int $entry): array
    {
        if ($entry <= 0) return [];
        if (!isset(self::$cache[$entry])) {
            // warm missing entry
            $row = ItemTemplate::query()
                ->where('entry', $entry)
                ->first(['entry','name','Quality']);
            self::$cache[$entry] = $row ? ['name'=>$row->name, 'quality'=>(int)$row->Quality] : [];
        }
        return self::$cache[$entry];
    }

    /** Optional: batch warm a set of entries (call this with the page's entries) */
    public static function warm(array $entries): void
    {
        $entries = array_values(array_unique(array_filter(array_map('intval', $entries))));
        $missing = array_values(array_diff($entries, array_keys(self::$cache)));
        if (!$missing) return;

        ItemTemplate::query()
            ->whereIn('entry', $missing)
            ->get(['entry','name','Quality'])
            ->each(function ($row) {
                self::$cache[(int)$row->entry] = ['name'=>$row->name, 'quality'=>(int)$row->Quality];
            });
    }
}
