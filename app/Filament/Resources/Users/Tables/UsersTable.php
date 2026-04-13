<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('UserName')
                    ->label('اسم المستخدم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('userGroup.GroupName')
                    ->label('المجموعة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('Phone')
                    ->label('رقم الهاتف')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('branch.BranchName')
                    ->label('الفرع')
                    ->sortable(),
                IconColumn::make('IsActive')
                    ->label('الحالة')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('LastLoginDate')
                    ->label('آخر دخول')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('CreatedDate')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
               
                ]),
            ]);
    }
}
