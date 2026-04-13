<?php

namespace App\Filament\Resources\WalletTransactions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WalletTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([ 
                TextEntry::make('WalletAddress'),
                TextEntry::make('TransactionHash'),
                TextEntry::make('LogIndex')
                    ->numeric() 
                    ->placeholder('-'),
                TextEntry::make('BlockNumber')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('BlockTimestamp')
                    ->dateTime(),
                TextEntry::make('FromAddress'),
                TextEntry::make('ToAddress'),
                TextEntry::make('Amount')
                    ->numeric(),
                TextEntry::make('TokenAddress')
                    ->placeholder('-'),
                TextEntry::make('TokenSymbol')
                    ->placeholder('-'),
                TextEntry::make('TokenName')
                    ->placeholder('-'),
                IconEntry::make('IsIngoing')
                    ->boolean(),
                TextEntry::make('Chain'),
                TextEntry::make('CreatedDate')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('ModifiedDate')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('CurrencyID')
                    ->numeric()
                    ->placeholder('-'),
            ]);
    }
}
