<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old strict unique indexes for Name and Number
        try {
            Schema::table('tblAccounts', function (Blueprint $table) {
                // MySQL index name from original migration for AccountNumber
                $table->dropUnique(['AccountNumber']);
                
                // Explicitly named index for AccountName
                $table->dropIndex('idx_unique_account_name');
            });
        } catch (\Exception $e) {
            // Some indexes might not exist or have different names
        }

        // Create new functional unique indexes for MySQL 8.0.13+
        // These only consider active records (where deleted_at is NULL)
        DB::statement('CREATE UNIQUE INDEX idx_unique_account_name_active ON tblAccounts (AccountName, (CASE WHEN deleted_at IS NULL THEN 1 ELSE NULL END))');
        DB::statement('CREATE UNIQUE INDEX idx_unique_account_number_active ON tblAccounts (AccountNumber, (CASE WHEN deleted_at IS NULL THEN 1 ELSE NULL END))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('tblAccounts', function (Blueprint $table) {
                $table->dropIndex('idx_unique_account_name_active');
                $table->dropIndex('idx_unique_account_number_active');
            });
        } catch (\Exception $e) {}

        Schema::table('tblAccounts', function (Blueprint $table) {
            $table->unique('AccountName', 'idx_unique_account_name');
            $table->unique('AccountNumber', 'tblaccounts_accountnumber_unique');
        });
    }
};
