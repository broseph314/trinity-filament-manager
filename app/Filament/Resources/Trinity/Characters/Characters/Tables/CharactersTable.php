<?php

namespace App\Filament\Resources\Trinity\Characters\Characters\Tables;

use App\Filament\Resources\Trinity\Auth\Accounts\AccountResource;
use App\Filament\Resources\Trinity\Auth\Accounts\Pages\CreateAccount;
use App\Filament\Resources\Trinity\Characters\Characters\Pages\CreateCharacter;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class CharactersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('guid')
                    ->label('GUID')
                    ->sortable()
                    ->copyable()
                    ->copyMessage('GUID copied'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                // Account link (click to view Account in Filament)
                TextColumn::make('account')
                    ->label('Account')
                    ->url(fn ($record) => AccountResource::getUrl('view', ['record' => $record->account]))
                    ->openUrlInNewTab()
                    ->sortable(),

                TextColumn::make('level')
                    ->sortable()
                    ->badge(),

                TextColumn::make('class_name')
                    ->label('Class')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('race_name')
                    ->label('Race')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('gender_name')
                    ->label('Gender')
                    ->toggleable(),

                IconColumn::make('online')
                    ->boolean()
                    ->label('Online')
                    ->trueIcon('heroicon-m-bolt')
                    ->falseIcon('heroicon-m-power')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->sortable(),

                // Map / Zone (toggleable – shown if you’ve got data)
                TextColumn::make('map')
                    ->label('Map')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('zone')
                    ->label('Zone')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Money (gold.silver.copper)
                TextColumn::make('money_human')
                    ->label('Gold')
                    ->sortable(query: function ($query, $direction) {
                        // money column is integer copper; keep native sort
                        return $query->orderBy('money', $direction);
                    }),

                // Total time played (human)
                TextColumn::make('totaltime_human')
                    ->label('Played')
                    ->tooltip(fn ($record) => number_format((int) $record->totaltime) . 's total')
                    ->toggleable(),

                // Last logout
                TextColumn::make('logout_time_dt')
                    ->label('Last Logout')
                    ->dateTime()
                    ->sortable()
                    ->since()      // shows “2 hours ago”
                    ->toggleable(isToggledHiddenByDefault: true),

                // Coords (hidden by default, handy for debugging)
                TextColumn::make('position')
                    ->label('Pos (x,y,z)')
                    ->formatStateUsing(fn ($record) => sprintf('%.1f, %.1f, %.1f', $record->position_x, $record->position_y, $record->position_z))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('name')
            ->searchPlaceholder('Search name or GUID…')
            ->recordUrl(null) // don’t auto-link to edit
            ->recordActions([
                ViewAction::make(),
            ])

            ->filters([
                TernaryFilter::make('online')
                    ->label('Online')
                    ->placeholder('All')
                    ->trueLabel('Online')
                    ->falseLabel('Offline')
                    ->queries(
                        true: fn ($q) => $q->where('online', 1),
                        false: fn ($q) => $q->where('online', 0),
                        blank: fn ($q) => $q
                    ),

                SelectFilter::make('class')
                    ->label('Class')
                    ->options(self::classChoices())
                    ->searchable(),

                SelectFilter::make('race')
                    ->label('Race')
                    ->options(self::raceChoices())
                    ->searchable(),

                SelectFilter::make('level')
                    ->label('Level')
                    ->options(collect(range(1, 80))->mapWithKeys(fn ($l) => [$l => $l])->all())
                    ->searchable()
                    ->native(false),
            ])

            ->paginated([25, 50, 100])
            ->persistFiltersInSession()
            ->persistColumnSearchesInSession()
            ->striped();
    }

    // Helpers for filter options (mirror the accessors below)
    private static function classChoices(): array
    {
        return [
            1 => 'Warrior',
            2 => 'Paladin',
            3 => 'Hunter',
            4 => 'Rogue',
            5 => 'Priest',
            6 => 'Death Knight',
            7 => 'Shaman',
            8 => 'Mage',
            9 => 'Warlock',
            11 => 'Druid',
        ];
    }

    private static function raceChoices(): array
    {
        return [
            1 => 'Human',
            2 => 'Orc',
            3 => 'Dwarf',
            4 => 'Night Elf',
            5 => 'Undead',
            6 => 'Tauren',
            7 => 'Gnome',
            8 => 'Troll',
            10 => 'Blood Elf',
            11 => 'Draenei',
        ];
    }
}
