<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Trinity\Auth\Accounts\AccountResource;
use App\Filament\Resources\Trinity\Characters\Characters\CharacterResource;
use App\Models\Trinity\Characters\Character;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Auth\Pages\EditProfile;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;


class Profile extends Page implements HasInfolists, HasTable
{
    use InteractsWithInfolists, InteractsWithTable;

    protected string $view = 'filament.pages.profile';
    protected static string|null|\BackedEnum $navigationIcon = Heroicon::UserCircle;

    public User $user;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $this->user = auth()->user()
            ->load([
                'trinityLink',
                // if you DON’T use a global scope, eager-load only their characters here:
                // 'characters' => fn ($q) => $q->where('account', auth()->user()?->trinity_account_id),
                'characters',
                'roles',
            ]);
    }

    /** Header actions (top-right) */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('editProfile')
                ->label('Edit profile')
                ->icon('heroicon-m-pencil-square'),

            Action::make('logoutOthers')
                ->label('Log out other sessions')
                ->icon('heroicon-m-arrow-right-on-rectangle')
                ->requiresConfirmation()
                ->action(fn () => request()->user()->logoutOtherDevices(request()->user()->email)),
        ];
    }

    /** Infolist for the profile header card */
    public function accountInfo(Schema $infolist): Schema
    {
        return $infolist
            ->state($this->user)
            ->schema([
                Section::make('Account')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Name')->weight('bold'),
                        TextEntry::make('email')->label('Email'),
                        TextEntry::make('created_at')->dateTime(),
                        TextEntry::make('updated_at')->dateTime(),
                    ])
            ]);
    }

    /** Characters table (rich list with search/sort) */
    public function getTableQuery(): Builder
    {
        // If you already added the global “own-or-any” scope, you can just return Character::query()
        $query = Character::query();

        if (! auth()->user()->can('characters.view.any')) {
            $query->where('account', auth()->user()->trinity_account_id);
        }

        return $query;
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('level')->sortable(),
            TextColumn::make('race'),
            TextColumn::make('class'),
            TextColumn::make('map'),
        ];
    }

    public function getTableActions(): array
    {
        return [
            ViewAction::make()
                ->url(fn ($record) => CharacterResource::getUrl('view', ['record' => $record])),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
