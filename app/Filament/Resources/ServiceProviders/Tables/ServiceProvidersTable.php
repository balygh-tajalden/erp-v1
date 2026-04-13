<?php

namespace App\Filament\Resources\ServiceProviders\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ServiceProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('Logo')
                    ->label('')
                    ->circular(),

                TextColumn::make('Name')
                    ->label('الشبكة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('Code')
                    ->label('كود الربط')
                    ->toggleable()
                    ->copyable(),

                TextColumn::make('Category')
                    ->label('الفئة')
                    ->badge()
                    ->color('info'),

                TextColumn::make('DefaultProfit')
                    ->label('الربح الافتراضي')
                    ->money('YR')
                    ->sortable(),

                ToggleColumn::make('IsActive')
                    ->label('الحالة')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
