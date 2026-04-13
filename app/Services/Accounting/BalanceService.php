<?php

namespace App\Services\Accounting;

use App\Services\BaseService;
use App\Models\EntryDetail;
use App\Models\Entry;
use Illuminate\Support\Facades\DB;

class BalanceService extends BaseService
{
    /**
     * Rebuilds the tblAccountBalances table from scratch based on tblEntryDetails.
     * Useful for "Zeroing" and ensuring absolute data consistency.
     */
    public function recalculateBalances()
    {
        return DB::transaction(function () {
            // 1. Clear existing balances
            DB::table('tblAccountBalances')->delete();

            // 2. Aggregate from posted, non-deleted details
            $balances = EntryDetail::query()
                ->join('tblEntries', 'tblEntryDetails.ParentID', '=', 'tblEntries.ID')
                ->where('tblEntries.IsPosted', 1)
                ->where('tblEntries.isDeleted', 0)
                ->select([
                    'tblEntryDetails.AccountID',
                    'tblEntryDetails.CurrencyID',
                    'tblEntries.BranchID',
                    DB::raw('SUM(tblEntryDetails.Amount) as TotalBalance')
                ])
                ->groupBy(['tblEntryDetails.AccountID', 'tblEntryDetails.CurrencyID', 'tblEntries.BranchID'])
                ->get();

            // 3. Batch insert new balances
            foreach ($balances as $row) {
                DB::table('tblAccountBalances')->insert([
                    'AccountID'   => $row->AccountID,
                    'CurrencyID'  => $row->CurrencyID,
                    'BranchID'    => $row->BranchID,
                    'Balance'     => $row->TotalBalance,
                    'LastUpdated' => now(),
                ]);
            }

            return count($balances);
        });
    }

    /**
     * حساب الرصيد التاريخي الموحد للحساب (بالمكافئ المحلي)
     * يستفيد من الـ View الجاهز: vw_AccountStatementSummary
     */
    public function getHistoricalConsolidatedBalance(int $accountId): float
    {
        return (float) DB::table('vw_AccountStatementSummary')
            ->where('AccountID', $accountId)
            ->sum('المبلغ المكافئ');
    }
}
