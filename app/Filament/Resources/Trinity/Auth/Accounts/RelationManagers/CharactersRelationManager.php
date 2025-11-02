<?php

namespace App\Filament\Resources\Trinity\Auth\Accounts\RelationManagers;

use App\Filament\Resources\Trinity\Characters\Characters\CharacterResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CharactersRelationManager extends RelationManager
{
    protected static string $relationship = 'characters';

    protected static ?string $title = 'Characters';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guid')->label('GUID')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('level')->badge()->color('success')->sortable(),
                Tables\Columns\TextColumn::make('class')->label('Class')->sortable(),
                Tables\Columns\TextColumn::make('race')->label('Race')->sortable(),
                Tables\Columns\TextColumn::make('map')->label('Map')->sortable(),
                Tables\Columns\TextColumn::make('zone')->label('Zone')->sortable(),
            ])
            ->actions([
                ViewAction::make('view')
                    ->label('View')
                    ->url(fn ($record) => CharacterResource::getUrl('view', ['record' => $record->guid]))
                    ,
            ])       // read-only
            ->headerActions([]) // no create
            ->bulkActions([]);
    }
}
