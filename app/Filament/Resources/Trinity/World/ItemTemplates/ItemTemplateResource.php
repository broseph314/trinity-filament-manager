<?php

namespace App\Filament\Resources\Trinity\World\ItemTemplates;

use App\Filament\Resources\Trinity\World\ItemTemplates\Pages\CreateItemTemplate;
use App\Filament\Resources\Trinity\World\ItemTemplates\Pages\EditItemTemplate;
use App\Filament\Resources\Trinity\World\ItemTemplates\Pages\ListItemTemplates;
use App\Filament\Resources\Trinity\World\ItemTemplates\Pages\ViewItemTemplate;
use App\Filament\Resources\Trinity\World\ItemTemplates\Schemas\ItemTemplateForm;
use App\Filament\Resources\Trinity\World\ItemTemplates\Schemas\ItemTemplateInfolist;
use App\Filament\Resources\Trinity\World\ItemTemplates\Tables\ItemTemplatesTable;
use App\Models\Trinity\World\ItemTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ItemTemplateResource extends Resource
{
    protected static ?string $model = ItemTemplate::class;

    protected static ?string $label = "Item Listing";

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArchiveBox;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ItemTemplateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ItemTemplateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemTemplatesTable::configure($table);
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemTemplates::route('/'),
            'view' => ViewItemTemplate::route('/{record}'),
        ];
    }
}
