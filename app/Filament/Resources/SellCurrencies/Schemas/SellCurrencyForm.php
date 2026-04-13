<?php

namespace App\Filament\Resources\SellCurrencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\SellCurrency;
use Filament\Forms\Components\Select;
use App\Models\Account;
use App\Services\AccountingService;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use App\Services\Accounting\ValidationService;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\SellCurrencies\SellCurrencyResource;

class SellCurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('Thenumber')
                                    ->label('رقم السند')
                                    ->required()
                                    ->columnStart([1])
                                    ->default(fn($get) => SellCurrencyResource::getDefaultDocumentNumber($get)),

                                Select::make('BranchID')
                                    ->label('الفرع')
                                    ->options(SellCurrencyResource::getBranchOptions())
                                    ->required()
                                    ->live()
                                    ->default(2)
                                    ->afterStateUpdated(fn($set, $get) => SellCurrencyResource::refreshDocumentNumber($set, $get, 'Thenumber')),

                                DateTimePicker::make('TheDate')
                                    ->label('التاريخ')
                                    ->required()
                                    ->columnStart([3])
                                    ->default(now())
                                    ->live()
                                    ->afterStateUpdated(fn($set, $get) => SellCurrencyResource::refreshDocumentNumber($set, $get, 'Thenumber')),
                                Select::make('PurchaseMethod')
                                    ->label('حساب / نقد')
                                    ->options([
                                        1 => 'نقد',
                                        2 => 'حساب',
                                    ])
                                    ->autofocus()
                                    ->default(2)
                                    ->live()
                                    ->columnStart([1])
                                    ->afterStateUpdated(fn($set) => $set('AccountID', null))
                                    ->columnSpan([1]),
                                Select::make('AccountID')
                                    ->label(fn($get) => $get('PurchaseMethod') === 1 ? 'اسم الصندوق' : 'اسم الحساب')
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $account = Account::find($state);
                                        $set('AccountNumber', $account?->AccountNumber);
                                        $set('FundAccountID', $state);
                                    })
                                    ->options(
                                        fn($get): array => $get('PurchaseMethod') === 1
                                            ? SellCurrencyResource::getCashBoxOptions()
                                            : SellCurrencyResource::getChildrenAccountOptions()
                                    ),
                                TextInput::make('AccountNumber')
                                    ->label('رقم الحساب')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set) {
                                        $account = Account::where('AccountNumber', $state)->first();
                                        $set('AccountID', $account?->ID);
                                        $set('FundAccountID', $account->ID);
                                    }),
                                Select::make('CurrencyID')
                                    ->label('العملة المباعة')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($get, $set) => SellCurrencyResource::refreshCurrencyPrice('sell', $get, $set))
                                    ->options(
                                        collect(SellCurrencyResource::getCurrencyOptions())
                                            ->forget(1) // Exclude Yemeni Rial (ID 1)
                                            ->toArray()
                                    ),
                                Select::make('ExchangeCurrencyID')
                                    ->label('العملة المستلمة')
                                    ->required()
                                    ->default(1) // Yemeni Rial (ID 1)
                                    ->live()
                                    ->afterStateUpdated(fn($get, $set) => SellCurrencyResource::refreshSellingPrice($get, $set))
                                    ->options(SellCurrencyResource::getCurrencyOptions()),

                                TextInput::make('Amount')
                                    ->label('المبلغ')
                                    ->numeric()
                                    ->required()
                                    ->lazy()
                                    ->afterStateUpdated(fn($get, $set) => SellCurrencyResource::refreshExchangeAmount($get, $set)),
                                TextInput::make('Price')
                                    ->label('سعر البيع')
                                    ->numeric()
                                    ->required()
                                    ->lazy()
                                    ->afterStateUpdated(fn($get, $set) => SellCurrencyResource::refreshExchangeAmount($get, $set)),
                                Select::make('FundAccountID')
                                    ->label('الحساب المستلم')
                                    ->required()
                                    ->searchable()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $account = Account::find($state);
                                        $set('FundAccountID', $state);
                                    })
                                    ->options(
                                        fn($get): array => $get('PurchaseMethod') === 1
                                            ? SellCurrencyResource::getCashBoxOptions()
                                            : SellCurrencyResource::getChildrenAccountOptions()
                                    ),
                                TextInput::make('ExchangeAmount')
                                    ->label('المبلغ المستلم')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('Notes')
                                    ->label('ملاحظات')
                                    ->columnSpan([2]),
                                TextInput::make('ReferenceNumber')
                                    ->label('رقم المرجع'),

                                Hidden::make('RowVersion')
                            ]),

                    ])
                    ->columnSpan([2]),



            ]);
    }
}
