<?php

namespace App\Filament\Resources\CurrencyPrices\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CurrencyPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sourceCurrency.CurrencyName')
                    ->label('من عملة')
                    ->sortable(),
                TextColumn::make('targetCurrency.CurrencyName')
                    ->label('إلى عملة')
                    ->sortable(),
                TextColumn::make('ExchangePrice')
                    ->label('سعر التحويل')
                    ->formatStateUsing(fn($state) => (float) $state)
                    ->sortable(),
                TextColumn::make('BuyPrice')
                    ->label('سعر الشراء')
                    ->formatStateUsing(fn($state) => (float) $state)
                    ->sortable(),
                TextColumn::make('MinBuyPrice')
                    ->label('أدنى شراء')
                    ->formatStateUsing(fn($state) => (float) $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('MaxBuyPrice')
                    ->label('أعلى شراء')
                    ->formatStateUsing(fn($state) => (float) $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('SellPrice')
                    ->label('سعر البيع')
                    ->formatStateUsing(fn($state) => (float) $state)
                    ->sortable(),
                TextColumn::make('MinSellPrice')
                    ->label('أدنى بيع')
                    ->formatStateUsing(fn($state) => (float) $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('MaxSellPrice')
                    ->label('أعلى بيع')
                    ->formatStateUsing(fn($state) => (float) $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('CreatedDate')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton(),
                EditAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
