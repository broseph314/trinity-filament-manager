<?php

namespace App\Filament\Resources\Trinity\World\ItemTemplates\Pages;

use App\Filament\Resources\Trinity\World\ItemTemplates\ItemTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListItemTemplates extends ListRecords
{
    protected static string $resource = ItemTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
