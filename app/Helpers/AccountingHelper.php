<?php

namespace App\Helpers;

use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Query\Expression;

/**
 * AccountingHelper: Global utilities for currency, numbers, and text.
 */
class AccountingHelper
{
    /**
     * 1. Smart Money Formatting (تنسيق المبالغ الذكي)
     * Shows decimals only if they are not zero.
     */
    public static function formatMoney($value)
    {
        $value = (float)($value ?? 0);
        $decimals = ($value == (int)$value) ? 0 : 2;
        return number_format($value, $decimals);
    }

    /**
     * 2. Amount to Words (Tafqeet - التفقيط)
     * Converts numbers to Arabic words.
     */
    public static function amountToWords($amount, $currency = 'ريال يمني')
    {
        $amount = floor(abs($amount));
        if ($amount == 0) return 'صفر ' . $currency;

        $units = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة'];
        $teens = ['عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
        $tens  = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
        $hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];
        $parts = [['ألف', 'ألفان', 'آلاف'], ['مليون', 'مليونان', 'ملايين'], ['مليار', 'ملياران', 'مليارات']];

        $process = function ($n) use (&$process, $units, $teens, $tens, $hundreds, $parts) {
            if ($n < 11) return $units[$n];
            if ($n < 20) return $teens[$n - 10];
            if ($n < 100) return ($n % 10 ? $units[$n % 10] . ' و' : '') . $tens[floor($n / 10)];
            if ($n < 1000) return $hundreds[floor($n / 100)] . ($n % 100 ? ' و' . $process($n % 100) : '');

            foreach (array_reverse($parts, true) as $i => $p) {
                $d = pow(1000, $i + 1);
                if ($n >= $d) {
                    $c = floor($n / $d);
                    $r = $n % $d;
                    $txt = $c == 1 ? $p[0] : ($c == 2 ? $p[1] : ($c <= 10 ? $process($c) . ' ' . $p[2] : $process($c) . ' ' . $p[0]));
                    return $txt . ($r ? ' و' . $process($r) : '');
                }
            }
        };

        return 'فقط ' . $process($amount) . ' ' . $currency . ' لا غير';
    }

    /**
     * 3. Money Summarizer (مختصر الإجماليات)
     * Returns a pre-configured Sum summarizer for Filament tables.
     */
    public static function moneySummarizer($label = null, $includeTafqit = false)
    {
        $summarizer = Sum::make()
            ->label($label)
            ->formatStateUsing(fn($state) => self::formatMoney($state));

        if ($includeTafqit) {
            return [
                Sum::make()->label($label)->formatStateUsing(fn($state) => self::amountToWords($state)),
                $summarizer,
            ];
        }

        return [$summarizer];
    }

    public static function tafqitSummarizer($label = '')
    {
        return Summarizer::make()
            ->label($label)
            ->using(function ($query) {
                // حل مشكلة الـ Expression Object عند التعامل مع الـ Views
                $from = $query->from;
                $table = ($from instanceof Expression)
                    ? (string) $from->getValue($query->getGrammar())
                    : (string) $from;

                $column = (str_contains($table, 'Summary')) ? 'صافي المبلغ' : 'المبلغ';
                return $query->sum($column);
            })
            ->formatStateUsing(function ($state) use ($label) {
                if (!$state) return $label;

                $prefix = ($state < 0) ? 'الرصيد عليكم: ' : 'الرصيد لكم: ';
                return $prefix . self::amountToWords(abs($state));
            });
    }



    /**
     * 4. Clean Number (تنظيف الأرقام)
     */
    public static function cleanNumber($value)
    {
        return (float) str_replace([',', ' '], '', $value);
    }

    /**
     * 5. Convert to Base (التحويل للعملة المحلية)
     */
    public static function convertToBase($amount, $rate)
    {
        return self::cleanNumber($amount) * self::cleanNumber($rate);
    }

    /**
     * 6. Generate Reference (توليد مرجع فريد)
     */
    public static function generateReference($prefix = 'REF')
    {
        return $prefix . '-' . strtoupper(uniqid());
    }

    /**
     * 7. Apply Statement Filters (تطبيق فلاتر كشف الحساب المركزية)
     */
    public static function applyStatementFilters($query, $filters)
    {
        $dateType = $filters['date_type'] ?? 'any_date';
        $fromDate = $filters['fromDate'] ?? null;
        $toDate = $filters['toDate'] ?? null;
        $accountId = $filters['accountId'] ?? null;
        $currencyId = $filters['currencyId'] ?? null;

        // 1. فلترة الحساب (إذا كان التقرير تفصيلي)
        if ($accountId) {
            $query->where('AccountID', $accountId);
        }

        // 2. فلترة العملة
        if ($currencyId) {
            $query->where('CurrencyID', $currencyId);
        }

        // 3. تحديد أعمدة التاريخ بناءً على نوع الـ View
        $isSummaryOnly = $filters['is_summary_only'] ?? false;
        $isSummary = empty($accountId) || $isSummaryOnly;
        $dateCol = $isSummary ? 'آخر تاريخ' : 'التاريخ';
        $fromDateCol = $isSummary ? 'أول تاريخ' : 'التاريخ';

        // 4. تطبيق منطق التاريخ
        switch ($dateType) {
            case 'daily':
                $query->whereDate($dateCol, $toDate ?: now()->toDateString());
                break;

            case 'period':
                if ($fromDate) $query->whereDate($fromDateCol, '>=', $fromDate);
                if ($toDate) $query->whereDate($dateCol, '<=', $toDate);
                break;

            case 'monthly':
                if ($toDate) {
                    $carbonDate = \Carbon\Carbon::parse($toDate);
                    $query->whereYear($dateCol, $carbonDate->year)
                        ->whereMonth($dateCol, $carbonDate->month);
                }
                break;

            case 'yearly':
                if ($toDate) {
                    $carbonDate = \Carbon\Carbon::parse($toDate);
                    $query->whereYear($dateCol, $carbonDate->year);
                }
                break;

            case 'until_today':
                if ($toDate) {
                    $query->whereDate($dateCol, '<=', $toDate);
                }
                break;

            case 'any_date':
            default:
                break;
        }

        return $query;
    }
}
