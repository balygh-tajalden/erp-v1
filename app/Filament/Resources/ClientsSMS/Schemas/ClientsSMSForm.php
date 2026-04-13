<?php

namespace App\Filament\Resources\ClientsSMS\Schemas;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;



class ClientsSMSForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ClientName')
                    ->label('اسم العميل')
                    ->required()
                    ->maxLength(200),
                TextInput::make('Username')
                    ->label('اسم المستخدم')
                    ->required()
                    ->maxLength(50),
                TextInput::make('PasswordHash')
                    ->label('كلمة المرور')
                    ->password()
                    ->required(fn ($context) => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
                TextInput::make('DeviceName')
                    ->label('اسم الجهاز')
                    ->disabled()
                    ->visible(fn ($context) => $context === 'edit'),
                TextInput::make('SubscriptionKey')
                    ->label('مفتاح الترخيص')
                    ->disabled()
                    ->visible(fn ($context) => $context === 'edit'),

                TextInput::make('SMSBalance')
                    ->label('رصيد الرسائل')
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('ExpiryDate')
                    ->label('تاريخ الانتهاء')
                    ->required(),
                Toggle::make('IsActive')
                    ->label('نشط')
                    ->default(true)
                    ->visible(fn ($context) => $context === 'edit'),
                TextInput::make('MaxDevices')
                    ->label('أقصى عدد للأجهزة')
                    ->numeric()
                    ->default(1),
            ]);
    }
}
