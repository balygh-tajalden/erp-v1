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
        // Stage 8: Invoicing & Inventory Basics

        Schema::create('tblItems', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('ItemName', 100);
            $table->string('ItemType', 20)->nullable();
            $table->boolean('IsGold')->default(0);
            $table->boolean('IsCurrency')->default(0);
            $table->string('Unit', 20)->nullable();
            $table->decimal('DefaultPrice', 18, 2)->default(0);
            $table->string('Notes', 250)->nullable();
            $table->boolean('IsActive')->default(1);
        });

        Schema::create('tblInvoiceTypes', function (Blueprint $table) {
            $table->integer('ID')->primary();
            $table->string('InvoiceTypeName', 50);
        });

        Schema::create('tblInvoices', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('InvoiceType', 20);
            $table->unsignedBigInteger('AccountID');
            $table->unsignedBigInteger('PaymentAccountID')->nullable();
            $table->integer('InvoiceTypeID');
            $table->unsignedBigInteger('CustomerID');
            $table->unsignedBigInteger('EntryID')->nullable();
            $table->integer('TheNumber');
            $table->date('TheDate');
            $table->string('Notes', 250)->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->integer('SessionID')->nullable();
            $table->dateTime('CreatedDate')->useCurrent();

            $table->foreign('AccountID')->references('ID')->on('tblAccounts');
            $table->foreign('CustomerID')->references('ID')->on('tblCustomers');
            $table->foreign('EntryID')->references('ID')->on('tblEntries')->onDelete('set null');
        });

        Schema::create('tblInvoiceDetails', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('InvoiceID');
            $table->unsignedBigInteger('ItemID');
            $table->decimal('Quantity', 18, 3);
            $table->decimal('UnitPrice', 18, 2);
            $table->decimal('ManufactureCost', 18, 2)->default(0);
            $table->decimal('Total', 18, 2)->virtualAs('Quantity * UnitPrice + ManufactureCost');

            $table->foreign('InvoiceID')->references('ID')->on('tblInvoices')->onDelete('cascade');
            $table->foreign('ItemID')->references('ID')->on('tblItems');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblInvoiceDetails');
        Schema::dropIfExists('tblInvoices');
        Schema::dropIfExists('tblInvoiceTypes');
        Schema::dropIfExists('tblItems');
    }
};
