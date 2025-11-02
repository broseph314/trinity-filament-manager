<?php

namespace App\Filament\Resources\Trinity\Characters\CharacterResource\RelationManagers;

use App\Filament\Resources\Trinity\World\ItemTemplates\ItemTemplateResource;
use App\Models\Trinity\World\ItemTemplate;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
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
                // return is optional; both mutate and return work
                return $query->with('itemInstance');
            })
            ->columns([
                // Use the actual DB fields for sorting; format via accessors
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

                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item')
                    ->wrap()
                    ->searchable(
                    // customize both the per-column and global search behaviour
                        query: function (Builder $query, string $search): Builder {
                            // If user typed a number, also match by entry directly:
                            $entryIds = collect();
                            if (is_numeric($search)) {
                                $entryIds->push((int) $search);
                            }

                            // Look up entries by name in the world DB (separate query, so no cross-DB join)
                            $byName = ItemTemplate::query()
                                ->where('name', 'like', '%' . $search . '%')
                                ->limit(500) // cap for sanity; raise if you need
                                ->pluck('entry');

                            $entryIds = $entryIds->merge($byName)->unique()->values();

                            // If nothing matched, return a 'false' condition to avoid loading everything
                            if ($entryIds->isEmpty()) {
                                return $query->whereRaw('0 = 1');
                            }

                            // Constrain by item_instance.itemEntry
                            return $query->whereHas('itemInstance', function (Builder $iq) use ($entryIds) {
                                $iq->whereIn('itemEntry', $entryIds);
                            });
                        },
                        isIndividual: true,   // allow the column’s own search input (if enabled)
                        isGlobal: true        // include it in the table’s global search box
                    )
                    ->url(fn ($record) => ItemTemplateResource::getUrl('view', ['record' => $record->item_entry]))
                    ->openUrlInNewTab(),

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
            ->paginated([25, 50, 100])
            ->searchPlaceholder('Search item name or entry…')
            ->filters([
                Tables\Filters\SelectFilter::make('bag')
                    ->label('Bag')
                    ->options([0 => 'Backpack', 1 => 'Bag 1', 2 => 'Bag 2', 3 => 'Bag 3', 4 => 'Bag 4'])
                    ->searchable(),
            ])
            ->headerActions([]) // read-only
            ->actions([])       // no row actions
            ->bulkActions([]);
    }
}
