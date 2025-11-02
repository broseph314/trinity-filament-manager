<?php

namespace App\Filament\Resources\Trinity\Auth\Accounts;

use App\Filament\Resources\Trinity\Auth\Accounts\Pages\CreateAccount;
use App\Filament\Resources\Trinity\Auth\Accounts\Pages\EditAccount;
use App\Filament\Resources\Trinity\Auth\Accounts\Pages\ListAccounts;
use App\Filament\Resources\Trinity\Auth\Accounts\Pages\ViewAccount;
use App\Filament\Resources\Trinity\Auth\Accounts\Schemas\AccountForm;
use App\Filament\Resources\Trinity\Auth\Accounts\Schemas\AccountInfolist;
use App\Filament\Resources\Trinity\Auth\Accounts\Tables\AccountsTable;
use App\Models\Trinity\Auth\Account;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::User;

    protected static ?string $recordTitleAttribute = 'username';

    public static function form(Schema $schema): Schema
    {
        return AccountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CharactersRelationManager::class,
        ];
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }


    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'view' => ViewAccount::route('/{record}'),
        ];
    }
}
