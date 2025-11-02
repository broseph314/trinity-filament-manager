<?php

namespace App\Filament\Resources\Trinity\Characters\Characters\Pages;

use App\Filament\Resources\Trinity\Characters\Characters\CharacterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCharacter extends ViewRecord
{
    protected static string $resource = CharacterResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
