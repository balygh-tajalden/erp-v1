<?php

namespace App\Filament\Resources\Currencies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
    use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ID')
                    ->label('No')
                    ->sortable(), 
                TextColumn::make('CurrencyName')
                    ->label('اسم العملة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('EnglishCode')
                    ->label('Code')
                    ->searchable(),
                IconColumn::make('IsDefault')
                    ->label('Default')
                    ->boolean(),
                TextColumn::make('CreatedBy')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ModifiedBy')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
