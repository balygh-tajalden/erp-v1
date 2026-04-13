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
        // 1. Drop the temporary sessions table if it exists
        Schema::dropIfExists('sessions');

        // 2. We use a "Rebuild" approach for tblsessions to avoid complex MySQL primary key/auto_increment constraints
        
        // Skip renaming if already exists or renamed
        if (Schema::hasTable('tblsessions') && !Schema::hasColumn('tblsessions', 'payload')) {
            Schema::rename('tblsessions', 'tblsessions_backup');
        }

        // 3. Create NEW unified tblsessions
        if (!Schema::hasTable('tblsessions')) {
            Schema::create('tblsessions', function (Blueprint $table) {
                // Laravel required columns (Primary String ID)
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();

                // Accounting required columns (from original tblsessions)
                $table->bigInteger('legacy_id')->nullable(); // Original ID
                $table->dateTime('StartTime')->nullable();
                $table->dateTime('EndTime')->nullable();
                $table->string('PCID', 200)->nullable();
                $table->string('OSVersion', 100)->nullable();
                $table->string('MachineName', 100)->nullable();
                $table->string('OSUserName', 100)->nullable();
                $table->string('Notes', 250)->nullable();
                $table->bigInteger('BranchID')->nullable();
                $table->dateTime('EnterTime')->nullable();
                $table->boolean('IsEnded')->nullable();
                $table->string('ServiceAddress', 100)->nullable();
                $table->bigInteger('SessionID')->nullable();
                $table->boolean('ISHasToken')->nullable();
                $table->boolean('ISHasValidationCode')->nullable();
            });

            // 4. Backfill data from backup if it exists
            if (Schema::hasTable('tblsessions_backup')) {
                 DB::statement("INSERT INTO tblsessions 
                    (id, user_id, ip_address, legacy_id, StartTime, EndTime, PCID, OSVersion, MachineName, OSUserName, Notes, BranchID, EnterTime, IsEnded, ServiceAddress, SessionID, ISHasToken, ISHasValidationCode, payload, last_activity)
                    SELECT 
                    CONCAT('legacy_', ID), UserID, IPAddress, ID, StartTime, EndTime, PCID, OSVersion, MachineName, OSUserName, Notes, BranchID, EnterTime, IsEnded, ServiceAddress, SessionID, ISHasToken, ISHasValidationCode, '', " . time() . "
                    FROM tblsessions_backup");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblsessions');
        if (Schema::hasTable('tblsessions_backup')) {
            Schema::rename('tblsessions_backup', 'tblsessions');
        }
    }
};
