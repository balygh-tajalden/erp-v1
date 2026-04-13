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
        Schema::create('tblServiceProviders', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Name', 100);
            $table->string('Code', 50)->unique(); // مثل: yemen_mobile
            $table->string('Category', 50)->default('Telecommunications');
            $table->boolean('IsActive')->default(true);
            $table->decimal('DefaultProfit', 18, 4)->default(0); 
            $table->string('Logo')->nullable();

            // الطوابع الزمنية المتبعة في مشروعك
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
            $table->softDeletes();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblServiceProviders');
    }
};
