<?php

namespace App\Filament\Resources\AccountingSessions\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;

class AccountingSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('legacy_id')
                    ->label('ID الجلسة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.UserName')
                    ->label('المستخدم')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('branch.BranchName')
                    ->label('الفرع')
                    ->sortable(),

                TextColumn::make('StartTime')
                    ->label('وقت البدء')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('EndTime')
                    ->label('وقت الانتهاء')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('MachineName')
                    ->label('اسم الجهاز')
                    ->searchable(),

                TextColumn::make('ip_address')
                    ->label('IP المتصل')
                    ->searchable(),

                IconColumn::make('IsEnded')
                    ->label('منتهية')
                    ->boolean(),

                TextColumn::make('OSVersion')
                    ->label('إصدار النظام')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('legacy_id', 'desc')
            ->recordActions([
                ViewAction::make()->iconButton(),
                Action::make('close_session')
                    ->label('إغلاق الجلسة')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ! $record->IsEnded)
                    ->action(fn ($record) => $record->close()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
