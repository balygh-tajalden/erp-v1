<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Grid;


class JournalEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل القيد')
                    ->schema([
                        RepeatableEntry::make('viewDetails')
                            ->label('')
                            ->columns(7)
                            ->schema([
                                TextEntry::make('اسم الحساب')->label('اسم الحساب'),
                                TextEntry::make('المبلغ مدين')
                                    ->label('مبلغ مدين')
                                    ->numeric(4),
                                TextEntry::make('المبلغ دائن')
                                    ->label('مبلغ دائن')
                                    ->numeric(4),
                                TextEntry::make('العملة')->label('العملة'),
                                TextEntry::make('سعر التحويل')
                                    ->label('سعر الصرف')
                                    ->default(1),
                                TextEntry::make('المقابل')
                                    ->label('المقابل المحلي')
                                    ->numeric(4),
                                    TextEntry::make('ملاحظات')->label('البيان'),
                            ])
                    ])->columnSpanFull()
            ]);
    }
}
