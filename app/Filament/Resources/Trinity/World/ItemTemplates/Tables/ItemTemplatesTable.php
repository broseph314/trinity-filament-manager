<?php

namespace App\Filament\Resources\Trinity\World\ItemTemplates\Tables;

use App\Models\Trinity\World\ItemTemplate;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
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

                // Fancy item panel
                ViewColumn::make('item')
                    ->label('Item')
                    ->view('filament.tables.columns.item-template-card')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('name', $direction))
                    ->searchable(query: function ($query, $search) {
                        $s = strtolower($search);
                        $query->whereRaw('LOWER(name) LIKE ?', ["%{$s}%"]);
                    }),

                // Keep compact numeric columns if you want quick scan/sort
                TextColumn::make('ItemLevel')->label('iLvl')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('RequiredLevel')->label('Req Lvl')->sortable()->toggleable(isToggledHiddenByDefault: true),

                // These now use model helpers
                TextColumn::make('class')
                    ->label('Class')
                    ->formatStateUsing(fn ($state, ItemTemplate $record) => $record->classLabel())
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('InventoryType')
                    ->label('Inv. Type')
                    ->formatStateUsing(fn ($state, ItemTemplate $record) => $record->inventoryTypeLabel())
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('entry') // or ->defaultSort('name') if you prefer
            ->filters([
                SelectFilter::make('Quality')
                    ->label('Quality')
                    ->options(ItemTemplate::QUALITY_LABELS),

                SelectFilter::make('class')
                    ->label('Class')
                    ->options(ItemTemplate::CLASS_LABELS)
                    ->searchable(),
            ])
            ->paginated([5,10,25, 50, 100]);
    }
}
