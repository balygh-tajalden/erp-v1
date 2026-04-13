<?php

namespace App\Filament\Resources\WalletTransactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WalletTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('WalletAddress')
                    ->required(),
                TextInput::make('TransactionHash')
                    ->required(),
                TextInput::make('LogIndex')
                    ->numeric(),
                TextInput::make('BlockNumber')
                    ->numeric(),
                DateTimePicker::make('BlockTimestamp')
                    ->required(),
                TextInput::make('FromAddress')
                    ->required(),
                TextInput::make('ToAddress')
                    ->required(),
                TextInput::make('Amount')
                    ->required()
                    ->numeric(),
                TextInput::make('TokenAddress'),
                TextInput::make('TokenSymbol'),
                TextInput::make('TokenName'),
                Toggle::make('IsIngoing')
                    ->required(),
                TextInput::make('Chain')
                    ->required()
                    ->default('bsc'),
                TextInput::make('CurrencyID')
                    ->numeric(),
            ]);
    }
}
