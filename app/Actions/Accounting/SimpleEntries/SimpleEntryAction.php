<?php

namespace App\Actions\Accounting\SimpleEntries;

use App\DTOs\Accounting\SimpleEntryDTO;
use App\Models\SimpleEntry;
use App\Models\Account;
use App\Services\Accounting\BalanceSyncService;
use App\Services\Accounting\JournalManagerService;
use App\Services\System\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class SimpleEntryAction
{
    public function __construct(
        protected BalanceSyncService $balanceSync,
        protected JournalManagerService $journal,
        protected WhatsAppService $whatsapp
    ) {}

    /**
     * تنفيذ عملية إضافة مستند بيع العملة
     */
    public function create($dto, array $helperData)
    {
        return DB::transaction(function () use ($dto, $helperData) {
            try {
                // 1. إنشاء سجل بيع العملة
                $simpleEntry = SimpleEntry::create([
                    'TheNumber' => $dto->theNumber,
                    'TheDate' => $dto->date,
                    'CurrencyID' => $dto->currencyId,
                    'FromAccountID' => $dto->fromAccountId,
                    'ToAccountID' => $dto->toAccountId,
                    'Amount' => $dto->amount,
                    'Notes' => $dto->notes,
                    'CreatedBy' => Auth::id(),
                    'BranchID' => $dto->branchId,
                    'ReferenceNumber' => $dto->referenceNumber,
                    'RowVersion' => 1, 
                ]);

                // 2. تهيئة القيد المحاسبي
                $glEntry = $this->journal->initialize([
                    'DocumentID'    => $dto->documentTypeId,
                    'TheDate'       => $dto->date,
                    'Notes'         => $dto->notes,
                    'BranchID'      => $dto->branchId,
                    'RecordID'      => $simpleEntry->ID,
                    'RecordNumber'  => $dto->theNumber,
                ]);

                // 3. إضافة التفاصيل باستخدام المترجم المدمج
                $mappedDetails = $this->mapToEntryDetails($dto, $helperData);

                foreach ($mappedDetails as $detailData) {
                    $this->journal->addDetail($glEntry->ID, $detailData);
                }

                // 4. الترحيل النهائي وتحديث مستند البيع
                $this->journal->post($glEntry->ID);
                $simpleEntry->update(['EntryID' => $glEntry->ID]);

                // 5. تطبيق موازنة الأرصدة
                $glEntry->load('details');
                $this->balanceSync->apply($glEntry);

                // 6. إرسال إشعارات الواتساب
                try {
                    $this->whatsapp->sendSimpleEntryNotification($simpleEntry);
                } catch (Exception $e) {
                    Log::error('WhatsApp Notification failed: ' . $e->getMessage());
                }

                return $simpleEntry;
            } catch (Exception $e) {
                Log::error('simpleEntryAction Create failed: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * تنفيذ عملية تعديل مستند بيع العملة
     */
    public function update(SimpleEntry $simpleEntry, SimpleEntryDTO $dto, array $helperData)
    {
        return DB::transaction(function () use ($simpleEntry, $dto, $helperData) {
            try {
                // نقطة تفتيش التزامن (Optimistic Locking)
                if ($dto->rowVersion != null && $simpleEntry->RowVersion != $dto->rowVersion) {
                    throw new Exception("تنبيه أمني: تم تعديل هذا السند من قبل مستخدم آخر أثناء فتحك للشاشة. يرجى تحديث الصفحة لمنع تداخل البيانات.");
                }

                // 1. تحميل القيد والمزامنة العكسية
                $simpleEntry->load('entry.details');
                $glEntry = $simpleEntry->entry;

                if (!$glEntry) {
                    throw new Exception("القيد المحاسبي غير موجود لهذا المستند.");
                }

                $this->balanceSync->revert($glEntry);

                // 2. تحديث سجل القيود البسيطة
                $simpleEntry->update([
                    'TheNumber'          => $dto->theNumber,
                    'TheDate'            => $dto->date,
                    'FromAccountID'          => $dto->fromAccountId,
                    'ToAccountID'          => $dto->toAccountId,
                    'CurrencyID'         => $dto->currencyId,
                    'Amount'             => $dto->amount,
                    'Notes'              => $dto->notes, // ✅ تم إضافة البيان (الملاحظات) ليتم تحديثه
                    'ModifiedBy'         => Auth::id(),
                    'BranchID'           => $dto->branchId,
                    'RowVersion'         => $simpleEntry->RowVersion + 1, // ✅ رفع رقم النسخة
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


                return $simpleEntry;
            } catch (Exception $e) {
                Log::error('simpleEntryAction Update failed: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * تنفيذ عملية حذف مستند بيع العملة
     */
    public function delete(SimpleEntry $simpleEntry)
    {
        return DB::transaction(function () use ($simpleEntry) {
            $simpleEntry->load('entry.details');
            $glEntry = $simpleEntry->entry;

            if ($glEntry) {
                $this->balanceSync->revert($glEntry);

                $glEntry->delete(); // ✅ Trigger SoftDeletes (sets deleted_at)

                $glEntry->update([
                    'Notes' => $glEntry->Notes . "حذف سند رقم " . $simpleEntry->TheNumber . "",
                    'ModifiedBy' => Auth::id(),
                ]);
            }

            return $simpleEntry->delete();
        });
    }

    /**
     * يحول بيانات القيد إلى "أطراف قيود" محاسبية
     */
    private function mapToEntryDetails(SimpleEntryDTO $dto, array $helperData): array
    {
        // نستلم سعر الصرف من البيانات المساعدة (إذا العملة محلية سيكون السعر 1)
        $exchangeRate = $helperData['exchangeRate'];
        $fromName = $helperData['fromAccountName'];
        $toName = $helperData['toAccountName'];

        // حساب المبلغ المكافئ بالعملة المحلية
        $mcAmount = round($dto->amount * $exchangeRate, 4);

        $details = [];
        
        // بناء النصوص التوضيحية المستقلة لكل طرف
        $userNote = empty(trim($dto->notes)) ? "" : " - " . trim($dto->notes);
        
        // حساب "من" (العاطي/المرسِل): يُكتب في كشفه "عليكم الى حساب X"
        $fromNote = "عليكم الى حساب {$toName}{$userNote}";
        // حساب "الى" (الآخذ/المستلم): يُكتب في كشفه "لكم من حساب X"
        $toNote   = "لكم من حساب {$fromName}{$userNote}";

        // 1. الطرف المدين (إلى حساب) - حساب الآخذ/المستلِم
        $details[] = [
            'AccountID'  => $dto->toAccountId,
            'Amount'     => abs($dto->amount),
            'CurrencyID' => $dto->currencyId,
            'MCAmount'   => abs($mcAmount),
            'Notes'      => $toNote,
        ];

        // 2. الطرف الدائن (من حساب) - حساب العاطي/المرسِل
        $details[] = [
            'AccountID'  => $dto->fromAccountId,
            'Amount'     => -abs($dto->amount),
            'CurrencyID' => $dto->currencyId,
            'MCAmount'   => -abs($mcAmount),
            'Notes'      => $fromNote,
        ];


        return $details;
    }
}
