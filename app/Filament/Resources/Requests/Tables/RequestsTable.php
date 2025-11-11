<?php

namespace App\Filament\Resources\Requests\Tables;

use App\Jobs\ProcessApprovedRequests;
use App\Models\Request;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->searchable(),
                TextColumn::make('user.name')->label('User')->sortable()->searchable(),
                TextColumn::make('character_name')->label('Character')->sortable()->searchable(),
                TextColumn::make('category')
                    ->badge()
                    ->colors(['primary'])
                    ->sortable(),
                TextColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info'    => 'approved',
                        'gray'    => 'processing',
                        'success' => 'done',
                        'danger'  => 'rejected',
                        'error'   => 'failed',
                    ])
                    ->badge()
                    ->sortable(),
                TextColumn::make('lines_count')->label('Lines')->toggleable(true,true)->sortable(),
                TextColumn::make('done_lines_count')->label('Done')->toggleable(true,true)->sortable(),
                TextColumn::make('error_lines_count')->label('Errors')->toggleable(true,true)->sortable(),
                TextColumn::make('created_at')->dateTime()->since()->toggleable(true,true)->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'    => 'Pending',
                        'approved'   => 'Approved',
                        'processing' => 'Processing',
                        'done'       => 'Done',
                        'rejected'   => 'Rejected',
                        'failed'     => 'Failed',
                    ]),
                SelectFilter::make('category')
                    ->options([
                        'items' => 'Items',
                        'money' => 'Money',
                        'level' => 'Level',
                        'pack'  => 'Pack',
                        'perm'  => 'Permission',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color(Color::Green)
                    ->visible(fn (Request $record) => $record->status === 'pending')
                    ->schema([
                        Textarea::make('note')->label('Admin note')->rows(3),
                    ])
                    ->action(function (Request $record, array $data) {
                        $record->markApproved($data['note'] ?? null);
                        dispatch(new ProcessApprovedRequests()); // kicks the worker
                    })
                    ->requiresConfirmation(),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color(Color::Red)
                    ->visible(fn (Request $record) => $record->status === 'pending')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason')->required()->rows(3),
                    ])
                    ->action(fn (Request $record, array $data) =>
                    $record->markRejected($data['reason'])
                    )
                    ->requiresConfirmation(),

                Action::make('processNow')
                    ->label('Process now')
                    ->icon('heroicon-o-play')
                    ->color(Color::Indigo)
                    ->visible(fn (Request $record) => in_array($record->status, ['approved','processing','failed']))
                    ->action(fn () => dispatch(new ProcessApprovedRequests())),

                Action::make('requeueFailed')
                    ->label('Requeue failed lines')
                    ->icon('heroicon-o-arrow-path')
                    ->color(Color::Orange)
                    ->visible(fn (Request $record) => $record->error_lines_count > 0)
                    ->requiresConfirmation()
                    ->action(function (Request $record) {
                        $record->lines()->where('status','error')->update([
                            'status' => 'pending',
                            'error_text' => null,
                        ]);
                        // Leave request in 'processing' so the worker picks it back up
                        if (!in_array($record->status, ['approved','processing'])) {
                            $record->update(['status' => 'approved']);
                        }
                        dispatch(new ProcessApprovedRequests());
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('bulkApprove')
                    ->label('Approve selected')
                    ->icon('heroicon-o-check-circle')
                    ->color(Color::Green)
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        /** @var \Illuminate\Support\Collection $records */
                        $records->each->markApproved();
                        dispatch(new ProcessApprovedRequests());
                    }),

                BulkAction::make('bulkReject')
                    ->label('Reject selected')
                    ->icon('heroicon-o-x-circle')
                    ->color(Color::Red)
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason for rejection')->required(),
                    ])
                    ->action(function ($records, array $data) {
                        $records->each->markRejected($data['reason']);
                    }),
            ]);
    }
}
