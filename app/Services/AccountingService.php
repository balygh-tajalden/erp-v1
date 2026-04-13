<?php

namespace App\Services;

use App\Models\WalletTransaction;
use App\Models\Entry;
use App\Models\EntryDetail;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Account;
use App\Models\SimpleEntry;
use App\Models\AccountingSession;
use App\Services\Accounting\JournalManagerService;
use Illuminate\Database\Eloquent\Model;
use Exception;
use App\DTOs\Accounting\SimpleEntryDTO;
use App\DTOs\Accounting\BuyCurrencyDTO;
use Illuminate\Support\Facades\Cache;
use App\Services\Currency\ExchangeService;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountType;
use App\Models\SellCurrency;
use App\Models\BuyCurrency;
use App\DTOs\Accounting\SellCurrencyDTO;
use Illuminate\Support\Facades\Log;
use App\Services\Accounting\ValidationService;
use App\Services\Accounting\BalanceSyncService;
use App\Actions\Accounting\SellCurrencies\SellCurrencyAction;
use App\Actions\Accounting\BuyCurrencies\BuyCurrencyAction;
use App\Actions\Accounting\SimpleEntries\SimpleEntryAction;
use App\Actions\Accounting\JournalEntries\JournalEntryAction;
use App\Services\Accounting\EntryValidationService;


class AccountingService
{
    public function __construct(
        protected JournalManagerService $journal,
        protected BalanceSyncService $balanceSync,
        protected SellCurrencyAction $sellCurrencyAction,
        protected SimpleEntryAction $simpleEntryAction,
        protected BuyCurrencyAction $buyCurrencyAction,
        protected JournalEntryAction $journalEntryAction
    ) {}

    /**
     * تجلب الجلسة الحالية للمستخدم أو تنشئ واحدة إذا لزم الأمر للتوافق المحاسبي
     */
    public function getCurrentSessionID()
    {
        // البحث عن الجلسة المحاسبية النشطة للمستخدم (الوردية الحالية)
        $session = AccountingSession::where('user_id', Auth::id())
            ->where('IsEnded', 0)
            ->latest('StartTime')
            ->first();

        if (!$session) {
            if (app()->runningInConsole()) return 1;
            throw new Exception("لا توجد جلسة محاسبية نشطة للمستخدم. يرجى تسجيل الدخول مجدداً لفتح جلسة جديدة.");
        }

        return $session->legacy_id;
    }

    /**
     * التحقق من وجود قيد مكرر للقيود البسيطة (نفس التاريخ والمبلغ والحساب الصادر)
     */
    public function isSimpleEntryDuplicate(array $data, $ignoreId = null): bool
    {
        $query = DB::table('tblSimpleEntries')
            ->where('TheDate', $data['TheDate'] ?? null)
            ->where('Amount', (float) ($data['Amount'] ?? 0))
            ->where('FromAccountID', $data['FromAccountID'] ?? null)
            ->where('isDeleted', 0);

        if ($ignoreId) {
            $query->where('ID', '!=', $ignoreId);
        }

        return $query->exists();
    }

    /**
     * التحقق من وجود قيد مكرر لقيود اليومية (نفس التاريخ وإجمالي المبلغ والحساب الأول)
     */
    public function isJournalEntryDuplicate(array $data, $ignoreId = null): bool
    {
        // 1. حساب إجمالي مبلغ القيد (المدين) من الأسطر
        $totalAmount = round($this->calculateTotal($data['lines'], 'debit'), 2);
        
        // 2. أول حساب في القيد
        $firstAccountId = $data['lines'][0]['account_id'];

        if (!$firstAccountId || $totalAmount <= 0) return false;

        $query = DB::table('tblEntries as e')
            ->join('tblEntryDetails as ed', 'e.ID', '=', 'ed.ParentID')
            ->where('e.TheDate', $data['TheDate'] ?? null)
            ->where('e.IsPosted', 1)
            ->where('e.isDeleted', 0)
            ->where('ed.AccountID', $firstAccountId)
            ->where(DB::raw('ROUND(ABS(ed.Amount) * ed.MCAmount / ABS(ed.Amount), 2)'), $totalAmount);

        if ($ignoreId) {
            $query->where('e.ID', '!=', $ignoreId);
        }

        return $query->exists();
    }
    /**
     * Post a wallet transaction to the accounting system.
     */



