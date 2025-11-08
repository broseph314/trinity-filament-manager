<?php

namespace App\Filament\Resources\Trinity\Characters\Characters;

use App\Filament\Resources\Trinity\Characters\CharacterResource\RelationManagers\InventoriesRelationManager;
use App\Filament\Resources\Trinity\Characters\Characters\Pages\CreateCharacter;
use App\Filament\Resources\Trinity\Characters\Characters\Pages\EditCharacter;
use App\Filament\Resources\Trinity\Characters\Characters\Pages\ListCharacters;
use App\Filament\Resources\Trinity\Characters\Characters\Pages\ViewCharacter;
use App\Filament\Resources\Trinity\Characters\Characters\Schemas\CharacterForm;
use App\Filament\Resources\Trinity\Characters\Characters\Schemas\CharacterInfolist;
use App\Filament\Resources\Trinity\Characters\Characters\Tables\CharactersTable;
use App\Models\Trinity\Characters\Character;
use BackedEnum;
use Filament\Forms\Components\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CharacterResource extends Resource
{
    protected static ?string $model = Character::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CharacterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CharacterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CharactersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InventoriesRelationManager::class,
        ];
    }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyPermission(['characters.view.own','characters.view.any']) ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyPermission(['characters.view.any','characters.view.own']) ?? false;
    }

    public static function canView(Model $record): bool
    {

        if(auth()->user()?->hasPermissionTo('characters.view.any')) {
            return true;
        }

        if (auth()->user()?->hasPermissionTo('characters.view.own')) {
            return $record->account_id === auth()->user()->trinityLink?->trinity_account_id;
        }

        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCharacters::route('/'),
            'view' => ViewCharacter::route('/{record}'),
        ];
    }
}
