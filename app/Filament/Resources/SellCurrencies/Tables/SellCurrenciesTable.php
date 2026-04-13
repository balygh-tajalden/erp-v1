<?php

namespace App\Filament\Resources\SellCurrencies\Tables;

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
use App\DTOs\Accounting\SellCurrencyDTO;
use Illuminate\Database\Eloquent\Model;


class SellCurrenciesTable
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

                TextColumn::make('viewDetails.العملة المباعة')
                    ->label('العملة المباعة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.العملة المستلمة')
                    ->label('العملة المستلمة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.المبلغ')
                    ->label('المبلغ المباع')
                    ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.سعر البيع')
                    ->label('السعر')
                    ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.المبلغ المستلم')
                    ->label('المبلغ المستلم')
                    ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('viewDetails.الملاحظات')
                    ->label('ملاحظات')
                    ->limit(50)
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
                    ->action(fn (Model $record) => app(AccountingService::class)->deleteSellCurrency($record)),
                EditAction::make()
                    ->iconButton()
                    ->using(fn(Model $record, array $data): Model => app(AccountingService::class)->updateSellCurrency($record, SellCurrencyDTO::fromArray($data))),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                ]),
                
            ]);
            
    }
}