    public function postSellCurrency(SellCurrencyDTO $dto): Model
    {
        $mainCurrencyId = $this->getMainCurrencyId();

        $helperData = [
            'mainCurrencyId' => $mainCurrencyId,
            'rateSold'       => $this->getExchangeRate($dto->currencyId, $mainCurrencyId),
            'rateReceived'   => $this->getExchangeRate($dto->exchangeCurrencyId, $mainCurrencyId),
            'currencyName'   => $this->getCurrencyName($dto->currencyId),
            'sessionId'      => $this->getCurrentSessionID(),
        ];

        return $this->sellCurrencyAction->create($dto, $helperData);
    }

    public function updateSellCurrency(SellCurrency $sellCurrency, SellCurrencyDTO $dto): Model
    {
        $mainCurrencyId = $this->getMainCurrencyId();

        $helperData = [
            'mainCurrencyId' => $mainCurrencyId,
            'rateSold'       => $this->getExchangeRate($dto->currencyId, $mainCurrencyId),
            'rateReceived'   => $this->getExchangeRate($dto->exchangeCurrencyId, $mainCurrencyId),
            'currencyName'   => $this->getCurrencyName($dto->currencyId),
        ];

        return $this->sellCurrencyAction->update($sellCurrency, $dto, $helperData);
    }

    public function deleteSellCurrency(SellCurrency $sellCurrency)
    {
        return $this->sellCurrencyAction->delete($sellCurrency);
    }

    public function postBuyCurrency(BuyCurrencyDTO $dto): Model
    {
        $mainCurrencyId = $this->getMainCurrencyId();

        $helperData = [
            'mainCurrencyId' => $mainCurrencyId,
            'rateBought'     => $this->getExchangeRate($dto->currencyId, $mainCurrencyId),
            'rateReceived'   => $this->getExchangeRate($dto->exchangeCurrencyId, $mainCurrencyId),
            'currencyName'   => $this->getCurrencyName($dto->currencyId),
            'sessionId'      => $this->getCurrentSessionID(),
        ];

        return $this->buyCurrencyAction->create($dto, $helperData);
    }

    public function updateBuyCurrency(BuyCurrency $buyCurrency, BuyCurrencyDTO $dto): Model
    {
        $mainCurrencyId = $this->getMainCurrencyId();

        $helperData = [
            'mainCurrencyId' => $mainCurrencyId,
            'rateBought'     => $this->getExchangeRate($dto->currencyId, $mainCurrencyId),
            'rateReceived'   => $this->getExchangeRate($dto->exchangeCurrencyId, $mainCurrencyId),
            'currencyName'   => $this->getCurrencyName($dto->currencyId),
        ];

        return $this->buyCurrencyAction->update($buyCurrency, $dto, $helperData);
    }

    public function deleteBuyCurrency(BuyCurrency $buyCurrency)
    {
        return $this->buyCurrencyAction->delete($buyCurrency);
    }


    /**
     * Unpost a wallet transaction and delete its entries.
     */


    public function getAccountBalance($accountID)
    {
        return DB::table('tblEntryDetails')
            ->where('AccountID', $accountID)
            ->sum('Amount');
    }

    /**
     * جلب اسم الحساب بواسطة المعرف
     */
    public function getAccountName($accountId)
    {
        if (!$accountId) return 'كافة الحسابات';

        return Account::where('ID', $accountId)->value('AccountName');
    }

    /**
     * جلب اسم العملة بواسطة المعرف
     */
    public function getCurrencyName($currencyId)
    {
        if (!$currencyId) return 'كافة العملات';

        return Currency::where('ID', $currencyId)->value('CurrencyName');
    }




    /**
     * إنشاء قيد بسيط (Master + GL) باستخدام DTO
     */
    public function createSimpleEntry(SimpleEntryDTO $dto): Model
    {
        $mainCurrencyId = $this->getMainCurrencyId();

        $helperData = [
            'exchangeRate' => $this->getExchangeRate($dto->currencyId, $mainCurrencyId),
            'fromAccountName' => $this->getAccountName($dto->fromAccountId),
            'toAccountName' => $this->getAccountName($dto->toAccountId),
        ];

        return $this->simpleEntryAction->create($dto, $helperData);
    }

