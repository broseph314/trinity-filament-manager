<?php

namespace App\Filament\Resources\Trinity\Auth\Accounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('reg_mail')
                    ->searchable(),
                TextColumn::make('joindate')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_ip')
                    ->searchable(),
                TextColumn::make('last_attempt_ip')
                    ->searchable(),
                TextColumn::make('failed_logins')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('locked')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_login')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('online')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expansion')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('os')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
            ]);
    }
}
