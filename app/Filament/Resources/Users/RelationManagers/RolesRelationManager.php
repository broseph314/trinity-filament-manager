<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('name')
                    ->label('Role')
                    ->options(fn () => Role::query()->pluck('name', 'name'))
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->limit(5) // optional: show up to 5 badges, rest collapsed
                    ->separator(',')
                    ->color('info')
                    ->toggleable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->badge(),
                TextColumn::make('guard_name')->label('Guard')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('permissions.name')
                    ->label('Permissions')
//                    ->limit(5)
                    ->color('success')
                    ->badge()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No roles yet');
    }
}
