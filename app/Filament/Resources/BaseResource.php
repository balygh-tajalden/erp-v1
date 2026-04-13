<?php

namespace App\Filament\Resources;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\User;
use App\Models\UserGroup;
use App\Services\AccountingService;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

abstract class BaseResource extends Resource
{
    protected static ?int $documentTypeID = null;
    /**
     * تجلب قائمة الحسابات للاستخدام في القوائم المنسدلة
     */
    public static function getAccountOptions($onlyDetailed = false): array
    {
        $cacheKey = $onlyDetailed ? 'lookup:accounts:detailed' : 'lookup:accounts:all';

        return Cache::rememberForever($cacheKey, function () use ($onlyDetailed) {
            $query = Account::query();

            if ($onlyDetailed) {
                $query->where('AccountTypeID', 2);
            }

            return $query->get()
                ->mapWithKeys(fn($account) => [
                    $account->ID => "{$account->AccountNumber} - {$account->AccountName}"
                ])
                ->toArray();
        });
    }

    /**
     * تجلب قائمة العملات للاستخدام في القوائم المنسدلة
     */
    public static function getCurrencyOptions($codeType = 'ISO_Code'): array
    {
        return Cache::rememberForever("lookup:currencies:{$codeType}", function () use ($codeType) {
            return Currency::query()
                ->get()
                ->mapWithKeys(fn($currency) => [
                    $currency->ID => $currency->CurrencyName . ($currency->{$codeType} ? " ({$currency->{$codeType}})" : "")
                ])
                ->toArray();
        });
    }

    /**
     * تجلب قائمة الحسابات التفصيلية
     */
    public static function getChildrenAccountOptions(): array
    {
        return Cache::rememberForever(
            'lookup:accounts:children',
            fn() => Account::query()
                ->where('AccountTypeID', 2)
                ->orderBy('AccountName')

                ->pluck('AccountName', 'ID')
                ->toArray()
        );
    }

    /**
     * تجلب قائمة الصناديق (Cash Boxes)
     */
    public static function getCashBoxOptions(): array
    {
        return Cache::rememberForever(
            'lookup:accounts:cashboxes',
            fn() => Account::query()
                ->where('FatherNumber', '1211')
                ->orderBy('AccountName')
                ->pluck('AccountName', 'ID')
                ->toArray()
        );
    }
    public static function getChildrenAccountAndCashBoxOptions(): array
    {
        return self::getChildrenAccountOptions() + self::getCashBoxOptions();
    }

    /**
     * تجلب قائمة الفروع
     */
    public static function getBranchOptions(): array
    {
        return Cache::rememberForever(
            'lookup:branches',
            fn() =>
            Branch::query()
                ->pluck('BranchName', 'ID')
                ->toArray()
        );
    }


    /**
     * جلب قائمة أنواع المستندات النشطة
     */
    public static function getDocumentTypeOptions($id = null): array
    {
        return Cache::rememberForever(
            'lookup:document_types',
            fn() =>
            DB::table('tblDocumentTypes')
                ->where('IsActive', 1)
                ->where('ID', $id)
                ->pluck('DocumentName', 'ID')
                ->toArray()
        );
    }

    /**
     * تجلب رقم التسلسل التالي لنوع مستند معين
     * تقوم بجلب النوع والفرع والسنة تلقائياً إذا أمكن
     */
    public static function getNextSequence($docType = null, $branchId = null, $year = null): int
    {
        // إذا لم يتم تمرير النوع، نحاول جلب المعرف من الكلاس الحالي ( static::$documentTypeID )
        $docType = $docType ?? (isset(static::$documentTypeID) ? static::$documentTypeID : null);

        if (!$docType) {
            // إذا كنا في كلاس موديل (مثل SellCurrency::class)، فقد نحتاج للتعامل معه ولكن الأفضل هو الـ IDs
            // سنقوم بإرجاع 0 أو التعامل مع الخطأ بشكل أفضل
            return 0;
        }

        $branchId = $branchId ?? 2; // الافتراضي للفرع الأول
        $year = $year ?? date('Y');

        return app(AccountingService::class)->getNextSequence($docType, $branchId, $year);
    }

