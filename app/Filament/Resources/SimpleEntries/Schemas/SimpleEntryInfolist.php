<?php

namespace App\Filament\Resources\SimpleEntries\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;

class SimpleEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)

            ->components([

                TextEntry::make('TheNumber')
                            ->label('رقم السند'),
                        TextEntry::make('ReferenceNumber')
                            ->label('رقم المرجع'),
                        TextEntry::make('TheDate')
                            ->label('التاريخ')
                            ->date('Y-m-d'),
                        TextEntry::make('Notes')
                            ->label('ملاحظات'),
                            TextEntry::make('fromAccount.AccountName')
                            ->label('من حساب'),
                        TextEntry::make('toAccount.AccountName')
                            ->label('الى حساب'),
                        TextEntry::make('Amount')
                            ->label('المبلغ')
                            ->numeric(decimalPlaces: 2),
                        TextEntry::make('currency.CurrencyName')
                            ->label('العملة'),
                             TextEntry::make('user.UserName')
                            ->label('المستخدم'),
                        TextEntry::make('branch.BranchName')
                            ->label('الفرع'),
            ]);
    }
}
