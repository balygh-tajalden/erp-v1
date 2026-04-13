<?php

namespace App\Filament\Resources\ClientsSMS\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;



class ClientsSMSTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ClientID')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('ClientName')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Username')
                    ->label('اسم المستخدم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('SMSBalance')
                    ->label('الرصيد')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('IsActive')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('MaxDevices')
                    ->label('أجهزة')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                Action::make('add_package')
                    ->label('إضافة باقة')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('MessagesCount')
                            ->label('عدد الرسائل')
                            ->required()
                            ->numeric(),
                        TextInput::make('Price')
                            ->label('القيمة (السعر)')
                            ->numeric(),
                        Textarea::make('Notes')
                            ->label('ملاحظات')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, \App\Models\ClientsSMS $record) {
                        // 1. Log the recharge
                        \App\Models\SMSRecharge::create([
                            'ClientID' => $record->ClientID,
                            'MessagesCount' => $data['MessagesCount'],
                            'Price' => $data['Price'] ?? 0,
                            'Notes' => $data['Notes'] ?? null,
                        ]);
                        // 2. Increment balance
                        $record->increment('SMSBalance', $data['MessagesCount']);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
