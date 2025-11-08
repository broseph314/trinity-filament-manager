<?php

namespace App\Filament\Resources\Trinity\Characters\Characters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CharacterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema;
    }
}
