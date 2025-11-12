<?php

namespace App\Filament\Resources\Trinity\Characters\CharacterResource\RelationManagers;

use App\Filament\Resources\Trinity\World\ItemTemplates\ItemTemplateResource;
use App\Models\Trinity\World\ItemTemplate;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';
    protected static ?string $title = 'Inventory';


    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with('itemInstance.template');
            })
            ->columns([
                Tables\Columns\TextColumn::make('bag')
                    ->label('Bag')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state == 0 ? 'Backpack' : "Bag {$state}")
                    ->sortable(),

                Tables\Columns\TextColumn::make('slot')
                    ->label('Slot')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                // Fancy item panel
                ViewColumn::make('item_panel')
                    ->label('Item')
                    ->state(fn ($record) => $record->itemInstance?->template) // <- pass ItemTemplate instance
                    ->view('filament.tables.columns.item-panel-inventory')      // your existing card
                    ->sortable(false)                                         // sorting handled by text column (below)
                    ->searchable(false),


                Tables\Columns\TextColumn::make('item_entry')
                    ->label('Entry')
                    ->copyable()
                    ->copyMessage('Copied')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('item_count')
                    ->label('Qty')
                    ->badge(),

                Tables\Columns\TextColumn::make('quality_name')
                    ->label('Quality')
                    ->badge()
                    ->color(fn ($record) => $record->quality_color),

                // Optional: show iLvl / req level if you add those to ItemTemplateLookup
                // Tables\Columns\TextColumn::make('item_level')->label('iLvl')->sortable(),
                // Tables\Columns\TextColumn::make('required_level')->label('Req Lvl')->sortable(),
            ])
            ->defaultSort('bag')
            ->paginated([5,10,25, 50, 100])
            ->searchPlaceholder('Search item name or entry…')
            ->filters([
                Tables\Filters\SelectFilter::make('bag')
                    ->label('Bag')
                    ->options([0 => 'Backpack', 1 => 'Bag 1', 2 => 'Bag 2', 3 => 'Bag 3', 4 => 'Bag 4'])
                    ->searchable(),
            ])
            ->headerActions([]);
    }
}
