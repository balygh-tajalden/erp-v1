<?php

namespace App\Actions\Accounting\SellCurrencies;

use App\DTOs\Accounting\SellCurrencyDTO;
use App\Models\SellCurrency;
use App\Models\Account;
use App\Services\Accounting\BalanceSyncService;
use App\Services\Accounting\JournalManagerService;
use App\Services\System\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Number;

class SellCurrencyAction
{
    public function __construct(
        protected BalanceSyncService $balanceSync,
        protected JournalManagerService $journal,
        protected WhatsAppService $whatsapp
    ) {}

    /**
     * تنفيذ عملية إضافة مستند بيع العملة
     */
    public function create(SellCurrencyDTO $dto, array $helperData)
    {
        return DB::transaction(function () use ($dto, $helperData) {
            try {
                // 1. إنشاء سجل بيع العملة
                $sellCurrency = SellCurrency::create([
                    'Thenumber'          => $dto->theNumber,
                    'TheDate'            => $dto->date,
                    'AccountID'          => $dto->accountId,
                    'CurrencyID'         => $dto->currencyId,
                    'Amount'             => $dto->amount,
                    'FundAccountID'      => $dto->fundAccountId,
                    'Price'              => $dto->price,
                    'ExchangeAmount'     => $dto->exchangeAmount,
                    'ExchangeCurrencyID' => $dto->exchangeCurrencyId,
                    'CreatedBy'          => Auth::id(),
                    'BranchID'           => $dto->branchId,
                    'PurchaseMethod'     => $dto->purchaseMethod,
                    'RowVersion'         => 1, 
                    'IsDeleted'          => 0,
                    'IsReversed'         => 0,
                    'SessionID'          => $helperData['sessionId'],
                ]);

                // 2. تهيئة القيد المحاسبي
                $glEntry = $this->journal->initialize([
                    'DocumentID'    => $dto->documentTypeId,
                    'TheDate'       => $dto->date,
                    'Notes'         => $dto->notes,
                    'BranchID'      => $dto->branchId,
                    'RecordID'      => $sellCurrency->ID,
                    'RecordNumber'  => $dto->theNumber,
                ]);

                // 3. إضافة التفاصيل باستخدام المترجم المدمج
                $mappedDetails = $this->mapToEntryDetails($dto, $helperData);

                foreach ($mappedDetails as $detailData) {
                    $this->journal->addDetail($glEntry->ID, $detailData);
                }

                // 4. الترحيل النهائي وتحديث مستند البيع
                $this->journal->post($glEntry->ID);
                $sellCurrency->update(['EntryID' => $glEntry->ID]);

                // 5. تطبيق موازنة الأرصدة
                $glEntry->load('details');
                $this->balanceSync->apply($glEntry);

                // 6. إرسال إشعارات الواتساب
                try {
                    $this->whatsapp->sendSellCurrencyNotification($sellCurrency);
                } catch (Exception $e) {
                    Log::error('WhatsApp Notification failed: ' . $e->getMessage());
                }

                return $sellCurrency;
            } catch (Exception $e) {
                Log::error('SellCurrencyAction Create failed: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * تنفيذ عملية تعديل مستند بيع العملة
     */
    public function update(SellCurrency $sellCurrency, SellCurrencyDTO $dto, array $helperData)
    {
        return DB::transaction(function () use ($sellCurrency, $dto, $helperData) {
            try {
                // 🔒 نقطة تفتيش التزامن (Optimistic Locking)
                if ($dto->rowVersion != null && $sellCurrency->RowVersion != $dto->rowVersion) {
                    throw new Exception("تنبيه أمني: تم تعديل هذا السند من قبل مستخدم آخر أثناء فتحك للشاشة. يرجى تحديث الصفحة لمنع تداخل البيانات.");
                }

                // 1. تحميل القيد والمزامنة العكسية
                $sellCurrency->load('entry.details');
                $glEntry = $sellCurrency->entry;

                if (!$glEntry) {
                    throw new Exception("القيد المحاسبي غير موجود لهذا المستند.");
                }

                $this->balanceSync->revert($glEntry);

                // 2. تحديث سجل بيع العملة
                $sellCurrency->update([
                    'Thenumber'          => $dto->theNumber,
                    'TheDate'            => $dto->date,
                    'AccountID'          => $dto->accountId,
                    'CurrencyID'         => $dto->currencyId,
                    'Amount'             => $dto->amount,
                    'Notes'              => $dto->notes,
                    'FundAccountID'      => $dto->fundAccountId,
                    'Price'              => $dto->price,
                    'ExchangeAmount'     => $dto->exchangeAmount,
                    'ExchangeCurrencyID' => $dto->exchangeCurrencyId,
                    'ModifiedBy'         => Auth::id(),
                    'BranchID'           => $dto->branchId,
                    'PurchaseMethod'     => $dto->purchaseMethod,
                    'RowVersion'         => $sellCurrency->RowVersion + 1, // ✅ رفع النسخة
                ]);

                // 3. تحديث رأس القيد
                $glEntry->update([
                    'TheDate'    => $dto->date,
                    'Notes'      => $dto->notes,
                    'BranchID'   => $dto->branchId,
                    'RecordNumber' => $dto->theNumber,
                    'ModifiedBy' => Auth::id(),
                ]);

                // 4. تحديث التفاصيل
                $glEntry->details()->delete();
                $mappedDetails = $this->mapToEntryDetails($dto, $helperData);

                foreach ($mappedDetails as $detailData) {
                    $this->journal->addDetail($glEntry->ID, $detailData);
                }

                // 5. تطبيق التوازن النهائي
                $glEntry->load('details');
                $this->balanceSync->apply($glEntry);

                return $sellCurrency;
            } catch (Exception $e) {
                Log::error('SellCurrencyAction Update failed: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * تنفيذ عملية حذف مستند بيع العملة
     */
    public function delete(SellCurrency $sellCurrency)
    {
        return DB::transaction(function () use ($sellCurrency) {
            $sellCurrency->load('entry.details');
            $glEntry = $sellCurrency->entry;

            if ($glEntry) {
                $this->balanceSync->revert($glEntry);

                $glEntry->delete(); // ✅ Trigger SoftDeletes (sets deleted_at)

                $glEntry->update([
                    'Notes' => $glEntry->Notes . " (Deleted from Sell Currency #" . $sellCurrency->ID . ")",
                    'ModifiedBy' => Auth::id(),
                ]);
            }

            return $sellCurrency->delete();
        });
    }

    /**
     * يحول بيانات البيع إلى "أطراف قيود" محاسبية (دمج منطق المترجم)
     */
    private function mapToEntryDetails(SellCurrencyDTO $dto, array $helperData): array
    {
        $mainCurrencyId = $helperData['mainCurrencyId'];
        $rateSold = $helperData['rateSold'];
        $rateReceived = $helperData['rateReceived'];
        $currencyName = $helperData['currencyName'];

        $mcAmountSold = round($dto->amount * $rateSold, 4);
        $mcAmountReceived = round($dto->exchangeAmount * $rateReceived, 4);

        $fmtSold     = Number::format($rateSold,    maxPrecision: 8);
        $details = [];

        // 1. حساب العميل/الصندوق (المبلغ المستلم منه) - يخصم منه (-)
        $details[] = [
            'AccountID'  => $dto->fundAccountId,
            'Amount'     => -abs($dto->exchangeAmount),
            'CurrencyID' => $dto->exchangeCurrencyId,
            'MCAmount'   => -abs($mcAmountReceived),
            'Notes'      => "عليكم بيع عملة سعر {$fmtSold}",
        ];

        // 2. حساب العميل (العملة المباعة له) - تضاف إليه (+)
        $details[] = [
            'AccountID'  => $dto->accountId,
            'Amount'     => abs($dto->amount),
            'CurrencyID' => $dto->currencyId,
            'MCAmount'   => abs($mcAmountSold),
            'Notes'      => "لكم بيع عملة سعر {$fmtSold}",
        ];

        // 3. حساب "فوارق البيع" وإضافتها للطرف الموازن (حساب 4121003)
        // الموازنة: +المباع - المستلم + الفرق = 0  => الفرق = المستلم - المباع
        $difference = round(abs($mcAmountReceived) - abs($mcAmountSold), 4);
        if ($difference != 0) {
            $gainLossAccountId = Account::where('AccountNumber', '4121003')->value('ID');

            $details[] = [
                'AccountID'  => $gainLossAccountId,
                'Amount'     => $difference,
                'CurrencyID' => $mainCurrencyId,
                'MCAmount'   => $difference,
                'Notes'      => "أرباح/خسائر فروق صرف بيع عملة",
            ];
        }

        return $details;
    }
}
