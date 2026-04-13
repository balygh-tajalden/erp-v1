<?php

namespace App\Observers;

use App\Models\EntryDetail;
use App\Services\Accounting\BalanceSyncService;

class EntryDetailObserver
{
    public function __construct(protected BalanceSyncService $balanceSync) {}

    /**
     * Handle the EntryDetail "created" event.
     */
    public function created(EntryDetail $detail)
    {
        // Use the protected update method via a helper or make it public in service
        // For simplicity, let's add a public method to the service or just call a generic sync.
        if ($detail->entry && $detail->entry->IsPosted && !$detail->entry->isDeleted) {
            $this->applyDetail($detail);
        }
    }

    /**
     * Handle the EntryDetail "updated" event.
     */
    public function updated(EntryDetail $detail)
    {
        if ($detail->entry && $detail->entry->IsPosted && !$detail->entry->isDeleted) {
            // 1. Revert Old State
            $this->revertDetail($detail, true);
            // 2. Apply New State
            $this->applyDetail($detail);
        }
    }

    /**
     * Handle the EntryDetail "deleted" event.
     */
    public function deleted(EntryDetail $detail)
    {
        if ($detail->entry && $detail->entry->IsPosted && !$detail->entry->isDeleted) {
            $this->revertDetail($detail);
        }
    }

    protected function applyDetail(EntryDetail $detail)
    {
        $this->balanceSync->updateSingleBalance(
            $detail->AccountID,
            $detail->CurrencyID,
            $detail->entry->BranchID,
            $detail->Amount
        );
    }

    protected function revertDetail(EntryDetail $detail, bool $useOriginal = false)
    {
        $accountId = $useOriginal ? $detail->getOriginal('AccountID') : $detail->AccountID;
        $currencyId = $useOriginal ? $detail->getOriginal('CurrencyID') : $detail->CurrencyID;
        $amount = $useOriginal ? $detail->getOriginal('Amount') : $detail->Amount;
        
        $this->balanceSync->updateSingleBalance(
            $accountId,
            $currencyId,
            $detail->entry->BranchID,
            -$amount
        );
    }
}
