<?php

namespace App\Filament\Resources\Trinity\Auth\Accounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->required(),
                TextInput::make('session_key_auth'),
                TextInput::make('session_key_bnet'),
                TextInput::make('totp_secret'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('reg_mail')
                    ->required(),
                DateTimePicker::make('joindate')
                    ->required(),
                TextInput::make('last_ip')
                    ->required()
                    ->default('127.0.0.1'),
                TextInput::make('last_attempt_ip')
                    ->required()
                    ->default('127.0.0.1'),
                TextInput::make('failed_logins')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('locked')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('lock_country')
                    ->required()
                    ->default('00'),
                DateTimePicker::make('last_login'),
                TextInput::make('online')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('expansion')
                    ->required()
                    ->numeric()
                    ->default(2),
                TextInput::make('mutetime')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('mutereason')
                    ->required(),
                TextInput::make('muteby')
                    ->required(),
                TextInput::make('locale')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('os')
                    ->required(),
                TextInput::make('timezone_offset')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('recruiter')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
