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
        Schema::create('tblAccountWallets', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->integer('AccountID');
            $table->string('WalletAddress')->unique();
            $table->string('Network')->default('USDT');
            $table->string('Label')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->text('Notes')->nullable();
            
            // Audit columns
            $table->integer('CreatedBy')->nullable();
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('CreatedDate')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            $table->softDeletes('DeletedDate');

            $table->index('AccountID');
            $table->index('WalletAddress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblAccountWallets');
    }
};