    /**
     * تجلب قائمة الحسابات التي يمكن أن تكون "أباً" (رئيسية) 
     * مع استبعاد المجموعات التي تحتوي بالفعل على أبناء من نوع "رئيسي"
     * لضمان عدم خلط الحسابات الفرعية والرئيسية في نفس المستوى
     */
    public static function getParentAccountOptions($targetAccountTypeID = 1): array
    {
        $cacheKey = "lookup:parent_accounts_for_type_" . $targetAccountTypeID;

        return Cache::rememberForever($cacheKey, function () use ($targetAccountTypeID) {
            $query = Account::query()
                ->where('AccountTypeID', 1); // لا يمكن أن يكون الأب إلا حساباً رئيسياً

            // استبعاد المجموعات التي تحتوي بالفعل على أبناء من نوع مخالف لنوع الحساب الجديد
            $query->whereNotExists(function ($q) use ($targetAccountTypeID) {
                $oppositeType = ($targetAccountTypeID == 1) ? 2 : 1;

                $q->select(DB::raw(1))
                    ->from('tblAccounts as children')
                    ->whereColumn('children.FatherNumber', 'tblAccounts.AccountNumber')
                    ->where('children.AccountTypeID', $oppositeType)
                    ->whereNull('children.deleted_at');
            });

            return $query->get()
                ->mapWithKeys(fn($account) => [
                    $account->AccountNumber => "{$account->AccountNumber} - {$account->AccountName}"
                ])
                ->toArray();
        });
    }

    /**
     * تجلب قائمة المستخدمين
     */
    public static function getUserOptions(): array
    {
        return Cache::rememberForever(
            'lookup:users',
            fn() =>
            User::query()
                ->pluck('UserName', 'ID')
                ->toArray()
        );
    }

    /**
     * تجلب قائمة مجموعات المستخدمين
     */
    public static function getUserGroupOptions(): array
    {
        return Cache::rememberForever(
            'lookup:user_groups',
            fn() =>
            UserGroup::query()
                ->pluck('GroupName', 'ID')
                ->toArray()
        );
    }

    /**
     * تحديث سعر الصرف والمبالغ في نماذج بيع وشراء العملات
     */
    public static function refreshCurrencyPrice(string $operationType, $get, $set): void
    {
        $details = app(AccountingService::class)->getCurrencyExchangeDetails(
            $operationType,
            $get('CurrencyID'),
            $get('ExchangeCurrencyID'),
            $get('Amount')
        );

        foreach ($details['set'] as $key => $value) {
            $set($key, $value);
        }
    }
    /**
     * تحديث إجمالي المبلغ (المبلغ * السعر) في النماذج
     */
    public static function refreshExchangeAmount($get, $set): void
    {
        $amount = $get('Amount');
        $price = $get('Price');

        // لا نحسب إذا فقدنا أحد القيم (لضمان الدقة وفق طلب العميل)
        if ($amount === null || $price === null) return;

        $total = app(AccountingService::class)->calculateAmountPriceTotal($amount, $price);

        $set('ExchangeAmount', $total);
    }

    /**
     * تحديث معرف الحساب بناءً على رقم الحساب
     */
    public static function refreshAccountID($state, $set, $targetField = 'AccountID'): void
    {
        if (!$state) return;

        $account = Account::where('AccountNumber', $state)
            ->where('AccountTypeID', 2)
            ->first();

        $set($targetField, $account?->ID);
    }

    /**
     * تحديث رقم الحساب بناءً على معرف الحساب
     */
    public static function refreshAccountNumber($state, $set, $targetField = 'AccountNumber'): void
    {
        if (!$state) return;

        $account = Account::where('ID', $state)
            ->where('AccountTypeID', 2)
            ->first();
        $set($targetField, $account?->AccountNumber ?? null);
    }

    /**
     * تحديث رقم المستند في الواجهة (Form) بناءً على المدخلات الحالية
     */
    public static function refreshDocumentNumber($set, $get, string $targetField = 'TheNumber'): void
    {
        $branchId = $get('BranchID');
        $date = $get('TheDate') ?? now();
        $year = date('Y', strtotime($date));

        $set($targetField, static::getNextSequence(null, $branchId, $year));
    }

    /**
     * جلب رقم المستند الافتراضي للواجهة (Form)
     */
    public static function getDefaultDocumentNumber($get): int
    {
        $branchId = $get('BranchID');
        $date = $get('TheDate') ?? now();
        $year = date('Y', strtotime($date));

        return static::getNextSequence(null, $branchId, $year);
    }
}
