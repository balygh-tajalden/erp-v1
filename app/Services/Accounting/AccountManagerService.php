<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\EntryDetail;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * AccountManagerService: Handles modular Account creation and mapping.
 */
class AccountManagerService extends BaseService
{
    /**
     * 1. Validate Parent
     */
    public function validateParent($parentNumber)
    {
        $parent = Account::where('AccountNumber', $parentNumber)->first();
        if (!$parent) {
            throw new Exception("Parent account not found.");
        }
        
        // Custom logic: Only specific types can have children
        if ($parent->AccountTypeID != 1) { // 1 = الرئيسي
            throw new Exception("Cannot add children to a sub-account.");
        }

        return $parent;
    }

    /**
     * 3. Persist and Initialize
     */
    public function create(array $data)
    {
        $parent = $this->validateParent($data['FatherNumber']);
        $newNumber = Account::generateNextChildNumber($data['FatherNumber']);

        return DB::transaction(function () use ($data, $newNumber, $parent) {
            $account = Account::create([
                'AccountNumber'    => $newNumber,
                'AccountName'      => $data['AccountName'],
                'FatherNumber'     => $data['FatherNumber'],
                'AccountTypeID'    => $data['AccountTypeID'] , 
                'AccountReference' => $parent->AccountReference,
                'BranchID'         => $data['BranchID'] , 
                'CreatedBy'        => Auth::id(), 
                'CreatedDate'      => now(),
                'AccountCode'      => $newNumber,
            ]);

            return $account;
        });
    }

    /**
     * 8. Is Account Deletable?
     */
    public function isDeletable($accountId)
    {
        // Check if has children
        if (Account::where('FatherNumber', function($q) use ($accountId) {
            $q->select('AccountNumber')->from('tblAccounts')->where('ID', $accountId);
        })->exists()) {
            return false;
        }

        // Check if has transactions
        if (EntryDetail::where('AccountID', $accountId)->exists()) {
            return false;
        }

        return true;
    }
}
