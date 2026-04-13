<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\AccountingService;
use Illuminate\Support\HtmlString;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Header Section
            Section::make('Entry Header')
                ->description('بيانات القيد الأساسية')
                ->icon('heroicon-o-information-circle')
                ->columnSpanFull()
                ->schema([
                    Grid::make(4)->schema([
                        TextInput::make('TheNumber')
                            ->label('رقم القيد')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(fn($get) => JournalEntryResource::getDefaultDocumentNumber($get)),
                        DatePicker::make('TheDate')
                            ->label('التاريخ')
                            ->required()
                            ->default(now()),
                        Select::make('BranchID')
                            ->label('الفرع')
                            ->options(JournalEntryResource::getBranchOptions())
                            ->required()
                            ->default(2),
                    ]),

                    TextInput::make('Notes')
                        ->label('المنشأ / ملاحظات عامة')
                        ->placeholder('بيان القيد الرئيسي...')
                        ->columnSpanFull(),
                ]),

            // Entry Lines Section

            Section::make('')
                ->schema([
                    Repeater::make('entryLines')
                        ->label('تفاصيل خطوط القيد')
                        ->schema([
                            Grid::make(12)->schema([
                                Select::make('account_id')
                                    ->label('الحساب')
                                    ->options(JournalEntryResource::getChildrenAccountAndCashBoxOptions())
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state, Set $set) => static::refreshAccountNumber($state, $set))
                                    ->columnSpan(4),
                                TextInput::make('display_amount')
                                    ->label('المبلغ')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => static::syncAmount($get, $set))
                                    ->columnSpan(2),
                                Select::make('type')
                                    ->label('القيد')
                                    ->options(['debit' => 'من حساب - مدين', 'credit' => 'الى حساب - دائن'])
                                    ->live()
                                    ->afterStateUpdated(fn(Get $get, Set $set) => static::syncAmount($get, $set))
                                    ->default('debit')
                                    ->columnSpan(2),
                                Select::make('currency_id')
                                    ->label('العملة')
                                    ->options(JournalEntryResource::getCurrencyOptions('english_code'))
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(fn(Get $get, Set $set) => static::updateExchangeRate($get, $set))
                                    ->columnSpan(2),
                                TextInput::make('exchange_rate')
                                    ->label('سعر الصرف')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => static::syncAmount($get, $set))
                                    ->columnSpan(2),
                                TextInput::make('line_notes')->label('ملاحظات الحقل')->columnSpanFull(),

                                // Hidden Fields
                                TextInput::make('amount')->hidden()->dehydrated(true),
                                TextInput::make('mc_amount')->hidden()->dehydrated(true),
                                TextInput::make('account_number')->hidden()->dehydrated(false),
                            ]),
                        ])
                        ->columns(1)
                        ->live(),
                ])->columnSpanFull(),

            // Summary Section
            Section::make('Entry Summary')
                ->columns(3)
                ->schema([
                    Placeholder::make('total_debit')
                        ->label('إجمالي المدين')
                        ->content(fn(Get $get) => static::calculateTotal($get, 'debit')),
                    Placeholder::make('total_credit')
                        ->label('إجمالي الدائن')
                        ->content(fn(Get $get) => static::calculateTotal($get, 'credit')),
                    Placeholder::make('balance_diff')
                        ->label('الفارق (Difference)')
                        ->content(fn(Get $get) => static::calculateBalanceDiff($get)),
                ])->columnSpanFull(),
        ]);
    }

    public static function calculateTotal(Get $get, string $type): string
    {
        $total = resolve(AccountingService::class)->calculateTotal($get('entryLines'), $type);
        return number_format($total, 2);
    }

    public static function calculateBalanceDiff(Get $get): HtmlString
    {
        $diff = resolve(AccountingService::class)->calculateBalanceDiff($get('entryLines'));
        $color = round($diff, 2) == 0.00 ? 'text-success-600' : 'text-danger-600';
        return new HtmlString("<span class='font-bold {$color}'>" . number_format(abs($diff), 2) . "</span>");
    }

    /* -------------------------------------------------------------------------- */
    /*                                Form Helpers                                */
    /* -------------------------------------------------------------------------- */

    public static function syncAmount(Get $get, Set $set): void
    {
        $amount = (float) $get('display_amount');
        $type = $get('type') ?? 'debit';
        $rate = (float) ($get('exchange_rate'));

        $service = resolve(AccountingService::class);

        // This is mainly for UI feedback. The Action will re-calculate for truth.
        $finalAmount = $service->applyAccountingSign($amount, $type);
        $set('amount', $finalAmount);

        $mcAmount = round($finalAmount * $rate, 4);
        $set('mc_amount', abs($mcAmount));
    }

    public static function updateExchangeRate(Get $get, Set $set): void
    {
        $currencyId = $get('currency_id');
        if (!$currencyId) return;

        $service = resolve(AccountingService::class);
        $mainCurrencyId = $service->getMainCurrencyId();

        try {
            $rate = $service->getExchangeRate($currencyId, $mainCurrencyId);
            $set('exchange_rate', $rate);
            static::syncAmount($get, $set);
        } catch (\Exception $e) {
            $set('exchange_rate', 0);
            $set('mc_amount', 0);
            
            \Filament\Notifications\Notification::make()
                ->title('تنبيه: سعر الصرف مفقود')
                ->body("العملة المحددة ليس لها سعر صرف معروف.")
                ->warning()
                ->send();
        }
    }

    public static function refreshAccountNumber($state, Set $set): void
    {
        if (!$state) return;
        $account = \App\Models\Account::find($state);
        $set('account_number', $account?->AccountNumber);
    }
}
