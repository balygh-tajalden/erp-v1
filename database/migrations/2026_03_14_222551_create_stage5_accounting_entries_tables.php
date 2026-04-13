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
        // Stage 5: Accounting Entries Tables

        Schema::create('tblEntries', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('DocumentID');
            $table->bigInteger('RecordNumber')->nullable();
            $table->bigInteger('RecordID');
            $table->date('TheDate');
            $table->string('Notes', 255)->nullable();
            $table->unsignedBigInteger('CreatedBy');
            $table->unsignedBigInteger('BranchID');
            $table->boolean('IsPosted')->nullable()->default(0);
            $table->boolean('isDeleted')->default(0);
            $table->boolean('IsReversed')->nullable()->default(0);
            $table->bigInteger('ReversalOfID')->nullable();
            $table->dateTime('CreatedDate')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            $table->tinyInteger('IsClosingEntry')->nullable();
            $table->integer('Year')->virtualAs('year(TheDate)');

            $table->unique(['DocumentID', 'RecordID']);
        });

        Schema::create('tblEntryDetails', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('ParentID');
            $table->decimal('Amount', 20, 4);
            $table->decimal('MCAmount', 20, 4)->nullable();
            $table->unsignedInteger('CurrencyID')->nullable();
            $table->unsignedBigInteger('AccountID')->nullable();
            $table->string('Notes', 2500)->nullable();
            $table->unsignedBigInteger('CostCenterID')->nullable();
            $table->unsignedBigInteger('DetailedAccountID')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->nullable();
            $table->unsignedInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            
            // Foreign key to tblEntries
            $table->foreign('ParentID')->references('ID')->on('tblEntries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblEntryDetails');
        Schema::dropIfExists('tblEntries');
    }
};
