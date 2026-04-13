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
        Schema::create('tblDeviceLicenses', function (Blueprint $table) {
            $table->id('LicenseID');
            $table->string('SystemName', 100)->comment('e.g. SMS_Gateway, ERP');
            // Polymorphic relation to any client/account table
            $table->string('licenseable_type');
            $table->unsignedBigInteger('licenseable_id');
            
            $table->string('DeviceName', 100)->nullable();
            $table->string('DeviceKey', 100)->unique();
            $table->string('Status', 20)->default('pending')->comment('pending, approved, rejected');
            
            $table->timestamps();
            
            // Index for faster polymorphic queries
            $table->index(['licenseable_type', 'licenseable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_device_licenses');
    }
};
