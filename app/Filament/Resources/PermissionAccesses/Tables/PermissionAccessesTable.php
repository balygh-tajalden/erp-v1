<?php

namespace App\Filament\Resources\PermissionAccesses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\DocumentType;

class PermissionAccessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('TargetType')
                    ->label('الهدف')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'User' => 'مستخدم',
                        'Group' => 'مجموعة',
                        default => $state,
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('TargetID')
                    ->label('الاسم')
                    ->formatStateUsing(function ($record) {
                        if ($record->TargetType === 'User') {
                            return User::find($record->TargetID)?->UserName ?? $record->TargetID;
                        }
                        if ($record->TargetType === 'Group') {
                            return UserGroup::find($record->TargetID)?->GroupName ?? $record->TargetID;
                        }
                        return $record->TargetID;
                    })
                    ->searchable(),
                TextColumn::make('FormCode')
                    ->label('الشاشة')
                    ->formatStateUsing(fn ($state) => DocumentType::find($state)?->DocumentName ?? $state)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('PermissionValues')
                    ->label('الصلاحيات')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                TextColumn::make('CreatedDate')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
