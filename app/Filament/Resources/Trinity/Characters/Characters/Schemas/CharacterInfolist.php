<?php

namespace App\Filament\Resources\Trinity\Characters\Characters\Schemas;

use App\Filament\Resources\Trinity\Auth\Accounts\AccountResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CharacterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Identity')
                ->columns(4)
                ->schema([
                    TextEntry::make('guid')
                        ->label('GUID')
                        ->copyable()
                        ->copyMessage('GUID copied')
                        ->columnSpan(1),

                    TextEntry::make('name')
                        ->label('Name')
                        ->weight('semibold')
                        ->columnSpan(1),

                    // Link to the Account View page in Filament
                    TextEntry::make('account')
                        ->label('Account')
                        ->url(fn ($record) => AccountResource::getUrl('view', ['record' => $record->account]))
                        ->openUrlInNewTab()
                        ->columnSpan(1),

                    Grid::make(3)->schema([
                        TextEntry::make('race_name')->label('Race')->badge()->color('gray'),
                        TextEntry::make('class_name')->label('Class')->badge()->color('info'),
                        TextEntry::make('gender_name')->label('Gender')->badge()->color('gray'),
                    ])->columnSpan(3),
                ]),

            Section::make('Status')
                ->columns(4)
                ->schema([
                    TextEntry::make('level')->label('Level')->badge()->color('success'),
                    TextEntry::make('xp')->label('XP')->numeric(),
                    TextEntry::make('money_human')->label('Gold')->icon('heroicon-m-banknotes'),
                    TextEntry::make('online')
                        ->label('Online')
                        ->formatStateUsing(fn ($state) => $state ? 'Online' : 'Offline')
                        ->badge()
                        ->color(fn ($state) => $state ? 'success' : 'gray'),

                    TextEntry::make('totaltime_human')->label('Time Played'),
                    TextEntry::make('logout_time_dt')->label('Last Logout')->dateTime()->since(),
                ]),

            Section::make('Location')
                ->columns(4)
                ->schema([
                    TextEntry::make('map')->label('Map ID')->placeholder('-'),
                    TextEntry::make('zone')->label('Zone ID')->placeholder('-'),
                    TextEntry::make('position_x')->label('X')->numeric(1)->placeholder('-'),
                    TextEntry::make('position_y')->label('Y')->numeric(1)->placeholder('-'),
                    TextEntry::make('position_z')->label('Z')->numeric(1)->placeholder('-'),
                    TextEntry::make('orientation')->label('Facing')->numeric(2)->placeholder('-'),
                ]),

            Section::make('Attributes & Resources')
                ->columns(6)
                ->collapsed() // tuck away unless needed
                ->schema([
                    TextEntry::make('health')->label('Health')->numeric(),
                    TextEntry::make('power1')->label('Power 1')->numeric(),
                    TextEntry::make('power2')->label('Power 2')->numeric(),
                    TextEntry::make('power3')->label('Power 3')->numeric(),
                    TextEntry::make('power4')->label('Power 4')->numeric(),
                    TextEntry::make('power5')->label('Power 5')->numeric(),

                    TextEntry::make('totalHonorPoints')->label('Honor (Total)')->numeric(),
                    TextEntry::make('todayHonorPoints')->label('Honor (Today)')->numeric(),
                    TextEntry::make('yesterdayHonorPoints')->label('Honor (Yesterday)')->numeric(),
                    TextEntry::make('arenaPoints')->label('Arena Points')->numeric(),
                    TextEntry::make('totalKills')->label('Kills (Total)')->numeric(),
                    TextEntry::make('todayKills')->label('Kills (Today)')->numeric(),
                ]),

            Section::make('Cosmetics')
                ->columns(6)
                ->collapsed()
                ->schema([
                    TextEntry::make('skin')->numeric(),
                    TextEntry::make('face')->numeric(),
                    TextEntry::make('hairStyle')->label('Hair Style')->numeric(),
                    TextEntry::make('hairColor')->label('Hair Color')->numeric(),
                    TextEntry::make('facialStyle')->label('Facial Style')->numeric(),
                    TextEntry::make('chosenTitle')->label('Title')->numeric(),
                ]),

            Section::make('Account Flags & Technical')
                ->columns(6)
                ->collapsed()
                ->schema([
                    TextEntry::make('bankSlots')->label('Bank Slots')->numeric(),
                    TextEntry::make('restState')->label('Rest State')->numeric(),
                    TextEntry::make('rest_bonus')->label('Rest Bonus')->numeric(),
                    TextEntry::make('latency')->label('Latency (ms)')->numeric(),
                    TextEntry::make('playerFlags')->label('Player Flags')->numeric(),
                    TextEntry::make('extra_flags')->label('Extra Flags')->numeric(),

                    TextEntry::make('at_login')->label('At Login Flags')->numeric(),
                    TextEntry::make('is_logout_resting')->label('Logout Resting')->formatStateUsing(fn ($s) => $s ? 'Yes' : 'No')->badge()->color(fn ($s) => $s ? 'info' : 'gray'),
                    TextEntry::make('cinematic')->label('Cinematic Watched')->numeric(),

                    TextEntry::make('leveltime')->label('Time @ Level (s)')->numeric(),
                    TextEntry::make('logout_time')->label('Logout (raw)')->numeric(),
                    TextEntry::make('knownCurrencies')->label('Known Currencies')->numeric(),
                ]),

            Section::make('Deletion Info')
                ->columns(3)
                ->collapsed()
                ->schema([
                    TextEntry::make('deleteInfos_Account')->label('Deleted By Account')->placeholder('-'),
                    TextEntry::make('deleteInfos_Name')->label('Deleted Name')->placeholder('-'),
                    TextEntry::make('deleteDate')->label('Delete Date (raw)')->placeholder('-'),
                ]),

            Section::make('Large Fields')
                ->collapsed()
                ->schema([
                    TextEntry::make('taximask')->label('Taxi Mask')->columnSpanFull()->placeholder('-'),
                    TextEntry::make('taxi_path')->label('Taxi Path')->columnSpanFull()->placeholder('-'),
                    TextEntry::make('exploredZones')->label('Explored Zones')->columnSpanFull()->placeholder('-'),
                    TextEntry::make('equipmentCache')->label('Equipment Cache')->columnSpanFull()->placeholder('-'),
                    TextEntry::make('knownTitles')->label('Known Titles')->columnSpanFull()->placeholder('-'),
                ]),
        ]);
    }
}
