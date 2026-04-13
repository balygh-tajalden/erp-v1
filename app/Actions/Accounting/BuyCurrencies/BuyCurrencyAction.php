<?php

namespace App\Actions\Accounting\BuyCurrencies;

use App\DTOs\Accounting\BuyCurrencyDTO;
use App\Models\BuyCurrency;
use App\Models\Account;
use App\Services\Accounting\BalanceSyncService;
use App\Services\Accounting\JournalManagerService;
use App\Services\System\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Exception;

class BuyCurrencyAction
{
    public function __construct(
        protected BalanceSyncService $balanceSync,
        protected JournalManagerService $journal,
        protected WhatsAppService $whatsapp
    ) {}

    /**
     * تنفيذ عملية إضافة مستند بيع العملة
     */
    public function create(BuyCurrencyDTO $dto, array $helperData)
    {
        return DB::transaction(function () use ($dto, $helperData) {
            try {
                // 1. إنشاء سجل بيع العملة
                $buyCurrency = BuyCurrency::create([
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
                    'RecordID'      => $buyCurrency->ID,
                    'RecordNumber'  => $dto->theNumber,
                ]);

                // 3. إضافة التفاصيل باستخدام المترجم المدمج
                $mappedDetails = $this->mapToEntryDetails($dto, $helperData);

                foreach ($mappedDetails as $detailData) {
                    $this->journal->addDetail($glEntry->ID, $detailData);
                }

                // 4. الترحيل النهائي وتحديث مستند البيع
                $this->journal->post($glEntry->ID);
                $buyCurrency->update(['EntryID' => $glEntry->ID]);

                // 5. تطبيق موازنة الأرصدة
                $glEntry->load('details');
                $this->balanceSync->apply($glEntry);

                // 6. إرسال إشعارات الواتساب
                try {
                    $this->whatsapp->sendBuyCurrencyNotification($buyCurrency);
                } catch (Exception $e) {
                    Log::error('WhatsApp Notification failed: ' . $e->getMessage());
                }

                return $buyCurrency;
            } catch (Exception $e) {
                Log::error('buyCurrencyAction Create failed: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * تنفيذ عملية تعديل مستند بيع العملة
     */
    public function update(BuyCurrency $buyCurrency, BuyCurrencyDTO $dto, array $helperData)
    {
        return DB::transaction(function () use ($buyCurrency, $dto, $helperData) {
            try {
                // 🔒 نقطة تفتيش التزامن (Optimistic Locking)
                if ($dto->rowVersion != null && $buyCurrency->RowVersion != $dto->rowVersion) {
                    throw new Exception("تنبيه أمني: تم تعديل هذا السند من قبل مستخدم آخر أثناء فتحك للشاشة. يرجى تحديث الصفحة لمنع تداخل البيانات.");
                }

                // 1. تحميل القيد والمزامنة العكسية
                $buyCurrency->load('entry.details');
                $glEntry = $buyCurrency->entry;

                if (!$glEntry) {
                    throw new Exception("القيد المحاسبي غير موجود لهذا المستند.");
                }

                $this->balanceSync->revert($glEntry);

                // 2. تحديث سجل بيع العملة
                $buyCurrency->update([
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
                    'RowVersion'         => $buyCurrency->RowVersion + 1, // ✅ رفع النسخة
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

                return $buyCurrency;
            } catch (Exception $e) {
                Log::error('buyCurrencyAction Update failed: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * تنفيذ عملية حذف مستند بيع العملة
     */
    public function delete(BuyCurrency $buyCurrency)
    {
        return DB::transaction(function () use ($buyCurrency) {
            $buyCurrency->load('entry.details');
            $glEntry = $buyCurrency->entry;

            if ($glEntry) {
                $this->balanceSync->revert($glEntry);

                $glEntry->delete();

                $glEntry->update([
                    'Notes' => $glEntry->Notes . " (Deleted from Buy Currency #" . $buyCurrency->ID . ")",
                    'ModifiedBy' => Auth::id(),
                ]);
            }

            return $buyCurrency->delete();
        });
    }

    /**
     * يحول بيانات الشراء إلى "أطراف قيود" محاسبية
     */
    private function mapToEntryDetails(BuyCurrencyDTO $dto, array $helperData): array
    {
        $mainCurrencyId = $helperData['mainCurrencyId'];
        $rateBought  = $helperData['rateBought'];
        $rateReceived = $helperData['rateReceived'];
        $currencyName = $helperData['currencyName'];

        $mcAmountBought = round($dto->amount * $rateBought, 4);
        $mcAmountReceived = round($dto->exchangeAmount * $rateReceived, 4);

        $fmtBought     = Number::format($rateBought,    maxPrecision: 8);

        $details = [];

        // 1. حساب العميل (العملة المشتراة منه) - تخصم منه (-)
        $details[] = [
            'AccountID'  => $dto->accountId,
            'Amount'     => -abs($dto->amount),
            'CurrencyID' => $dto->currencyId,
            'MCAmount'   => -abs($mcAmountBought),
            'Notes'      => "عليكم شراء عملة سعر {$fmtBought}",
        ];

        // 2. حساب العميل/الصندوق (العملة المدفوعة له) - تضاف إليه (+)
        $details[] = [
            'AccountID'  => $dto->fundAccountId,
            'Amount'     => abs($dto->exchangeAmount),
            'CurrencyID' => $dto->exchangeCurrencyId,
            'MCAmount'   => abs($mcAmountReceived),
            'Notes'      => "لكم شراء عملة سعر {$fmtBought}",
        ];

        // 3. حساب "فوارق الشراء" وإضافتها للطرف الموازن (حساب 4121004)
        // الموازنة: -المشترى + المدفوع + الفرق = 0  => الفرق = المشترى - المدفوع
        $difference = round(abs($mcAmountBought) - abs($mcAmountReceived), 4);

        if ($difference != 0) {
            $gainLossAccountId = Account::where('AccountNumber', '4121004')->value('ID');

            $details[] = [
                'AccountID'  => $gainLossAccountId,
                'Amount'     => $difference,
                'CurrencyID' => $mainCurrencyId,
                'MCAmount'   => $difference,
                'Notes'      => "أرباح/خسائر فروق صرف شراء عملة",
            ];
        }

        return $details;
    }
}
