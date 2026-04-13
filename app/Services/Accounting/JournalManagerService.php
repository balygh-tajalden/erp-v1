<?php

namespace App\Services\Accounting;

use App\Models\Entry;
use App\Models\EntryDetail;
use App\Models\History;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Events\Accounting\JournalEntryPosted;
use Illuminate\Support\Facades\Auth;
use App\Services\AccountingService;
use App\Events\Accounting\JournalEntryRolledBack;
/**
 * JournalManagerService: Handles the modular lifecycle of Journal Entries.
 */
class JournalManagerService extends BaseService
{
    /**
     * 1. Initialize Record (tblEntries)
     */
    public function initialize(array $data)
    {
        $sessionID = $data['SessionID'] ?? resolve(AccountingService::class)->getCurrentSessionID();
        
        return Entry::create([
            'DocumentID'    => $data['DocumentID'],
            'TheDate'       => $data['TheDate'] ?? now(),
            'Notes'         => $data['Notes'] ?? null,
            'BranchID'      => $data['BranchID'] ,
            'CreatedBy'     => Auth::id(),
            'IsPosted'      => 0,
            'isDeleted'     => 0,
            'RecordID'      => $data['RecordID'],
            'RecordNumber'  => $data['RecordNumber'] ,
        ]);
    }

    /**
     * 2. Add Detail Line (tblEntryDetails)
     */
    public function addDetail($entryId, array $detail)
    {
        return EntryDetail::create([
            'ParentID'   => $entryId,
            'AccountID'  => $detail['AccountID'],
            'Amount'     => $detail['Amount'],
            'CurrencyID' => $detail['CurrencyID'],
            'MCAmount'   => $detail['MCAmount'] ,
            'Notes'      => $detail['Notes'] ?? null,
            'CreatedBy'  => Auth::id(),
        ]);
    }

    /**
     * 3. Validate Balance
     */
    public function validateBalance($entryId)
    {
        return app(EntryValidationService::class)->validate($entryId);
    }

    /**
     * 4. Post Entry (Finalize)
     */
    public function post($entryId, $checkDuplicate = true)
    {
        return DB::transaction(function () use ($entryId, $checkDuplicate) {
            $entry = Entry::with('details')->findOrFail($entryId);
            
            $validator = app(EntryValidationService::class);
            $validator->validate($entryId);

            if ($checkDuplicate) {
                $validator->validateDuplicate($entryId);
            }

            // Update Entry Status
            $entry->update([
                'IsPosted' => 1,
                'ModifiedDate' => now(),
                'ModifiedBy' => Auth::id(),
            ]);

            // Fire Event: Let Listeners handle side-effects like balance sync
            JournalEntryPosted::dispatch($entry);

            return $entry;
        });
    }


    /**
     * 9. Get Next Sequence (الرقم التالي)
     */
    public function getNextSequence($docType, $branchId, $year = null)
    {
        $year = $year ?? now()->year;

        $lastNumber = Entry::where('DocumentID', $docType)
            ->where('BranchID', $branchId)
            ->whereYear('TheDate', $year)
            ->withTrashed()
            ->max('RecordNumber');
        
        return ($lastNumber ?? 0) + 1;
    }
    /**
     * لايتم استخدامها الا لسند قيد مزدوج
     */
    public function getNextRecordID($docType,$year = null)
    {
        $year = $year ?? now()->year;

        $lastNumber = Entry::where('DocumentID', $docType)
            ->whereYear('TheDate', $year)
            ->withTrashed()
            ->max('RecordID');
        
        return ($lastNumber ?? 0) + 1;
    }

    /**
     * 11. Is Entry Balanced? (Check array before save)
     */
    public function isArrayBalanced(array $details)
    {
        $sum = collect($details)->sum('Amount');
        return round($sum, 4) === 0.0000;
    }
}
