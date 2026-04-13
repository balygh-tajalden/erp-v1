<?php

namespace App\Filament\Resources\ClientsSMS\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;


class DeviceLicensesRelationManager extends RelationManager
{
    protected static string $relationship = 'deviceLicenses';
    protected static ?string $title = 'أجهزة وتراخيص النظام';
    protected static ?string $modelLabel = 'ترخيص جهاز';

    public function schema(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('DeviceKey')
                    ->label('مفتاح التفعيل')
                    ->required()
                    ->maxLength(100),
                TextInput::make('DeviceName')
                    ->label('اسم الجهاز')
                    ->maxLength(500),
                TextInput::make('SystemName')
                    ->label('البرنامج/النظام')
                    ->required()
                    ->default('SMS_Gateway')
                    ->maxLength(100),
                Select::make('Status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                    ])
                    ->required()
                    ->default('approved'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('DeviceKey')
            ->columns([
                TextColumn::make('SystemName')->label('النظام'),
                TextColumn::make('DeviceName')->label('اسم الجهاز')->placeholder('-'),
                TextColumn::make('DeviceKey')->label('مفتاح الترخيص')->copyable(),
                TextColumn::make('Status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'approved' => 'مقبول',
                        'pending' => 'معلق',
                        'rejected' => 'مرفوض',
                        default => $state,
                    }),
                TextColumn::make('created_at')->label('تاريخ الطلب')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة جهاز يدوياً'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('موافقة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->Status === 'pending')
                    ->action(fn($record) => $record->update(['Status' => 'approved'])),
                Action::make('reject')
                    ->label(fn($record) => $record->Status === 'pending' ? 'رفض' : 'تعطيل')
                    ->icon(fn($record) => $record->Status === 'pending' ? 'heroicon-o-x-circle' : 'heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => in_array($record->Status, ['pending', 'approved']))
                    ->action(fn($record) => $record->update(['Status' => 'rejected'])),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
