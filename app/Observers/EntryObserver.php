<?php

namespace App\Observers;

use App\Models\Entry;
use App\Services\Accounting\BalanceSyncService;
use Illuminate\Support\Facades\Log;

class EntryObserver
{
    public function __construct(protected BalanceSyncService $balanceSync) {}

    /**
     * Handle the Entry "updated" event.
     */
    public function updated(Entry $entry)
    {
        // 1. Handle Posting Transition (0 -> 1)
        if ($entry->wasChanged('IsPosted') && $entry->IsPosted && !$entry->isDeleted && !$entry->deleted_at) {
            $this->balanceSync->apply($entry);
            return;
        }

        // 2. Handle Unposting Transition (1 -> 0)
        if ($entry->wasChanged('IsPosted') && !$entry->IsPosted && $entry->getOriginal('IsPosted')) {
            $this->balanceSync->revert($entry);
            return;
        }

        // 3. Handle Soft Delete (IsPosted = 1 AND isDeleted becomes 1)
        if (($entry->wasChanged('isDeleted') && $entry->isDeleted && $entry->IsPosted) || 
            ($entry->wasChanged('deleted_at') && $entry->deleted_at && $entry->IsPosted)) {
            $this->balanceSync->revert($entry);
            return;
        }

        // 4. Handle Restore (IsPosted = 1 AND isDeleted becomes 0)
        if (($entry->wasChanged('isDeleted') && !$entry->isDeleted && $entry->getOriginal('isDeleted') && $entry->IsPosted) ||
            ($entry->wasChanged('deleted_at') && !$entry->deleted_at && $entry->getOriginal('deleted_at') && $entry->IsPosted)) {
            $this->balanceSync->apply($entry);
            return;
        }

        // 5. Handle Header Changes (Branch) for already posted entries
        if ($entry->IsPosted && $entry->wasChanged('BranchID')) {
            $oldBranchId = $entry->getOriginal('BranchID');
            
            // Revert from old branch, apply to new branch
            $entry->load('details');
            foreach ($entry->details as $detail) {
                // Subtract from old branch
                $this->balanceSync->updateSingleBalance(
                    $detail->AccountID,
                    $detail->CurrencyID,
                    $oldBranchId,
                    -$detail->Amount
                );
                // Add to new branch
                $this->balanceSync->updateSingleBalance(
                    $detail->AccountID,
                    $detail->CurrencyID,
                    $entry->BranchID,
                    $detail->Amount
                );
            }
        }
    }

    /**
     * Handle the Entry "deleted" event (Hard Delete or Soft Delete if caught here).
     */
    public function deleted(Entry $entry)
    {
        if ($entry->IsPosted) {
            $this->balanceSync->revert($entry);
        }
    }

    /**
     * Handle the Entry "restored" event.
     */
    public function restored(Entry $entry)
    {
        if ($entry->IsPosted) {
            $this->balanceSync->apply($entry);
        }
    }
}
