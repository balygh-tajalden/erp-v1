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
        Schema::create('tblWalletTransactions', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('WalletAddress', 255)->nullable();
            $table->string('TransactionHash', 255)->nullable();
            $table->bigInteger('LogIndex')->nullable();
            $table->bigInteger('BlockNumber')->nullable();
            $table->dateTime('BlockTimestamp')->nullable();
            $table->string('FromAddress', 255)->nullable();
            $table->string('ToAddress', 255)->nullable();
            $table->decimal('Amount', 36, 18)->nullable();
            $table->string('TokenAddress', 255)->nullable();
            $table->string('TokenSymbol', 50)->nullable();
            $table->string('TokenName', 100)->nullable();
            $table->boolean('IsIngoing')->default(1);
            $table->string('Chain', 50)->nullable();
            $table->bigInteger('AccountID')->nullable();
            $table->integer('CurrencyID')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->bigInteger('EntryID')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->integer('SessionID')->nullable();
            $table->boolean('IsPosted')->default(0);
            $table->boolean('IsDeleted')->default(0);
            $table->text('Notes')->nullable();
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
            $table->dateTime('ModifiedDate')->nullable();
            $table->integer('ModifiedBy')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblWalletTransactions');
    }
};