    public function updateSimpleEntry(SimpleEntry $simpleEntry, SimpleEntryDTO $dto): Model
    {
        $mainCurrencyId = $this->getMainCurrencyId();

        $helperData = [
            'exchangeRate' => $this->getExchangeRate($dto->currencyId, $mainCurrencyId),
            'fromAccountName' => $this->getAccountName($dto->fromAccountId),
            'toAccountName' => $this->getAccountName($dto->toAccountId),
        ];

        return $this->simpleEntryAction->update($simpleEntry, $dto, $helperData);
    }

    public function deleteSimpleEntry(SimpleEntry $simpleEntry)
    {
        return $this->simpleEntryAction->delete($simpleEntry);
    }

    /**
     * إنشاء قيد مزدوج (Journal Entry) باستخدام DTO
     */
    public function createJournalEntry(\App\DTOs\Accounting\JournalEntryDTO $dto): Entry
    {
        $this->validateJournalEntry($dto);
        return $this->journalEntryAction->create($dto);
    }

    /**
     * تعديل قيد مزدوج (Journal Entry) باستخدام DTO
     */
    public function updateJournalEntry(Entry $entry, \App\DTOs\Accounting\JournalEntryDTO $dto): Entry
    {
        $this->validateJournalEntry($dto);
        return $this->journalEntryAction->update($entry, $dto);
    }




    public function getNextSequence($docType, $branchId, $year = null)
    {
        return $this->journal->getNextSequence($docType, $branchId, $year);
    }


    /**
     * جلب العملة الافتراضية للنظام
     */
    public function getMainCurrencyId()
    {
        return Currency::where('IsDefault', 1)->value('ID');
    }

    /**
     * جلب سعر الصرف بين عملتين
     */
    public function getExchangeRate($sourceId, $targetId)
    {
        if ($sourceId == $targetId) return 1;

        return resolve(ExchangeService::class)->getRate($sourceId, $targetId);
    }


    /**
     * إبطال قيد (Void) مع ذكر السبب
     */
    public function voidEntry($entryId, $reason)
    {
        return DB::transaction(function () use ($entryId, $reason) {
            $entry = Entry::findOrFail($entryId);

            // تحديث حالة القيد
            $entry->update([
                'isDeleted' => 1,
                'Notes' => $entry->Notes . " (Voided: $reason)",
                'ModifiedBy' => Auth::id() ?? 1,
                'ModifiedDate' => now(),
            ]);

            // عكس الأرصدة يدوياً بسبب الإبطال
            $this->balanceSync->revert($entry);

            // تسجيل في السجل (History)
            DB::table('tblhistory')->insert([
                'TableName' => 'tblEntries',
                'RecordID' => $entryId,
                'UserID' => Auth::id(),
                'ActionType' => 'VOID',
                'ActionDescription' => "Voided entry: $reason",
                'ActionDate' => now(),
            ]);

            return $entry;
        });
    }


    /**
     * حساب سعر الصرف والمبلغ المستلم لعملية بيع عملة
     */
    public function calculateExchangePrice($sourceId, $targetId, $amount = 0): array
    {
        $rate = $this->getExchangeRate($sourceId, $targetId);

        return [
            'rate' => $rate,
            'exchangeAmount' => round((float)$amount * (float)$rate, 4),
        ];
    }

