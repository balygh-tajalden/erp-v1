<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // On MySQL, we can have a non-primary auto-increment column if it is indexed.
        Schema::table('tblsessions', function (Blueprint $table) {
            // Ensure legacy_id is indexed first to allow it to be auto_increment
            $table->unsignedBigInteger('legacy_id', true)->change(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblsessions', function (Blueprint $table) {
            $table->bigInteger('legacy_id')->nullable()->change();
        });
    }
};
