<?php

namespace App\Filament\Resources\Trinity\World\ItemTemplates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ItemTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('ItemTabs')
                ->persistTabInQueryString() // deep-linkable (optional)
                ->tabs([
                    // --- Overview (always shown) ---
                    Tab::make('Overview')
                        ->schema([
                            Section::make('Summary')
                                ->columns(5)
                                ->schema([
                                    TextEntry::make('summary_type')
                                        ->label('Type')
                                        ->badge()
                                        ->state(fn ($record) => sprintf(
                                            '%s · %s',
                                            self::classesMap()[$record->class] ?? $record->class,
                                            self::inventoryTypesMap()[$record->InventoryType] ?? $record->InventoryType
                                        )),

                                    TextEntry::make('summary_quality')
                                        ->label('Quality')
                                        ->badge()
                                        ->state(fn ($record) => match ((int) $record->Quality) {
                                            0=>'Poor',1=>'Common',2=>'Uncommon',3=>'Rare',4=>'Epic',
                                            5=>'Legendary',6=>'Artifact',7=>'Heirloom', default => (string) $record->Quality
                                        })
                                        ->color(fn ($record) => self::qualityColor((int) $record->Quality)),

                                    TextEntry::make('summary_levels')
                                        ->label('Levels')
                                        ->state(fn ($record) => "Item Lv {$record->ItemLevel} · Req {$record->RequiredLevel}"),

                                    TextEntry::make('summary_price')
                                        ->label('Vendor')
                                        ->state(fn ($record) => 'Buy '.self::money($record->BuyPrice).' · Sell '.self::money($record->SellPrice))
                                        ->visible(fn ($record) => (int) $record->BuyPrice > 0 || (int) $record->SellPrice > 0),

                                    TextEntry::make('summary_combat')
                                        ->label('Stats')
                                        ->state(function ($record) {
                                            $parts = [];
                                            if ((int) $record->Armor > 0) $parts[] = "Armor {$record->Armor}";
                                            if ((int) $record->dmg_min1 > 0) {
                                                $parts[] = "Damage {$record->dmg_min1}-{$record->dmg_max1}";
                                                if ((int) $record->delay > 0) $parts[] = sprintf('Speed %.2fs', $record->delay / 1000);
                                            }
                                            return $parts ? implode(' · ', $parts) : '—';
                                        })
                                        ->visible(fn ($record) => (int) $record->Armor > 0 || (int) $record->dmg_min1 > 0),
                                ]),

                            Section::make('Item')
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('entry')->label('Entry')->copyable(),
                                    TextEntry::make('name')->label('Name')->weight('semibold')->columnSpan(2),
                                    TextEntry::make('Quality')
                                        ->label('Quality')
                                        ->badge()
                                        ->color(fn ($record) => self::qualityColor((int) $record->Quality)),
                                    Grid::make(3)->schema([
                                        TextEntry::make('class')
                                            ->label('Class')
                                            ->formatStateUsing(fn ($v) => self::classesMap()[$v] ?? $v),

                                        TextEntry::make('subclass')
                                            ->label('Subclass')
                                            ->formatStateUsing(fn ($v, $record) =>
                                            isset($record->class) ? (self::subclassesMap()[(int) $record->class][$v] ?? $v) : $v
                                            ),

                                        TextEntry::make('InventoryType')
                                            ->label('Inv. Type')
                                            ->formatStateUsing(fn ($v) => self::inventoryTypesMap()[$v] ?? $v),
                                    ]),
                                ]),

                            Section::make('Economy')
                                ->columns(5)
                                ->schema([
                                    TextEntry::make('BuyCount')->label('Vendor Stack')->numeric(),
                                    TextEntry::make('BuyPrice')->label('Buy Price')->formatStateUsing(fn ($v) => self::money($v)),
                                    TextEntry::make('SellPrice')->label('Sell Price')->formatStateUsing(fn ($v) => self::money($v)),
                                    TextEntry::make('stackable')->label('Stack Size')->placeholder('-'),
                                    TextEntry::make('ContainerSlots')->label('Container Slots')->placeholder('-'),
                                ])
                                ->visible(self::visibleIfAny(['BuyCount','BuyPrice','SellPrice','stackable','ContainerSlots'])),

                        ]),

                    // --- Requirements tab ---
                    Tab::make('Requirements')
                        ->schema([
                            Section::make('Requirements & Levels')
                                ->columns(4)
                                ->schema([
                                    TextEntry::make('ItemLevel')->label('Item Level'),
                                    TextEntry::make('RequiredLevel')->label('Required Level'),
                                    TextEntry::make('bonding')->label('Bonding')->badge()->color('gray'),
                                    TextEntry::make('MaxDurability')->label('Max Durability')->placeholder('-'),
                                    TextEntry::make('AllowableClass')->label('Allowed Classes (mask)')->placeholder('-'),
                                    TextEntry::make('AllowableRace')->label('Allowed Races (mask)')->placeholder('-'),
                                    TextEntry::make('startquest')->label('Starts Quest')->placeholder('-'),
                                    TextEntry::make('RandomProperty')->label('Random Property')->placeholder('-'),
                                ])
                                ->visible(self::visibleIfAny([
                                    'ItemLevel','RequiredLevel','bonding','MaxDurability',
                                    'AllowableClass','AllowableRace','startquest','RandomProperty',
                                ])),
                        ])
                        ->visible(self::visibleIfAny([
                            'ItemLevel','RequiredLevel','bonding','MaxDurability',
                            'AllowableClass','AllowableRace','startquest','RandomProperty',
                        ])),

                    // --- Combat tab (weapons/armor) ---
                    Tab::make('Combat')
                        ->schema([
                            Section::make('Weapon / Armor Stats')
                                ->columns(6)
                                ->schema([
                                    TextEntry::make('dmg_min1')->label('Dmg Min'),
                                    TextEntry::make('dmg_max1')->label('Dmg Max'),
                                    TextEntry::make('dmg_type1')->label('Dmg Type'),
                                    TextEntry::make('delay')->label('Speed (ms)'),
                                    TextEntry::make('Armor')->label('Armor'),
                                    TextEntry::make('block')->label('Block'),

                                    TextEntry::make('dmg_min2')->label('Dmg2 Min')
                                        ->visible(fn ($component) => (int) ($component->getRecord()?->dmg_min2 ?? 0) > 0),
                                    TextEntry::make('dmg_max2')->label('Dmg2 Max')
                                        ->visible(fn ($component) => (int) ($component->getRecord()?->dmg_min2 ?? 0) > 0),
                                    TextEntry::make('dmg_type2')->label('Dmg2 Type')
                                        ->visible(fn ($component) => (int) ($component->getRecord()?->dmg_min2 ?? 0) > 0),

                                    TextEntry::make('holy_res')->label('Holy Res'),
                                    TextEntry::make('fire_res')->label('Fire Res'),
                                    TextEntry::make('nature_res')->label('Nature Res'),
                                    TextEntry::make('frost_res')->label('Frost Res'),
                                    TextEntry::make('shadow_res')->label('Shadow Res'),
                                    TextEntry::make('arcane_res')->label('Arcane Res'),
                                ]),
                        ])
                        ->visible(fn ($component) => self::anyTruthy($component->getRecord(), [
                            'Armor','block','delay','dmg_min1','dmg_max1','dmg_min2','dmg_max2',
                            'holy_res','fire_res','nature_res','frost_res','shadow_res','arcane_res',
                        ])),

                    // --- Stats tab ---
                    Tab::make('Stats')
                        ->schema([
                            Section::make('Stats (1–10)')
                                ->columns(5)
                                ->schema([...self::statRows()]),
                        ])
                        ->visible(fn ($component) => self::anyStatPresent($component->getRecord())),

                    // --- Spells tab ---
                    Tab::make('Spells')
                        ->schema([
                            Section::make('Spells')
                                ->columns(6)
                                ->schema([
                                    ...self::spellRows(1),
                                    ...self::spellRows(2),
                                    ...self::spellRows(3),
                                    ...self::spellRows(4),
                                    ...self::spellRows(5),
                                ]),
                        ])
                        ->visible(fn ($component) => self::anySpellPresent($component->getRecord())),

                    // --- Sockets tab ---
                    Tab::make('Sockets')
                        ->schema([
                            Section::make('Sockets & Gems')
                                ->columns(6)
                                ->schema([
                                    TextEntry::make('socketColor_1')->label('Socket 1')
                                        ->visible(fn (TextEntry $e) => (int) ($e->getState() ?? 0) !== 0),
                                    TextEntry::make('socketContent_1')->label('Socket 1 Gem')
                                        ->visible(fn (TextEntry $e) => (int) ($e->getState() ?? 0) !== 0),

                                    TextEntry::make('socketColor_2')->label('Socket 2')
                                        ->visible(fn (TextEntry $e) => (int) ($e->getState() ?? 0) !== 0),
                                    TextEntry::make('socketContent_2')->label('Socket 2 Gem')
                                        ->visible(fn (TextEntry $e) => (int) ($e->getState() ?? 0) !== 0),

                                    TextEntry::make('socketColor_3')->label('Socket 3')
                                        ->visible(fn (TextEntry $e) => (int) ($e->getState() ?? 0) !== 0),
                                    TextEntry::make('socketContent_3')->label('Socket 3 Gem')
                                        ->visible(fn (TextEntry $e) => (int) ($e->getState() ?? 0) !== 0),

                                    TextEntry::make('socketBonus')->label('Socket Bonus')->placeholder('-'),
                                    TextEntry::make('GemProperties')->label('Gem Properties')->placeholder('-'),
                                ]),
                        ])
                        ->visible(fn ($component) => self::anyTruthy($component->getRecord(), [
                            'socketColor_1','socketContent_1','socketColor_2','socketContent_2',
                            'socketColor_3','socketContent_3','socketBonus','GemProperties',
                        ])),

                    // --- Extras tab ---
                    Tab::make('Extras')
                        ->schema([
                            Section::make('Extras')
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('PageText')->label('Page Text')->placeholder('-'),
                                    TextEntry::make('LanguageID')->label('Language')->placeholder('-'),
                                    TextEntry::make('PageMaterial')->label('Page Material')->placeholder('-'),
                                    TextEntry::make('sheath')->label('Sheath')->placeholder('-'),
                                    TextEntry::make('ScriptName')->label('Script Name')->placeholder('-'),
                                    TextEntry::make('VerifiedBuild')->label('Verified Build')->placeholder('-'),
                                    TextEntry::make('flagsCustom')->label('Flags (Custom)')->placeholder('-'),
                                    TextEntry::make('Flags')->label('Flags')->placeholder('-'),
                                    TextEntry::make('FlagsExtra')->label('Flags Extra')->placeholder('-'),
                                    TextEntry::make('DisenchantID')->label('Disenchant ID')->placeholder('-'),
                                    TextEntry::make('RequiredDisenchantSkill')->label('Req. Disenchant Skill')->placeholder('-'),
                                    TextEntry::make('FoodType')->label('Food Type')->placeholder('-'),
                                    TextEntry::make('minMoneyLoot')->label('Min Money Loot')->formatStateUsing(fn ($v) => self::money($v))->placeholder('-'),
                                    TextEntry::make('maxMoneyLoot')->label('Max Money Loot')->formatStateUsing(fn ($v) => self::money($v))->placeholder('-'),
                                ]),
                        ])
                        ->visible(self::visibleIfAny([
                            'PageText','LanguageID','PageMaterial','sheath','ScriptName','VerifiedBuild',
                            'flagsCustom','Flags','FlagsExtra','DisenchantID','RequiredDisenchantSkill',
                            'FoodType','minMoneyLoot','maxMoneyLoot',
                        ])),
                ]),
        ])
            ->columns(1);
    }

    // ---------------- helpers ----------------

    private static function qualityColor(int $q): string
    {
        return match ($q) {
            0,1 => 'gray',
            2    => 'success',
            3    => 'info',
            4    => 'purple',
            5    => 'warning',
            6    => 'danger',
            7    => 'primary',
            default => 'gray',
        };
    }

    private static function money($copper): string
    {
        $c = (int) $copper;
        $g = intdiv($c, 10000);
        $s = intdiv($c % 10000, 100);
        $k = $c % 100;
        if ($g) return sprintf('%dg %ds %dc', $g, $s, $k);
        if ($s) return sprintf('%ds %dc', $s, $k);
        return sprintf('%dc', $k);
    }

    private static function classesMap(): array
    {
        return [
            0=>'Consumable', 1=>'Container', 2=>'Weapon', 3=>'Gem', 4=>'Armor',
            7=>'Trade Goods', 9=>'Recipe', 12=>'Quest', 13=>'Key', 15=>'Misc',
        ];
    }

    private static function subclassesMap(): array
    {
        return [
            2 => [
                0=>'Axe',1=>'Axe (2H)',2=>'Bow',3=>'Gun',4=>'Mace',5=>'Mace (2H)',
                6=>'Polearm',7=>'Sword',8=>'Sword (2H)',10=>'Staff',13=>'Fist',
                15=>'Dagger',16=>'Thrown',18=>'Crossbow',19=>'Wand',
            ],
            4 => [
                0=>'Misc',1=>'Cloth',2=>'Leather',3=>'Mail',4=>'Plate',6=>'Shield',
            ],
        ];
    }

    private static function inventoryTypesMap(): array
    {
        return [
            0=>'Non-equip',1=>'Head',2=>'Neck',3=>'Shoulder',4=>'Shirt',5=>'Chest',
            6=>'Waist',7=>'Legs',8=>'Feet',9=>'Wrist',10=>'Hands',11=>'Finger',
            12=>'Trinket',13=>'One-Hand',14=>'Shield',15=>'Ranged',16=>'Back',
            17=>'Two-Hand',18=>'Bag',19=>'Tabard',20=>'Robe',21=>'Main Hand',
            22=>'Off Hand',23=>'Holdable',25=>'Thrown',26=>'Ranged (right)',28=>'Relic',
        ];
    }

    /** Build stat rows (type/value pairs 1..10), shown only when present */
    private static function statRows(): array
    {
        $rows = [];

        for ($i = 1; $i <= 10; $i++) {
            $typeField  = "stat_type{$i}";
            $valueField = "stat_value{$i}";

            // Show both columns only if the type is non-zero.
            $visible = fn ($component) => (int) ($component->getRecord()?->{$typeField} ?? 0) !== 0;

            $rows[] = \Filament\Infolists\Components\TextEntry::make($typeField)
                ->label("Stat {$i} Type")
                ->formatStateUsing(fn ($state) => self::statTypeMap()[$state] ?? $state)
                ->visible($visible);

            $rows[] = \Filament\Infolists\Components\TextEntry::make($valueField)
                ->label("Stat {$i} Value")
                ->visible($visible);
        }

        return $rows;
    }

    private static function statTypeMap(): array
    {
        return [
            0 => 'None',
            3 => 'Agility',
            4 => 'Strength',
            5 => 'Intellect',
            6 => 'Spirit',
            7 => 'Stamina',
            13 => 'Defense',
            14 => 'Dodge',
            15 => 'Parry',
            16 => 'Block',
            31 => 'Hit (Melee)',
            32 => 'Hit (Ranged)',
            33 => 'Hit (Spell)',
            34 => 'Crit (Melee)',
            35 => 'Crit (Ranged)',
            36 => 'Crit (Spell)',
            37 => 'Hit Avoid',
            38 => 'Crit Avoid',
            45 => 'Resilience',
            46 => 'Haste (Melee)',
            47 => 'Haste (Ranged)',
            48 => 'Haste (Spell)',
            // ... will add more when i can be bothered
        ];
    }


    /** Build spell rows for N (id/trigger/charges/ppm/cooldowns) */
    private static function spellRows(int $n): array
    {
        $rows = [];

        // Only show any of the N-th spell fields if the base id exists (> 0)
        $isVisible = fn ($component) =>
            (int) ($component->getRecord()?->{"spellid_{$n}"} ?? 0) > 0;

        // id
        $rows[] = \Filament\Infolists\Components\TextEntry::make("spellid_{$n}")
            ->label("Spell {$n}")
            ->visible($isVisible);

        // trigger
        $rows[] = \Filament\Infolists\Components\TextEntry::make("spelltrigger_{$n}")
            ->label("Trigger {$n}")
            ->formatStateUsing(fn ($state) => self::spellTriggerMap()[$state] ?? $state)
            ->visible($isVisible);

        // charges
        $rows[] = \Filament\Infolists\Components\TextEntry::make("spellcharges_{$n}")
            ->label("Charges {$n}")
            ->formatStateUsing(function ($state) {
                if ($state === null) return '-';
                $v = (int) $state;
                return $v < 0 ? 'Unlimited' : $v;
            })
            ->visible($isVisible);

        // ppmRate
        $rows[] = \Filament\Infolists\Components\TextEntry::make("spellppmRate_{$n}")
            ->label("PPM {$n}")
            ->visible($isVisible);

        // cooldown (ms → s)
        $rows[] = \Filament\Infolists\Components\TextEntry::make("spellcooldown_{$n}")
            ->label("Cooldown {$n}")
            ->formatStateUsing(function ($state) {
                if ($state === null) return '-';
                $ms = (int) $state;
                if ($ms <= 0) return '—';
                return sprintf('%.1fs', $ms / 1000);
            })
            ->visible($isVisible);

        // category + category cooldown (ms → s)
        $rows[] = \Filament\Infolists\Components\TextEntry::make("spellcategory_{$n}")
            ->label("Category {$n}")
            ->visible($isVisible);

        $rows[] = \Filament\Infolists\Components\TextEntry::make("spellcategorycooldown_{$n}")
            ->label("Cat. CD {$n}")
            ->formatStateUsing(function ($state) {
                if ($state === null) return '-';
                $ms = (int) $state;
                if ($ms <= 0) return '—';
                return sprintf('%.1fs', $ms / 1000);
            })
            ->visible($isVisible);

        return $rows;
    }

    private static function spellTriggerMap(): array
    {
        return [
            0 => 'Use',
            1 => 'On Equip',
            2 => 'Chance on Hit',
            4 => 'Soulstone',
        ];
    }

    private static function visibleIfAny(array $fields): \Closure
    {
        return fn ($component) => self::anyTruthy($component->getRecord(), $fields);
    }

    /** Returns true if the record has any non-empty value across fields */
    private static function anyTruthy($record, array $fields): bool
    {
        if (!$record) return false;
        foreach ($fields as $f) {
            // Consider 0 and '0' as empty for our purposes here; tweak if needed:
            $v = $record->{$f} ?? null;
            if ($v !== null && $v !== '' && $v !== 0 && $v !== '0') {
                return true;
            }
        }
        return false;
    }

    /** Any stat_type# > 0 ? */
    private static function anyStatPresent($record): bool
    {
        if (!$record) return false;
        for ($i = 1; $i <= 10; $i++) {
            if ((int)($record->{"stat_type{$i}"} ?? 0) > 0) {
                return true;
            }
        }
        return false;
    }

    /** Any spellid_N > 0 ? */
    private static function anySpellPresent($record): bool
    {
        if (!$record) return false;
        for ($n = 1; $n <= 5; $n++) {
            if ((int)($record->{"spellid_{$n}"} ?? 0) > 0) {
                return true;
            }
        }
        return false;
    }

}
