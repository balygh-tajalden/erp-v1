<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([ 
                TextEntry::make('AccountNumber')
                    ->label('رقم الحساب'),
                TextEntry::make('AccountName')
                    ->label('اسم الحساب'),
                TextEntry::make('parent.AccountName')
                    ->label('الحساب الأب')
                    ->placeholder('-'),
                TextEntry::make('AccountTypeID')
                    ->label('نوع الحساب')
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        1 => 'رئيسي',
                        2 => 'فرعي',
                        default => (string)$state,
                    }),
                TextEntry::make('AccountReference')
                    ->label('الحساب الختامي')
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        '1' => 'الميزانية العمومية',
                        '2' => 'الأرباح والخسائر',
                        default => '-',
                    }),
                TextEntry::make('CreatedDate')
                    ->label('تاريخ الإضافة')
                    ->dateTime(),
                IconEntry::make('deleted_at')
                    ->label('محذوف')
                    ->boolean()
                    ->visible(fn($record) => $record?->trashed()),
            ]);
    }
}