    /**
     * حساب تفاصيل السعر والمبلغ للعملات (تستخدم للبيع والشراء)
     */
    public function getCurrencyExchangeDetails(string $operationType, $currencyId, $exchangeCurrencyId, $amount = 0): array
    {
        $validation = app(ValidationService::class);
        $actionName = $operationType === 'buy' ? 'شراء' : 'بيع';

        // 1. التحقق من العملة (يجب أن لا تكون العملة المحلية)
        if ($currencyId && !$validation->validateForeignCurrency($currencyId, $actionName)) {
            return ['error' => 'invalid_currency', 'set' => ['CurrencyID' => null]];
        }

        // 2. التحقق من اختلاف العملتين
        if (!$validation->validateDifferentCurrencies($currencyId, $exchangeCurrencyId)) {
            return ['error' => 'same_currencies', 'set' => ['ExchangeCurrencyID' => null]];
        }

        // 3. التحقق من توفر سعر صرف
        if ($currencyId && $exchangeCurrencyId && !$validation->validateExchangeRate($currencyId, $exchangeCurrencyId)) {
            return ['error' => 'invalid_rate', 'set' => ['Price' => null, 'ExchangeAmount' => null]];
        }

        $result = $this->calculateExchangePrice($currencyId, $exchangeCurrencyId, $amount);

        return [
            'success' => true,
            'set' => [
                'Price' => $result['rate'],
                'ExchangeAmount' => $result['exchangeAmount'],
            ]
        ];
    }

    /**
     * عملية حسابية بسيطة لضرب مبلغ في سعر (مثل إجمالي البيع)
     */
    public function calculateAmountPriceTotal($amount, $price): float
    {
        return round((float)$amount * (float)$price, 4);
    }

    /**
     * حساب إجمالي طرف معين (مدين أو دائن) لمجموعة من الخطوط
     */
    public function calculateTotal(?array $lines, string $type): float
    {
        if (empty($lines)) return 0.0;

        return array_reduce($lines, function ($sum, $line) use ($type) {
            $displayAmount = (float)($line['display_amount'] ?? 0);
            $lineType = $line['type'] ?? 'debit';
            $exchangeRate = (float)($line['exchange_rate'] ?? 1);

            $amount = $this->applyAccountingSign($displayAmount, $lineType);

            // حساب المعادل بالعملة المحلية للمقارنة والترصيد
            $mcAmount = $amount * $exchangeRate;

            if ($type === 'debit' && $mcAmount > 0) return $sum + $mcAmount;
            if ($type === 'credit' && $mcAmount < 0) return $sum + abs($mcAmount);
            return $sum;
        }, 0.0);
    }

    /**
     * حساب فارق الميزان بين المدين والدائن
     */
    public function calculateBalanceDiff(?array $lines): float
    {
        if (empty($lines)) return 0.0;

        return array_reduce($lines, function ($sum, $line) {
            $displayAmount = (float)($line['display_amount'] ?? 0);
            $lineType = $line['type'] ?? 'debit';
            $exchangeRate = (float)($line['exchange_rate'] ?? 1);

            $amount = $this->applyAccountingSign($displayAmount, $lineType);

            // حساب المعادل بالعملة المحلية لتحديد التوازن الرياضي
            $mcAmount = $amount * $exchangeRate;

            return $sum + $mcAmount;
        }, 0.0);
    }

    /**
     * تطبيق الإشارة المحاسبية بناءً على النوع (مدين + / دائن -)
     */
    public function applyAccountingSign(float $amount, string $type): float
    {
        return $type === 'debit' ? abs($amount) : -abs($amount);
    }

    /**
     * التحقق من صحة القيد المزدوج قبل الحفظ
     */
    public function validateJournalEntry(\App\DTOs\Accounting\JournalEntryDTO $dto): void
    {
        $lines = $dto->lines;

        // 1. عدد الأسطر
        if (count($lines) < 2) {
            throw new Exception("يجب أن يحتوي القيد على سطرين على الأقل (مدين ودائن).");
        }

        // 2. التحقق من البيانات الأساسية في كل سطر
        foreach ($lines as $index => $line) {
            $row = $index + 1;
            if (empty($line['account_id'])) {
                throw new Exception("يرجى تحديد حساب للسطر رقم ($row).");
            }
            if ((float)$line['display_amount'] <= 0) {
                throw new Exception("يجب أن يكون المبلغ في السطر ($row) أكبر من الصفر.");
            }
        }

        // 3. التحقق من التوازن
        $diff = $this->calculateBalanceDiff($lines);
        if (round($diff, 2) != 0) {
            $formattedDiff = number_format(abs($diff), 2);
            throw new Exception("القيد غير متزن. الفارق الحالي هو: $formattedDiff");
        }
    }
}
