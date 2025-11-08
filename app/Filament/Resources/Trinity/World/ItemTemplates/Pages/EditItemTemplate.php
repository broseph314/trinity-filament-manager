<?php

namespace App\Filament\Resources\Trinity\World\ItemTemplates\Pages;

use App\Filament\Resources\Trinity\World\ItemTemplates\ItemTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditItemTemplate extends EditRecord
{
    protected static string $resource = ItemTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
