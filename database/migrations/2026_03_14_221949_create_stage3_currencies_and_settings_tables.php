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
        // Stage 3: Currencies & Settings Tables

        Schema::create('tblCurrencies', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('TheNumber')->nullable();
            $table->string('CurrencyName', 50);
            $table->string('ArabicCode', 10)->nullable();
            $table->string('EnglishCode', 10)->nullable();
            $table->string('ISO_Code', 20)->nullable();
            $table->boolean('IsDefault')->nullable()->default(0);
            $table->bigInteger('CreatedBy')->nullable();
            $table->integer('SessionID')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
        });

        Schema::create('tblCurrenciesPrices', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->integer('SourceCurrencyID');
            $table->integer('TargetCurrencyID');
            $table->decimal('ExchangePrice', 18, 6);
            $table->decimal('BuyPrice', 18, 6);
            $table->decimal('SellPrice', 18, 6);
            $table->bigInteger('CreatedBy')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
            $table->decimal('MinBuyPrice', 18, 6)->nullable();
            $table->decimal('MaxBuyPrice', 18, 6)->nullable();
            $table->decimal('MinSellPrice', 18, 6)->nullable();
            $table->decimal('MaxSellPrice', 18, 6)->nullable();
            $table->string('Notes', 500)->nullable();
            $table->binary('RowVersion')->nullable();
            $table->binary('PrevRowVersion', 8)->nullable();
            $table->integer('SessionID')->nullable();
        });

        Schema::create('tblSystemSettings', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('SettingKey', 100);
            $table->string('SettingValue', 200)->nullable();
            $table->string('Description', 200)->nullable();
            $table->integer('TheUserID')->nullable();
            $table->integer('BranchID')->nullable();
            $table->integer('LevelID')->nullable();
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            $table->integer('CreatedBy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblSystemSettings');
        Schema::dropIfExists('tblCurrenciesPrices');
        Schema::dropIfExists('tblCurrencies');
    }
};
