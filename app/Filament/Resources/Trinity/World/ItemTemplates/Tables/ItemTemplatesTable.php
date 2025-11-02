<?php

namespace App\Filament\Resources\Trinity\World\ItemTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ItemTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('entry')
                ->label('Entry')
                ->sortable()
                ->copyable(),

            TextColumn::make('name')
                ->label('Name')
                ->searchable()
                ->wrap()
                ->weight('semibold'),

            TextColumn::make('ItemLevel')
                ->label('iLvl')
                ->sortable(),

            TextColumn::make('RequiredLevel')
                ->label('Req Lvl')
                ->sortable(),

            TextColumn::make('class')
                ->label('Class')
                ->formatStateUsing(fn ($v) => self::classesMap()[$v] ?? $v)
                ->toggleable(),

            TextColumn::make('InventoryType')
                ->label('Inv. Type')
                ->formatStateUsing(fn ($v) => self::inventoryTypesMap()[$v] ?? $v)
                ->toggleable(),
        ])
        ->defaultSort('entry')
        ->filters([
            SelectFilter::make('Quality')
                ->label('Quality')
                ->options([
                    0=>'Poor',1=>'Common',2=>'Uncommon',3=>'Rare',
                    4=>'Epic',5=>'Legendary',6=>'Artifact',7=>'Heirloom',
                ]),
            SelectFilter::make('class')
                ->label('Class')
                ->options(self::classesMap())
                ->searchable(),
        ])
        ->paginated([25, 50, 100]);
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
            2 => [ // Weapons
                0=>'Axe',1=>'Axe (2H)',2=>'Bow',3=>'Gun',4=>'Mace',5=>'Mace (2H)',
                6=>'Polearm',7=>'Sword',8=>'Sword (2H)',10=>'Staff',13=>'Fist',15=>'Dagger',
                16=>'Thrown',18=>'Crossbow',19=>'Wand',
            ],
            4 => [ // Armor
                0=>'Misc',1=>'Cloth',2=>'Leather',3=>'Mail',4=>'Plate',6=>'Shield',
            ],
            // add more only if you need them; otherwise show raw values
        ];
    }

    private static function inventoryTypesMap(): array
    {
        return [
            0=>'Non-equip',1=>'Head',2=>'Neck',3=>'Shoulder',4=>'Shirt',5=>'Chest',
            6=>'Waist',7=>'Legs',8=>'Feet',9=>'Wrist',10=>'Hands',11=>'Finger',
            12=>'Trinket',13=>'One-Hand',14=>'Shield',15=>'Ranged',16=>'Back',
            17=>'Two-Hand',18=>'Bag',19=>'Tabard',20=>'Robe',21=>'Main Hand',
            22=>'Off Hand',23=>'Holdable',25=>'Thrown',26=>'Ranged (right)',
            28=>'Relic',
        ];
    }
}
