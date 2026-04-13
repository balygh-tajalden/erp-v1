<?php

namespace App\Filament\Resources\SimpleEntries\Schemas;

use App\Services\AccountingService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use App\Models\Account;
use App\Filament\Resources\SimpleEntries\SimpleEntryResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;

class SimpleEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([4])
            ->components([
                DatePicker::make('TheDate')
                    ->label('التاريخ')
                    ->required()
                    ->columnStart([1])
                    ->default(now())
                    ->live()
                    ->afterStateUpdated(fn($set, $get) => SimpleEntryResource::refreshDocumentNumber($set, $get, 'TheNumber')),
                Select::make('BranchID')
                    ->label('الفرع')
                    ->options(SimpleEntryResource::getBranchOptions())
                    ->required()
                    ->live()
                    ->default(2),

                TextInput::make('TheNumber')
                    ->label('رقم السند')
                    ->required()
                    ->columnStart([3])
                    ->columnSpan([2])
                    ->default(fn($get) => SimpleEntryResource::getDefaultDocumentNumber($get)),
                TextInput::make('Amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->required()
                    ->autofocus()
                    ->columnStart(['1']),

                Select::make('CurrencyID')
                    ->label('العملة')
                    ->options(SimpleEntryResource::getCurrencyOptions('english_code'))
                    ->required()
                    ->default(1),

                Select::make('FromAccountID')
                    ->label('من حساب')
                    ->options(SimpleEntryResource::getChildrenAccountOptions())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn($state, $set) => SimpleEntryResource::refreshAccountNumber($state, $set, 'FromAccountNumber'))
                    ->required()
                    ->columnStart([1]),
                TextInput::make('FromAccountNumber')
                    ->label('رقم الحساب')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, $set) => SimpleEntryResource::refreshAccountID($state, $set, 'FromAccountID'))
                    ->dehydrated(false),
                Select::make('ToAccountID')
                    ->label('الى حساب')
                    ->options(SimpleEntryResource::getChildrenAccountOptions())
                    ->searchable()
                    ->preload()
                    ->different('FromAccountID')
                    ->live()
                    ->afterStateUpdated(fn($state, $set) => SimpleEntryResource::refreshAccountNumber($state, $set, 'ToAccountNumber'))
                    ->required(),
                TextInput::make('ToAccountNumber')
                    ->label('رقم الحساب')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, $set) => SimpleEntryResource::refreshAccountID($state, $set, 'ToAccountID'))
                    ->dehydrated(false),
                TextInput::make('Notes')
                    ->label('ملاحظات')
                    ->placeholder('بيان العملية...')
                    ->columnSpan(['default' => 2])
                    ->columnStart([1]),

                TextInput::make('ReferenceNumber')
                    ->label('رقم المرجع')
                    ->columnSpan(['default' => 1]),

                Hidden::make('RowVersion') // <-- لحفظ رقم النسخة للحماية من تضارب البيانات
            ]);
    }
}
