<?php

namespace App\Filament\Resources\Trinity\Auth\Accounts\Pages;

use App\Filament\Resources\Trinity\Auth\Accounts\AccountResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAccount extends EditRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
