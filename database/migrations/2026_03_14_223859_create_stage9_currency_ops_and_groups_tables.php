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
        // Stage 9: Currency Operations & Specialized Modules

        Schema::create('tblGroupTypesPurposes', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('PurposeName', 100);
            $table->unsignedInteger('GroupTypeID')->nullable();
            $table->boolean('IsExclusive')->default(0);
            $table->string('Notes', 200)->nullable();
            $table->unsignedInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->nullable();
            $table->unsignedInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();

            $table->foreign('GroupTypeID')->references('ID')->on('tblGroupTypes');
        });

        Schema::create('tblGroups', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('GroupName', 100);
            $table->unsignedInteger('GroupTypeID')->nullable();
            $table->unsignedInteger('PurposeID');
            $table->string('Notes', 200)->nullable();
            $table->unsignedInteger('CreatedBy')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->dateTime('CreatedDate')->useCurrent();
            $table->unsignedInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();

            $table->foreign('GroupTypeID')->references('ID')->on('tblGroupTypes');
            $table->foreign('PurposeID')->references('ID')->on('tblGroupTypesPurposes');
        });

        Schema::create('tblBuyCurrencies', function (Blueprint $table) {
            $table->increments('ID');
            $table->binary('RowVersion', 8);
            $table->integer('Thenumber');
            $table->date('TheDate')->nullable();
            $table->unsignedBigInteger('AccountID')->nullable();
            $table->unsignedBigInteger('FundAccountID')->nullable();
            $table->integer('CurrencyID')->nullable();
            $table->integer('ExchangeCurrencyID')->nullable();
            $table->decimal('Amount', 18, 4)->nullable();
            $table->decimal('Price', 18, 6)->nullable();
            $table->decimal('ExchangeAmount', 18, 4)->nullable();
            $table->string('PurchaseMethod', 20);
            $table->string('Notes', 500)->nullable();
            $table->integer('CreatedBy');
            $table->dateTime('CreatedDate')->useCurrent();
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            $table->integer('BranchID')->nullable();
            $table->unsignedBigInteger('EntryID')->nullable();
            $table->integer('SessionID')->nullable();
            $table->decimal('CommissionAmount', 18, 4)->nullable();
            $table->integer('CommissionCurrencyID')->nullable();
            $table->boolean('IsDeleted')->default(0);
            $table->boolean('IsReversed')->default(0);
            $table->integer('Year')->storedAs('YEAR(TheDate)');
            $table->string('ReferenceNumber', 250)->nullable();

            $table->foreign('AccountID')->references('ID')->on('tblAccounts');
            $table->foreign('EntryID')->references('ID')->on('tblEntries')->onDelete('set null');
        });

        Schema::create('tblSellCurrencies', function (Blueprint $table) {
            $table->increments('ID');
            $table->binary('RowVersion', 8);
            $table->integer('Thenumber');
            $table->date('TheDate')->nullable();
            $table->unsignedBigInteger('AccountID')->nullable();
            $table->unsignedBigInteger('FundAccountID')->nullable();
            $table->integer('CurrencyID')->nullable();
            $table->integer('ExchangeCurrencyID')->nullable();
            $table->decimal('Amount', 18, 4)->nullable();
            $table->decimal('Price', 18, 6)->nullable();
            $table->decimal('ExchangeAmount', 18, 4)->nullable();
            $table->string('PurchaseMethod', 20);
            $table->boolean('IsDeleted')->default(0);
            $table->boolean('IsReversed')->default(0);
            $table->string('Notes', 500)->nullable();
            $table->integer('CreatedBy');
            $table->dateTime('CreatedDate')->useCurrent();
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            $table->integer('BranchID')->nullable();
            $table->unsignedBigInteger('EntryID')->nullable();
            $table->integer('SessionID')->nullable();
            $table->decimal('CommissionAmount', 18, 4)->nullable();
            $table->integer('CommissionCurrencyID')->nullable();
            $table->integer('Year')->storedAs('YEAR(TheDate)');
            $table->string('ReferenceNumber', 250)->nullable();

            $table->foreign('AccountID')->references('ID')->on('tblAccounts');
            $table->foreign('EntryID')->references('ID')->on('tblEntries')->onDelete('set null');
        });

        Schema::create('tblGroupMembers', function (Blueprint $table) {
            $table->increments('ID');
            $table->unsignedInteger('GroupID');
            $table->unsignedBigInteger('ItemID');
            $table->string('CreatedBy', 50)->nullable();
            $table->dateTime('CreatedDate')->useCurrent();
            $table->string('ModifiedBy', 50)->nullable();
            $table->dateTime('ModifiedDate')->nullable();

            $table->foreign('GroupID')->references('ID')->on('tblGroups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblGroupMembers');
        Schema::dropIfExists('tblSellCurrencies');
        Schema::dropIfExists('tblBuyCurrencies');
        Schema::dropIfExists('tblGroups');
        Schema::dropIfExists('tblGroupTypesPurposes');
    }
};
