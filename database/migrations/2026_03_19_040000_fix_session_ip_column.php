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
        Schema::table('tblsessions', function (Blueprint $table) {
            // Laravel expects lowercase ip_address
            if (Schema::hasColumn('tblsessions', 'IPAddress') && !Schema::hasColumn('tblsessions', 'ip_address')) {
                $table->renameColumn('IPAddress', 'ip_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblsessions', function (Blueprint $table) {
            if (Schema::hasColumn('tblsessions', 'ip_address')) {
                $table->renameColumn('ip_address', 'IPAddress');
            }
        });
    }
};
