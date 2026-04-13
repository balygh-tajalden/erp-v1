<?php

namespace App\Filament\Resources\Accounts\Tables;

use App\Filament\Resources\Accounts\AccountResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Services\AccountingService;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DeleteAction;
use App\Models\Account;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('AccountNumber')
                    ->label('رقم الحساب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('AccountName')
                    ->label('اسم الحساب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.AccountName')
                    ->label('الحساب الأب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('AccountTypeID')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        1 => 'رئيسي',
                        2 => 'فرعي',
                        default => (string)$state,
                    }),
                TextColumn::make('AccountReference')
                    ->label('الحساب الختامي')
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        '1' => 'الميزانية العمومية',
                        '2' => 'الأرباح والخسائر',
                        default => '-',
                    }),
                TextColumn::make('branch.BranchName')
                    ->label('الفرع')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('CreatedDate')
                    ->label('تاريخ الإضافة')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('BranchID')
                    ->label('الفرع')
                    ->options(AccountResource::getBranchOptions()),
                SelectFilter::make('AccountTypeID')
                    ->label('النوع')
                    ->options([
                        1 => 'رئيسي',
                        2 => 'فرعي',
                    ]),
                SelectFilter::make('AccountReference')
                    ->label('الحساب الختامي')
                    ->options([
                        '1' => 'الميزانية العمومية',
                        '2' => 'الأرباح والخسائر',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                ->iconButton(),
                EditAction::make()
                ->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->disabled(fn (Account $record) => $record->getDeletionPreventionReason() !== null)
                    ->tooltip(fn (Account $record) => $record->getDeletionPreventionReason() ?? 'حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
