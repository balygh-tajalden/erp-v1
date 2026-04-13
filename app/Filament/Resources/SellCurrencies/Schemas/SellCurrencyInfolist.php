<?php

namespace App\Filament\Resources\SellCurrencies\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Helpers\AccountingHelper;

class SellCurrencyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل عملية البيع')
                    ->schema([
                        TextEntry::make('viewDetails.الرقم')
                            ->label('رقم السند'),
                        TextEntry::make('viewDetails.التاريخ')
                            ->label('التاريخ'),
                        TextEntry::make('viewDetails.اسم الحساب')
                            ->label('الحساب'),
                        TextEntry::make('viewDetails.المبلغ')
                            ->label('مبلغ البيع')
                            ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state)),
                        TextEntry::make('viewDetails.العملة المباعة')
                            ->label('العملة المباعة'),
                        TextEntry::make('viewDetails.سعر البيع')
                            ->label('سعر الصرف')
                            ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state)),
                        TextEntry::make('viewDetails.المبلغ المستلم')
                            ->label('المقابل المستلم')
                            ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state)),
                        TextEntry::make('viewDetails.العملة المستلمة')
                            ->label('العملة المستلمة'),
                        TextEntry::make('viewDetails.حساب القيمة')
                            ->label('صندوق/حساب الاستلام'),
                        TextEntry::make('viewDetails.طريقة البيع')
                            ->label('طريقة القبض'),
                        TextEntry::make('viewDetails.الملاحظات')
                            ->label('البيان/الملاحظات')
                            ->columnSpanFull(),
                        TextEntry::make('viewDetails.المستخدم')
                            ->label('مدخل العملية'),
                        TextEntry::make('viewDetails.الفرع')
                            ->label('الفرع'),
                    ])->columns(4)->columnSpanFull()
            ]);
    }
}
