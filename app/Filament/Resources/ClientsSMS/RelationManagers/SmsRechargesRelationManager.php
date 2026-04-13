<?php

namespace App\Filament\Resources\ClientsSMS\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;

class SmsRechargesRelationManager extends RelationManager
{
    protected static string $relationship = 'smsRecharges';
    protected static ?string $title = 'سجل شحن الباقات';
    protected static ?string $modelLabel = 'باقة / شحنة';

    public function schema(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('MessagesCount')
                    ->label('عدد الرسائل')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Price')
                    ->label('القيمة (السعر)')
                    ->numeric(),
                Forms\Components\Textarea::make('Notes')
                    ->label('ملاحظات')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('RechargeID')
            ->columns([
                Tables\Columns\TextColumn::make('MessagesCount')
                    ->label('العدد المضاف')
                    ->numeric()
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('Price')
                    ->label('السعر')
                    ->money('YER', true) // Default formatting
                    ->sortable(),
                Tables\Columns\TextColumn::make('Notes')
                    ->label('ملاحظات')
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الشحن')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // We won't use default CreateAction here as we want a specific Action in the Main Resource Table as requested.
                // However, we can keep it for manual correction.
                CreateAction::make()
                    ->label('إضافة يدوي للصلاحيات')
                    ->after(function ($livewire, $record) {
                        // In RelationManager, $record is the created Recharge, $livewire->ownerRecord is the Client
                        $client = $livewire->getOwnerRecord();
                        $client->increment('SMSBalance', $record->MessagesCount);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->after(function ($livewire, $record) {
                        // Deduct balance when deleted
                        $client = $livewire->getOwnerRecord();
                        $client->decrement('SMSBalance', $record->MessagesCount);
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }
}
