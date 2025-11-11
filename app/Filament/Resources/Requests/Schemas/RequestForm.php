<?php

namespace App\Filament\Resources\Requests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('character_guid')
                    ->required()
                    ->numeric(),
                TextInput::make('character_name')
                    ->required(),
                TextInput::make('category')
                    ->required(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'processing' => 'Processing',
            'done' => 'Done',
            'failed' => 'Failed',
        ])
                    ->default('pending')
                    ->required(),
                TextInput::make('reject_reason'),
                TextInput::make('meta'),
                DateTimePicker::make('approved_at'),
                DateTimePicker::make('processed_at'),
            ]);
    }
}
