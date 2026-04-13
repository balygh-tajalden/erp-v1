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
        // Stage 4: Chart of Accounts Tables

        Schema::create('tblAccounts', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->bigInteger('FatherNumber')->nullable();
            $table->string('AccountName', 500);
            $table->bigInteger('AccountNumber')->unique();
            $table->string('AccountReference', 30)->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
            $table->bigInteger('SessionID')->nullable();
            $table->bigInteger('AccountCode')->nullable();
            $table->bigInteger('AccountTypeID');
            $table->date('ModifiedDate')->nullable();
            $table->integer('ModifiedBy')->nullable();
            $table->boolean('IsCustomer')->nullable()->default(0);
            $table->boolean('IsSupplier')->nullable()->default(0);
        });

        // Add check constraint for tblAccounts (FatherNumber <> AccountNumber)
        // Note: SQLite doesn't support complex CHECK constraints well in migrations, 
        // but since we are using MySQL (erp2026), we can use raw SQL.
        DB::statement('ALTER TABLE tblAccounts ADD CONSTRAINT CK_tblAccounts_NotSelf CHECK (FatherNumber <> AccountNumber)');

        Schema::create('tblAccountBalances', function (Blueprint $table) {
            $table->integer('AccountID');
            $table->integer('CurrencyID');
            $table->integer('BranchID');
            $table->decimal('Balance', 18, 2);
            $table->dateTime('LastUpdated')->useCurrent();
            $table->primary(['AccountID', 'CurrencyID', 'BranchID']);
        });

        Schema::create('tblAccountLimits', function (Blueprint $table) {
            $table->increments('ID');
            $table->binary('RowVersion')->nullable();
            $table->integer('GroupID')->nullable();
            $table->bigInteger('AccountID')->nullable();
            $table->bigInteger('BranchID');
            $table->decimal('Amount', 18, 2);
            $table->integer('CurrencyID');
            $table->string('Notes', 500)->nullable();
            $table->boolean('IsActive')->default(1);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedDate')->useCurrent();
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblAccountLimits');
        Schema::dropIfExists('tblAccountBalances');
        Schema::dropIfExists('tblAccounts');
    }
};
