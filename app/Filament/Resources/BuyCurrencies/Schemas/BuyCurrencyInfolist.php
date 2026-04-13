<?php

namespace App\Filament\Resources\BuyCurrencies\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Helpers\AccountingHelper;

class BuyCurrencyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل عملية الشراء')
                    ->schema([
                        TextEntry::make('viewDetails.الرقم')
                            ->label('رقم السند'),
                        TextEntry::make('viewDetails.التاريخ')
                            ->label('التاريخ'),
                        TextEntry::make('viewDetails.اسم الحساب')
                            ->label('الحساب'),
                        TextEntry::make('viewDetails.المبلغ')
                            ->label('مبلغ الشراء')
                            ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state)),
                        TextEntry::make('viewDetails.العملة المشتراة')
                            ->label('العملة المشتراة'),
                        TextEntry::make('viewDetails.سعر الشراء')
                            ->label('سعر الصرف')
                            ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state)),
                        TextEntry::make('viewDetails.المبلغ المدفوع')
                            ->label('المقابل المدفوع')
                            ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state)),
                        TextEntry::make('viewDetails.العملة المدفوعة')
                            ->label('العملة المدفوعة'),
                        TextEntry::make('viewDetails.حساب الدفع')
                            ->label('صندوق/حساب الدفع'),
                        TextEntry::make('viewDetails.العمولة')
                            ->label('العمولة')
                            ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state)),
                        TextEntry::make('viewDetails.عملة العمولة')
                            ->label('عملة العمولة'),
                        TextEntry::make('viewDetails.طريقة الشراء')
                            ->label('طريقة الدفع'),
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
