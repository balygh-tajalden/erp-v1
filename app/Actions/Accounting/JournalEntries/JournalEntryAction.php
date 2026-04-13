<?php

namespace App\Actions\Accounting\JournalEntries;

use App\DTOs\Accounting\JournalEntryDTO;
use App\Models\Entry;
use App\Services\Accounting\BalanceSyncService;
use App\Services\Accounting\JournalManagerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class JournalEntryAction
{
    public function __construct(
        protected BalanceSyncService $balanceSync,
        protected JournalManagerService $journal
    ) {}

    public function create(JournalEntryDTO $dto): Entry
    {
        return DB::transaction(function () use ($dto) {
            try {
                // 1. Generate Next Sequence if not provided
                $recordNumber = $dto->theNumber ?? $this->journal->getNextSequence($dto->documentTypeId, $dto->branchId);
                $RecordID = $this->journal->getNextRecordID($dto->documentTypeId);


                // 2. Initialize the GL Entry (tblEntries)
                $glEntry = $this->journal->initialize([
                    'DocumentID'    => $dto->documentTypeId,
                    'TheDate'       => $dto->date,
                    'Notes'         => $dto->notes,
                    'BranchID'      => $dto->branchId,
                    'RecordID'      => $RecordID,
                    'RecordNumber'  => $recordNumber,
                ]);

                // 3. Map Raw Lines to Accounting Details
                $mappedLines = $this->mapToEntryLines($dto->lines, $dto->notes);

                // 4. Add Detail Lines (tblEntryDetails)
                foreach ($mappedLines as $lineData) {
                    $this->journal->addDetail($glEntry->ID, $lineData);
                }

                // 5. Post Entry (Finalize, Validate Balance, Audit Trail)
                $this->journal->post($glEntry->ID);

                // 6. Sync Account Balances
                $glEntry->load('details');
                $this->balanceSync->apply($glEntry);

                return $glEntry;
            } catch (Exception $e) {
                Log::error('JournalEntryAction Create failed: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    public function update(Entry $record, JournalEntryDTO $dto): Entry
    {
        return DB::transaction(function () use ($record, $dto) {
            try {
                // 1. Revert Old Balances
                $record->load('details');
                $this->balanceSync->revert($record);

                // 2. Update Header (tblEntries)
                $record->update([
                    'TheDate'       => $dto->date,
                    'Notes'         => $dto->notes,
                    'BranchID'      => $dto->branchId,
                    'RecordNumber'  => $dto->theNumber,
                    'ModifiedBy'    => Auth::id(),
                ]);

                // 3. Update Detail Lines (tblEntryDetails)
                $record->details()->delete();

                // 4. Map Raw Lines to Accounting Details
                $mappedLines = $this->mapToEntryLines($dto->lines, $dto->notes);

                // 5. Add new lines
                foreach ($mappedLines as $lineData) {
                    $this->journal->addDetail($record->ID, $lineData);
                }

                // 6. Sync Account Balances
                $record->load('details');
                $this->balanceSync->apply($record);

                return $record;
            } catch (Exception $e) {
                Log::error('JournalEntryAction Update failed: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * يحول بيانات الأسطر الخام من الواجهة إلى "أطراف قيود" محاسبية جاهزة للتخزين
     */
    private function mapToEntryLines(array $rawLines, string $headerNotes): array
    {
        return array_map(function ($line) use ($headerNotes) {
            $displayAmount = (float) $line['display_amount'];
            $type = $line['type'];
            $exchangeRate = (float) ($line['exchange_rate'] ?? 1.0);

            // حساب المبلغ المحاسبي (موجب للمدين، سالب للدائن)
            if ($type === 'debit') {
                $amount = -abs($displayAmount);
            } elseif($type === 'credit'){
                $amount = abs($displayAmount);
            }
            // حساب المبلغ المكافئ بالعملة المحلية
            $mcAmount = round($amount * $exchangeRate, 4);

            return [
                'AccountID'  => $line['account_id'],
                'Amount'     => $amount,
                'CurrencyID' => $line['currency_id'],
                'MCAmount'   => $mcAmount,
                'Notes'      => !empty(trim($line['line_notes'] ?? '')) ? trim($line['line_notes']) : $headerNotes,
            ];
        }, $rawLines);
    }
}
