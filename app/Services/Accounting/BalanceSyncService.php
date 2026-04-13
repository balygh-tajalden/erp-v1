<?php

namespace App\Services\Accounting;

use App\Models\Entry;
use App\Models\AccountBalance;
use Illuminate\Support\Facades\DB;
use Exception;

class BalanceSyncService
{
    /**
     * Reverts the financial impact of an entry from the account balances.
     */
    public function revert(Entry $entry)
    {
        $this->sync($entry, true);
    }

    /**
     * Applies the financial impact of an entry to the account balances.
     */
    public function apply(Entry $entry)
    {
        $this->sync($entry, false);
    }

    /**
     * Core logic to sync balances.
     * 
     * @param Entry $entry
     * @param bool $reverse If true, subtracts amounts (revert). If false, adds them (apply).
     */
    protected function sync(Entry $entry, bool $reverse = false)
    {
        // We only sync the current state of details.
        // If the caller needs to revert trashed ones, they should load them before calling this.
        if (!$entry->relationLoaded('details')) {
            $entry->load('details');
        }

        if ($entry->details->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($entry, $reverse) {
            foreach ($entry->details as $detail) {
                $amount = $reverse ? -$detail->Amount : $detail->Amount;
                
                $this->updateSingleBalance(
                    $detail->AccountID,
                    $detail->CurrencyID,
                    $entry->BranchID, // Branch is on the Entry header
                    $amount
                );
            }
        });
    }

    /**
     * Update or create a balance record atomically.
     */
    public function updateSingleBalance($accountId, $currencyId, $branchId, $amount)
    {
        if ($amount == 0) return;

        // Use a raw query for "Insert or Update" atomicity, similar to the trigger.
        // This is the safest way to handle concurrent balance updates.
        $query = "
            INSERT INTO tblAccountBalances (AccountID, CurrencyID, BranchID, Balance, LastUpdated)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                Balance = Balance + VALUES(Balance),
                LastUpdated = NOW()
        ";

        DB::statement($query, [$accountId, $currencyId, $branchId, $amount]);
    }
}
