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
        if (Schema::hasTable('tblAccountingSessions')) {
            Schema::table('tblAccountingSessions', function (Blueprint $table) {
                // The old 'id' column (string from Laravel sessions) is no longer needed 
                // as we use 'legacy_id' for accounting and 'sessions' table for web.
                if (Schema::hasColumn('tblAccountingSessions', 'id')) {
                    $table->string('id')->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
