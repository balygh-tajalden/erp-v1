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
        // 1. Create standard Laravel sessions table
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        // 2. Rename tblsessions to tblAccountingSessions (Persistent Shifts)
        if (Schema::hasTable('tblsessions') && !Schema::hasTable('tblAccountingSessions')) {
            Schema::rename('tblsessions', 'tblAccountingSessions');
        }

        // 3. Clean up tblAccountingSessions (optional but recommended)
        Schema::table('tblAccountingSessions', function (Blueprint $table) {
            // Drop Laravel-specific transient columns if they exist
            if (Schema::hasColumn('tblAccountingSessions', 'payload')) {
                $table->dropColumn(['payload', 'user_agent', 'last_activity']);
            }
            
            // Ensure ID is auto-increment if legacy_id is what we use, 
            // but let's keep the existing structure to avoid breaking legacy_id.
            // We just need it to be identifiable.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        if (Schema::hasTable('tblAccountingSessions')) {
            Schema::rename('tblAccountingSessions', 'tblsessions');
        }
    }
};
