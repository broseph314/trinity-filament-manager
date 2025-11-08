<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Trinity\Characters\Characters\CharacterResource;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('two_factor_confirmed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                RepeatableEntry::make('characters')
                    ->label('Characters')
                    ->columns(3)
                    ->visible(fn ($record) => filled($record->trinity_account_id))
                    ->schema([
                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('level')->label('Lvl'),
                        ViewAction::make('name')
                            ->label('View')
                            ->url(fn ($record) => CharacterResource::getUrl('view', ['record' => $record])) // or $char->getKey()
                            ->openUrlInNewTab(),
                    ])
            ]);
    }
}
