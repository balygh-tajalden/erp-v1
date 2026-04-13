<?php

namespace App\Services\Accounting;

use App\Models\Entry;
use App\Models\EntryDetail;
use App\Models\Account;
use App\Models\Currency;
use App\Models\CurrencyPrice;
use App\Models\AccountingSession;
use App\Services\BaseService;
use App\Exceptions\DuplicateEntryException;
use App\Services\AccountingService;
use Exception;

/**
 * EntryValidationService: Implements specific business rules for Journal Entries.
 */
class EntryValidationService extends BaseService
{
    /**
     * @var bool Flag to bypass duplicate check for the current request context.
     */
    public static bool $skipDuplicateCheck = false;

    /**
     * Disables the duplicate entry check for the remainder of this request.
     */
    public static function disableDuplicateCheck(): void
    {
        self::$skipDuplicateCheck = true;
    }

    /**
     * Run all requested validations for an entry
     */
    public function validate($entryId)
    {
        $entry = Entry::with('details')->findOrFail($entryId);
        $details = $entry->details;

        // 1. Min Lines Check (الحد الأدنى للأسطر)
        if ($details->count() < 2) {
            throw new Exception("يجب أن يحتوي القيد على سطرين على الأقل.");
        }

        $totalSum = 0;
        foreach ($details as $detail) {
            // 2. No Zeros Check (منع الأصفار)
            if (round($detail->Amount, 4) == 0 && round($detail->MCAmount, 4) == 0) {
                throw new Exception("يجب أن يكون مبلغ السطر أو المبلغ المكافئ أكبر من صفر.");
            }

            // 3. Fraction Precision Check (دقة الكسور)
            // (Assuming max 4 decimals as per standard accounting)
            if (strlen(substr(strrchr($detail->Amount, "."), 1)) > 4) {
                throw new Exception("لا يمكن قبول مبالغ بدقة تتجاوز 4 خانات عشرية.");
            }

            // 4. Rule 11: Sub-accounts only (الحسابات الفرعية فقط)
            $account = Account::find($detail->AccountID);
            if ($account->AccountTypeID == 1) { // 1 = رئيسي
                throw new Exception("لا يمكن التسجيل على حساب رئيسي: " . $account->AccountName);
            }
        }

        // 5. Multi-Currency Balance Rule (توازن العملات المتعددة بالريال)
        $totalMCAmount = $details->sum('MCAmount');
        if (round($totalMCAmount, 4) !== 0.0000) {
            throw new Exception("القيد غير متزن بالعملة المحلية. الفارق: " . $totalMCAmount);
        }



        // 6. Rule 16: Exchange Rate (سعر الصرف)
        // التحقق من أن كل عملة أجنبية في تفاصيل القيد لديها سعر صرف محدد
        $accountingService = app(AccountingService::class);
        $mainCurrencyId = $accountingService->getMainCurrencyId();
        foreach ($details as $detail) {
            if ($detail->CurrencyID != $mainCurrencyId) {
                $rate = $accountingService->getExchangeRate($detail->CurrencyID, $mainCurrencyId);
                if (empty($rate) || $rate <= 0) {
                    $currencyName = $accountingService->getCurrencyName($detail->CurrencyID);
                    throw new Exception("العملة ({$currencyName}) ليس لديها سعر صرف محدد. الرجاء إضافة سعر صرف أولاً من شاشة العملات والمحاسبة.");
                }
            }
        }

        // 7. Rule 22: Active Session (الجلسة النشطة)
        // البحث عن وردية نشطة للمستخدم الذي أنشأ القيد في نفس الفرع
        $session = AccountingSession::where('user_id', $entry->CreatedBy)
            ->where('BranchID', $entry->BranchID)
            ->where('IsEnded', 0)
            ->first();

        if (!$session && !app()->runningInConsole()) {
            throw new Exception("لا توجد جلسة محاسبية نشطة للمستخدم حالياً. يرجى تسجيل الدخول مجدداً.");
        }

        // 8. Rule 23: Duplicate Entry Prevention (تطابق القريب/الفريد)
        // Note: This is now a soft check handled in the UI if needed.
        // If you want it to REMAIN a hard check, call $this->isDuplicate() here.

        // 9. Rule 28: Prevent deletion of reversed entries (منع حذف القيد المعكوس)
        if ($entry->IsReversed) {
            throw new Exception("لا يمكن حذف أو تعديل قيد تم عكسه مسبقاً.");
        }

        return true;
    }

    /**
     * التحقق من تكرار القيد مع رمي استثناء خاص
     */
    public function validateDuplicate($entryId)
    {
        if (self::$skipDuplicateCheck) {
            return;
        }

        if ($this->isDuplicate($entryId)) {
            throw new DuplicateEntryException("تم اكتشاف قيد مكرر بنفس التاريخ والمبلغ والحساب الأول.");
        }
    }

    /**
     * التحقق من تكرار القيد (Logic)
     */
    public function isDuplicate($entryId): bool
    {
        $entry = Entry::with('details')->findOrFail($entryId);
        $details = $entry->details;

        if ($details->isEmpty()) return false;

        return Entry::where('ID', '!=', $entryId)
            ->where('TheDate', $entry->TheDate)
            ->where('DocumentID', $entry->DocumentID)
            ->where('isDeleted', 0) // Explicit check for legacy column
            ->whereNull('deleted_at') // Explicit check for SoftDeletes
            ->whereHas('details', function ($q) use ($details) {
                // Simplified duplicate check: same amount and same first account
                $q->where('AccountID', $details->first()->AccountID)
                    ->where('Amount', $details->first()->Amount)
                    ->where('CurrencyID', $details->first()->CurrencyID)
                    ->whereNull('deleted_at'); // Ensure detail is not deleted
            })
            ->exists();
    }

    /**
     * التحقق من تكرار القيد (Logic) باستخدام البيانات (قبل الإنشاء)
     */
    public function isDuplicateData(array $entryData, array $firstDetailData): bool
    {
        return Entry::where('TheDate', $entryData['TheDate'])
            ->where('DocumentID', $entryData['DocumentID'])
            ->where('isDeleted', 0)
            ->whereNull('deleted_at')
            ->whereHas('details', function ($q) use ($firstDetailData) {
                $q->where('AccountID', $firstDetailData['AccountID'])
                    ->where('Amount', $firstDetailData['Amount'])
                    ->where('CurrencyID', $firstDetailData['CurrencyID'])
                    ->whereNull('deleted_at');
            })
            ->exists();
    }
}
