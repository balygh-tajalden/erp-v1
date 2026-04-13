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
        Schema::create('tblServiceRequests', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->integer('AccountID');
            $table->integer('ProviderID');
            $table->unsignedTinyInteger('ServiceType');
            $table->string('PhoneNumber', 20);
            $table->decimal('Amount', 18, 4);
            $table->integer('CurrencyID')->default(1); // إضافة حقل العملة
            $table->decimal('Cost', 18, 4)->default(0);
            $table->decimal('Profit', 18, 4)->default(0);
            $table->enum('Status', ['Pending', 'Processing', 'Success', 'Failed'])->default('Pending');
            $table->string('TransactionID')->nullable();
            $table->text('ResponseLog')->nullable();
            $table->string('Notes', 500)->nullable(); // إضافة حقل الملاحظات
            $table->string('ReferenceNumber')->nullable();
            $table->bigInteger('EntryID')->nullable();
            $table->integer('BranchID')->nullable();

            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblServiceRequests');
    }
};
