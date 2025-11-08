<?php

namespace App\Filament\Resources\Trinity\World\ItemTemplates\Pages;

use App\Filament\Resources\Trinity\World\ItemTemplates\ItemTemplateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewItemTemplate extends ViewRecord
{
    protected static string $resource = ItemTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
