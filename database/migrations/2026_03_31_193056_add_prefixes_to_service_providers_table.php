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
        Schema::table('tblServiceProviders', function (Blueprint $table) {
            $table->string('Prefixes')->nullable()->after('Name')->comment('Comma separated prefixes e.g. 77,78');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblServiceProviders', function (Blueprint $table) {
            $table->dropColumn('Prefixes');
        });
    }
};
