<?php

namespace App\Filament\Resources\Requests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;

class RequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request Overview')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('user.name')
                                ->label('User')
                                ->icon('heroicon-o-user')
                                ->color('gray'),

                            TextEntry::make('character_name')
                                ->label('Character')
                                ->icon('heroicon-o-user-circle')
                                ->color('gray'),

                            TextEntry::make('category')
                                ->label('Category')
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'items' => 'info',
                                    'money' => 'success',
                                    'level' => 'warning',
                                    'pack'  => 'purple',
                                    'perm'  => 'gray',
                                    default => 'gray',
                                }),
                        ]),
                    ]),

                Section::make('Status & Actions')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'pending'    => Color::Amber,
                                    'approved'   => Color::Sky,
                                    'processing' => Color::Gray,
                                    'done'       => Color::Green,
                                    'rejected'   => Color::Rose,
                                    'failed'     => Color::Red,
                                    default      => Color::Slate,
                                })
                                ->icon(fn ($state) => match ($state) {
                                    'pending'    => 'heroicon-o-clock',
                                    'approved'   => 'heroicon-o-check-badge',
                                    'processing' => 'heroicon-o-arrow-path',
                                    'done'       => 'heroicon-o-check-circle',
                                    'rejected'   => 'heroicon-o-x-circle',
                                    'failed'     => 'heroicon-o-exclamation-triangle',
                                    default      => 'heroicon-o-question-mark-circle',
                                }),

                            TextEntry::make('reject_reason')
                                ->label('Rejection Reason')
                                ->placeholder('-')
                                ->color('gray')
                                ->icon('heroicon-o-x-mark'),

                            TextEntry::make('approved_at')
                                ->label('Approved At')
                                ->dateTime()
                                ->placeholder('-'),
                        ]),
                    ]),

                Section::make('Timestamps')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('processed_at')
                                ->label('Processed')
                                ->dateTime()
                                ->placeholder('-'),

                            TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime(),

                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->dateTime(),
                        ]),
                    ]),
            ]);
    }
}
