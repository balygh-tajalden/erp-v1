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
        Schema::create('tblClientsSMS', function (Blueprint $table) {
            $table->increments('ClientID');
            $table->string('ClientName', 200)->nullable();
            $table->string('Username', 50)->unique();
            $table->text('PasswordHash');
            $table->integer('SMSBalance')->default(0);
            $table->boolean('IsActive')->default(1);
            $table->integer('MaxDevices')->default(1);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblClientsSMS');
    }
};
