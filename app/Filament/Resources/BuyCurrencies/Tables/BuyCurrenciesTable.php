<?php

namespace App\Filament\Resources\BuyCurrencies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use App\Helpers\AccountingHelper;
use App\Services\AccountingService;
use App\DTOs\Accounting\BuyCurrencyDTO;
use Illuminate\Database\Eloquent\Model;

class BuyCurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('viewDetails.الرقم')
                    ->label('رقم السند')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.التاريخ')
                    ->label('التاريخ')
                    ->date()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.اسم الحساب')
                    ->label('الحساب')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.العملة المشتراة')
                    ->label('العملة المشتراة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.العملة المدفوعة')
                    ->label('العملة المدفوعة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.المبلغ')
                    ->label('المبلغ المشترى')
                    ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.سعر الشراء')
                    ->label('السعر')
                    ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.المبلغ المدفوع')
                    ->label('المبلغ المدفوع')
                    ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->action(fn(Model $record) => app(AccountingService::class)->deleteBuyCurrency($record)),
                EditAction::make()
                    ->iconButton()
                    ->using(fn(Model $record, array $data): Model => app(AccountingService::class)->updateBuyCurrency($record, BuyCurrencyDTO::fromArray($data))),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                ]),
            ]);
    }
}
