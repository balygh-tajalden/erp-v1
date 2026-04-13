<?php

namespace App\Filament\Resources\ServiceRequests\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class ServiceRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('CreatedDate')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('account.AccountName')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('provider.Name')
                    ->label('المزود')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('PhoneNumber')
                    ->label('الرقم')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('Amount')
                    ->label('المبلغ')
                    ->money('YR')
                    ->sortable(),

                TextColumn::make('Profit')
                    ->label('الربح')
                    ->money('YR')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('Status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Pending' => 'warning',
                        'Processing' => 'info',
                        'Success' => 'success',
                        'Failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('ReferenceNumber')
                    ->label('الرقم المرجعي')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('CreatedDate', 'desc')
            ->actions([])
            ->bulkActions([]);
    }
}
