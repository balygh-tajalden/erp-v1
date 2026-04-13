<?php

namespace App\Filament\Resources\CurrencyPrices\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Services\AccountingService;
use App\Filament\Resources\CurrencyPrices\CurrencyPriceResource;

class CurrencyPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Currency Conversion')
                    ->description('اختر العملات وحدد أسعار التحويل  ')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('SourceCurrencyID')
                                    ->label('من عملة')
                                    ->options(function ($get) {
                                        $options = CurrencyPriceResource::getCurrencyOptions();
                                        $target = $get('TargetCurrencyID');
                                        if ($target) {
                                            unset($options[$target]);
                                        }
                                        return $options;
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required(),
                                Select::make('TargetCurrencyID')
                                    ->label('إلى عملة')
                                    ->options(function ($get) {
                                        $options = CurrencyPriceResource::getCurrencyOptions();
                                        $source = $get('SourceCurrencyID');
                                        if ($source) {
                                            unset($options[$source]);
                                        }
                                        return $options;
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->different('SourceCurrencyID')
                                    ->validationMessages([
                                        'different' => 'يجب أن تكون العملة الهدف مختلفة عن العملة المصدر.',
                                    ]),
                                TextInput::make('ExchangePrice')
                                    ->label('سعر التحويل')
                                    ->numeric()
                                    ->required()
                                    ->gt(0)
                                    ->step(0.000001),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Selling Prices')
                    ->schema([
                        TextInput::make('SellPrice')
                            ->label('سعر البيع')
                            ->numeric()
                            ->required()
                            ->gt(0)
                            ->step(0.000001),
                        TextInput::make('MinSellPrice')
                            ->label('أدنى سعر بيع')
                            ->numeric()
                            ->lte('SellPrice')
                            ->step(0.000001),
                        TextInput::make('MaxSellPrice')
                            ->label('أعلى سعر بيع')
                            ->numeric()
                            ->gte('SellPrice')
                            ->step(0.000001),
                    ])->columnSpan(1),
                Section::make('Buying Prices')
                    ->schema([
                        TextInput::make('BuyPrice')
                            ->label('سعر الشراء')
                            ->numeric()
                            ->required()
                            ->gt(0)
                            ->step(0.000001),
                        TextInput::make('MinBuyPrice')
                            ->label('أدنى سعر شراء')
                            ->numeric()
                            ->lte('BuyPrice')
                            ->step(0.000001),
                        TextInput::make('MaxBuyPrice')
                            ->label('أعلى سعر شراء')
                            ->numeric()
                            ->gte('BuyPrice')
                            ->step(0.000001),
                    ])->columnSpan(1),
                Textarea::make('Notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
