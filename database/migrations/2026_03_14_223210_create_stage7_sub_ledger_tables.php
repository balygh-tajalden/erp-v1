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
        // Stage 7: Sub-Ledgers Tables

        Schema::create('tblCustRecv', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->binary('RowVersion', 8);
            $table->bigInteger('TheNumber');
            $table->unsignedBigInteger('AccountID');
            $table->unsignedBigInteger('FundAccountID');
            $table->date('TheDate');
            $table->decimal('Amount', 18, 4);
            $table->decimal('ExchangeAmount', 18, 4)->nullable();
            $table->integer('CurrencyID');
            $table->integer('ExchangeCurrencyID')->nullable();
            $table->unsignedBigInteger('EntryID')->nullable();
            $table->string('Handling', 250)->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('CreatedDate')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            $table->bigInteger('SessionID')->nullable();
            $table->boolean('IsReversed')->nullable();
            $table->boolean('isDeleted')->default(0);
            $table->integer('Year')->storedAs('YEAR(TheDate)');
            $table->string('ReferenceNumber', 250)->nullable();

            $table->foreign('EntryID')->references('ID')->on('tblEntries')->onDelete('set null');
        });

        Schema::create('tblSimpleEntries', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->binary('RowVersion', 8);
            $table->bigInteger('TheNumber');
            $table->unsignedBigInteger('FromAccountID');
            $table->unsignedBigInteger('ToAccountID');
            $table->date('TheDate');
            $table->decimal('Amount', 18, 4);
            $table->integer('CurrencyID');
            $table->unsignedBigInteger('EntryID')->nullable();
            $table->string('Notes', 255)->nullable();
            $table->integer('CreatedBy');
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('CreatedDate')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            $table->bigInteger('SessionID')->nullable();
            $table->boolean('IsReversed')->nullable();
            $table->boolean('isDeleted')->nullable()->default(0);
            $table->integer('Year')->storedAs('YEAR(TheDate)');
            $table->string('ReferenceNumber', 250)->nullable();

            $table->foreign('EntryID')->references('ID')->on('tblEntries')->onDelete('set null');
        });

        Schema::create('tblCustPay', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->binary('RowVersion', 8);
            $table->bigInteger('TheNumber');
            $table->unsignedBigInteger('AccountID');
            $table->unsignedBigInteger('FundAccountID');
            $table->date('TheDate');
            $table->decimal('Amount', 18, 4);
            $table->integer('CurrencyID');
            $table->unsignedBigInteger('EntryID')->nullable();
            $table->string('Handling', 250)->nullable();
            $table->integer('CreatedBy');
            $table->decimal('ExchangeAmount', 18, 4)->nullable();
            $table->integer('ExchangeCurrencyID')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('CreatedDate')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            $table->boolean('IsReversed')->nullable();
            $table->boolean('isDeleted')->nullable()->default(0);
            $table->integer('Year')->storedAs('YEAR(TheDate)');
            $table->string('ReferenceNumber', 250)->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('SessionID')->nullable();

            $table->foreign('EntryID')->references('ID')->on('tblEntries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblCustPay');
        Schema::dropIfExists('tblSimpleEntries');
        Schema::dropIfExists('tblCustRecv');
    }
};
