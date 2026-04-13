<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use App\Models\Account;
use App\Filament\Resources\Accounts\AccountResource;
use Filament\Schemas\Components\Utilities\Get;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['lg' => 3])
            ->components([
                Select::make('AccountTypeID')
                    ->label('نوع الحساب')
                    ->options([
                        1 => 'رئيسي',
                        2 => 'فرعي',
                    ])
                    ->required()
                    ->default('2')
                    ->live(),

                Select::make('FatherNumber')
                    ->label('الحساب الأب')
                    ->options(fn(Get $get): array => AccountResource::getParentAccountOptions($get('AccountTypeID')))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $set('AccountNumber', Account::generateNextChildNumber($state));
                    }),

                TextInput::make('AccountName')
                    ->label('اسم الحساب')
                    ->required()
                    ->maxLength(500)
                    ->unique('tblAccounts', 'AccountName', ignoreRecord: true),

                TextInput::make('AccountNumber')
                    ->label('رقم الحساب')
                    ->numeric()
                    ->required()
                    ->unique('tblAccounts', 'AccountNumber', ignoreRecord: true),
                Select::make('AccountReference')
                    ->label('الحساب الختامي')
                    ->options([
                        '1' => 'الميزانية العمومية',
                        '2' => 'حساب الأرباح والخسائر',
                    ])
                    ->default('1'),
                Select::make('BranchID')
                    ->label('الفرع')
                    ->options(AccountResource::getBranchOptions())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default('2'),
                TextInput::make('whatsapp_phone')
                    ->label('رقم واتساب العميل')
                    ->placeholder('مثال: 967771234567')
                    ->helperText('اختياري - سيتم تفعيل إشعارات الواتساب تلقائياً')
                    ->regex('/^[0-9]+$/')
                    ->minLength(9)
                    ->maxLength(15)
                    ->dehydrated(false), 

            ]);
    }
}
