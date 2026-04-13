<?php

namespace App\Filament\Resources\AccountWallets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AccountWalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account.AccountName')
                    ->label('الحساب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('WalletAddress')
                    ->label('العنوان')
                    ->copyable()
                    ->fontFamily('mono')
                    ->searchable(),
                
                TextColumn::make('Network')
                    ->label('الشبكة')
                    ->badge(),

                TextColumn::make('Label')
                    ->label('المسمى')
                    ->searchable(),

                IconColumn::make('IsActive')
                    ->label('الحالة')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('Network')
                    ->label('الشبكة')
                    ->options([
                        'USDT-BEP20' => 'USDT (BEP20)',
                        'USDT-ERC20' => 'USDT (ERC20)',
                        'USDT-TRC20' => 'USDT (TRC20)',
                        'BTC' => 'Bitcoin',
                        'ETH' => 'Ethereum',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
