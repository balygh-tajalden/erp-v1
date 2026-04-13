<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'tblBranches',
            'tblOperations',
            'tblDocumentTypes',
            'tblAccountTypes',
            'tblGroupTypes',
            'tblUserGroups',
            'tblUsers',
            'tblCurrencies',
            'tblAccounts',
            'tblEntries',
            'tblEntryDetails',
            'tblCustomers',
            'tblPackages',
            'tblCustRecv',
            'tblSimpleEntries',
            'tblCustPay',
            'tblItems',
            'tblInvoices',
            'tblBuyCurrencies',
            'tblSellCurrencies',
            'tblGroups',
            'tblGroupTypesPurposes'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'tblGroupTypesPurposes',
            'tblGroups',
            'tblSellCurrencies',
            'tblBuyCurrencies',
            'tblInvoices',
            'tblItems',
            'tblCustPay',
            'tblSimpleEntries',
            'tblCustRecv',
            'tblPackages',
            'tblCustomers',
            'tblEntryDetails',
            'tblEntries',
            'tblAccounts',
            'tblCurrencies',
            'tblUsers',
            'tblUserGroups',
            'tblGroupTypes',
            'tblAccountTypes',
            'tblDocumentTypes',
            'tblOperations',
            'tblBranches'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
