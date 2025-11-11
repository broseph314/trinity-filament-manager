<?php

namespace App\Filament\Resources\Requests\RelationManagers;

use App\Models\RequestLine;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('action')
                    ->required(),
                TextInput::make('params')
                    ->required(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'processing' => 'Processing', 'done' => 'Done', 'error' => 'Error'])
                    ->default('pending')
                    ->required(),
                TextInput::make('attempts')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('error_text'),
                DateTimePicker::make('processed_at'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('action')->badge()->colors(['primary']),
                TextColumn::make('params')
                    ->formatStateUsing(fn ($state) => json_encode($state, JSON_UNESCAPED_UNICODE))
                    ->tooltip(fn ($state) => json_encode($state, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))
                    ->limit(60),
                TextColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'gray'    => 'processing',
                        'success' => 'done',
                        'danger'  => 'error',
                    ])
                    ->badge()
                    ->sortable(),
                TextColumn::make('attempts')->sortable(),
                TextColumn::make('error_text')->limit(60)->toggleable(),
                TextColumn::make('processed_at')->dateTime()->since()->sortable(),
                TextColumn::make('created_at')->dateTime()->since()->sortable(),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color(Color::Indigo)
                    ->visible(fn (RequestLine $record) => in_array($record->status, ['pending','error']))
                    ->requiresConfirmation()
                    ->action(fn (RequestLine $record) => $record->update(['status'=>'pending','error_text'=>null])),

                Action::make('markDone')
                    ->label('Mark done')
                    ->icon('heroicon-o-check')
                    ->color(Color::Green)
                    ->visible(fn (RequestLine $record) => $record->status !== 'done')
                    ->requiresConfirmation()
                    ->action(fn (RequestLine $record) => $record->markDone()),
            ])
            ->headerActions([]) // no create lines from admin for now
            ->toolbarActions([
                BulkAction::make('bulkRetry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color(Color::Indigo)
                    ->action(fn ($records) => $records->each->update(['status'=>'pending','error_text'=>null])),
            ]);
    }
}
