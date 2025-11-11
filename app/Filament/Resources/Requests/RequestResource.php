<?php

namespace App\Filament\Resources\Requests;

use App\Filament\Resources\Requests\Pages\CreateRequest;
use App\Filament\Resources\Requests\Pages\EditRequest;
use App\Filament\Resources\Requests\Pages\ListRequests;
use App\Filament\Resources\Requests\Pages\ViewRequest;
use App\Filament\Resources\Requests\RelationManagers\LinesRelationManager;
use App\Filament\Resources\Requests\Schemas\RequestForm;
use App\Filament\Resources\Requests\Schemas\RequestInfolist;
use App\Filament\Resources\Requests\Tables\RequestsTable;
use App\Models\Request;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;
    protected static string|null|BackedEnum $navigationIcon  = 'heroicon-o-inbox-stack';
    protected static ?string $navigationLabel = 'Requests';
    protected static string|null|\UnitEnum $navigationGroup = 'Trinity Tools';
    protected static ?int    $navigationSort  = 30;


    protected static ?string $recordTitleAttribute = 'id';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin','gm']) ?? false;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Counts for lines & done_lines
        return parent::getEloquentQuery()
            ->withCount([
                'lines',
                'lines as done_lines_count' => fn ($q) => $q->where('status', 'done'),
                'lines as error_lines_count' => fn ($q) => $q->where('status', 'error'),
            ])->with('user');
    }

    public static function form(Schema $schema): Schema
    {
        return RequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LinesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRequests::route('/'),
            'create' => CreateRequest::route('/create'),
            'view' => ViewRequest::route('/{record}'),
            'edit' => EditRequest::route('/{record}/edit'),
        ];
    }
}
