<?php

namespace App\Filament\Resources\Trinity\Auth\Accounts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('username'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('reg_mail'),
                TextEntry::make('joindate')
                    ->dateTime(),
                TextEntry::make('last_ip'),
                TextEntry::make('last_attempt_ip'),
                TextEntry::make('failed_logins')
                    ->numeric(),
                TextEntry::make('locked')
                    ->numeric(),
                TextEntry::make('last_login')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('online')
                    ->numeric(),
                TextEntry::make('locale')
                    ->numeric(),
                TextEntry::make('os'),
            ]);
    }
}
