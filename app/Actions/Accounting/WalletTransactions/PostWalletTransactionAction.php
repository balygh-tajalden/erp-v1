<?php

namespace App\Actions\Accounting\WalletTransactions;

use App\DTOs\Accounting\WalletTransactionDTO;
use App\Models\WalletTransaction;
use App\Models\Account;
use App\Services\Accounting\BalanceSyncService;
use App\Services\Accounting\JournalManagerService;
use App\Services\System\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PostWalletTransactionAction
{
    public function __construct(
        protected BalanceSyncService $balanceSync,
        protected JournalManagerService $journal,
        protected WhatsAppService $whatsapp
    ) {}

    /**
     * ترحيل عملية محفظة إلى القيود المحاسبية
     */
    public function execute(WalletTransaction $transaction, WalletTransactionDTO $dto)
    {
        return DB::transaction(function () use ($transaction, $dto) {
            try {
                // 1. تهيئة القيد المحاسبي (DocumentTypeID 13 = المحافظ الإلكترونية)
                $glEntry = $this->journal->initialize([
                    'DocumentID'    => $dto->documentTypeId, 
                    'TheDate'       => $dto->date,
                    'Notes'         => $dto->notes,
                    'BranchID'      => $dto->branchId,
                    'RecordID'      => $transaction->ID,
                    'RecordNumber'  => $this->journal->getNextSequence($dto->documentTypeId, $dto->branchId),
                    'SessionID'     => $dto->sessionId,
                ]);

                // 2. بناء تفاصيل القيد (Debits/Credits)
                $details = $this->mapToEntryDetails($transaction, $dto);

                foreach ($details as $detailData) {
                    $this->journal->addDetail($glEntry->ID, $detailData);
                }

                // 3. الترحيل النهائي وتحديث المحفظة
                $this->journal->post($glEntry->ID);
                
                $transaction->update([
                    'AccountID' => $dto->accountId,
                    'IsPosted'  => true,
                    'EntryID'   => $glEntry->ID,
                    'ModifiedBy' => Auth::id(),
                    'Notes'      => $dto->notes,
                ]);

                // 4. تطبيق موازنة الأرصدة فوراً
                $glEntry->load('details');
                $this->balanceSync->apply($glEntry);

                // 5. إشعارات الواتساب (اختياري حسب توافر الدالة)
                try {
                    // سنقوم بإضافة هذه الدالة في الخطوة القادمة
                    if (method_exists($this->whatsapp, 'sendWalletTransactionNotification')) {
                        $this->whatsapp->sendWalletTransactionNotification($transaction);
                    }
                } catch (Exception $e) {
                    Log::error('WhatsApp Notification failed for Wallet Transaction: ' . $e->getMessage());
                }

                return $transaction;
            } catch (Exception $e) {
                Log::error('PostWalletTransactionAction failed: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * رسم خريطة الحسابات (من و إلى)
     */
    private function mapToEntryDetails(WalletTransaction $transaction, WalletTransactionDTO $dto): array
    {
        $details = [];
        $isIngoing = $transaction->IsIngoing;
        $amount = abs($dto->amount);
        
        // حساب سعر الصرف والمكافئ المحلي (الفحص مركزياً في EntryValidationService)
        $accountingService = app(\App\Services\AccountingService::class);
        $mainCurrencyId = $accountingService->getMainCurrencyId();
        $exchangeRate = $accountingService->getExchangeRate($dto->currencyId, $mainCurrencyId);
        $mcAmount = round($amount * $exchangeRate, 4);
        
        // جلب أسماء الحسابات للبيان
        $customerName = Account::where('ID', $dto->accountId)->value('AccountName');
        $fundName = Account::where('ID', $dto->fundAccountId)->value('AccountName');
        
        $baseNote = $dto->notes;

        if ($isIngoing) {
            // حالة إيداع (وصول USDT إلى المحفظة من العميل):
            // 1. حساب العميل: يضاف له الرصيد (موجب / لكم)
            $details[] = [
                'AccountID'  => $dto->accountId,
                'Amount'     => $amount,
                'CurrencyID' => $dto->currencyId,
                'MCAmount'   => $mcAmount,
                'Notes'      => "لكم إيداع USDT عبر المحفظة - {$baseNote}",
            ];

            // 2. حساب الصندوق (المحفظة): (سالب / عليكم)
            $details[] = [
                'AccountID'  => $dto->fundAccountId,
                'Amount'     => -$amount,
                'CurrencyID' => $dto->currencyId,
                'MCAmount'   => -$mcAmount,
                'Notes'      => "عليكم الى حساب {$customerName} - {$baseNote}",
            ];
        } else {
            // حالة سحب (إرسال USDT من المحفظة إلى العميل):
            // 1. حساب العميل: يخصم من رصيده (سالب / عليكم)
            $details[] = [
                'AccountID'  => $dto->accountId,
                'Amount'     => -$amount,
                'CurrencyID' => $dto->currencyId,
                'MCAmount'   => -$mcAmount,
                'Notes'      => "عليكم سحب USDT مقيدة على حسابكم - {$baseNote}",
            ];

            // 2. حساب الصندوق (المحفظة): يضاف له المقابل لقاء خصم العميل (موجب / لكم)
            $details[] = [
                'AccountID'  => $dto->fundAccountId,
                'Amount'     => $amount,
                'CurrencyID' => $dto->currencyId,
                'MCAmount'   => $mcAmount,
                'Notes'      => "لكم سحب خصماً من حساب {$customerName} - {$baseNote}",
            ];
        }

        return $details;
    }

    /**
     * إلغاء الترحيل (Unpost)
     */
    public function unpost(WalletTransaction $transaction)
    {
        return DB::transaction(function () use ($transaction) {
            if ($transaction->EntryID) {
                $glEntry = \App\Models\Entry::with('details')->find($transaction->EntryID);
                
                if ($glEntry) {
                    // 1. عكس الأرصدة
                    $this->balanceSync->revert($glEntry);

                    // 2. حذف نهائي للقيد من السجل
                    $glEntry->forceDelete(); 
                }
            }

            // 3. تحديث حالة الحركة لتصبح غير مرحلة
            $transaction->update([
                'IsPosted' => false,
                'EntryID' => null,
                'ModifiedBy' => Auth::id(),
            ]);

            return $transaction;
        });
    }
}
