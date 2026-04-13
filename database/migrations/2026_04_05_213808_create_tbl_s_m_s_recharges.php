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
        Schema::create('tblSMSRecharges', function (Blueprint $table) {
            $table->id('RechargeID');
            $table->unsignedInteger('ClientID'); // Match tblClientsSMS primary key type (increments)
            $table->integer('MessagesCount');
            $table->decimal('Price', 10, 2)->nullable();
            $table->string('Notes', 255)->nullable();
            $table->timestamps();
            
            $table->foreign('ClientID')->references('ClientID')->on('tblClientsSMS')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_s_m_s_recharges');
    }
};
