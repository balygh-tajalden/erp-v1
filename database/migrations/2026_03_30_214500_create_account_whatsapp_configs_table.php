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
        Schema::create('tblAccountWhatsAppConfig', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('AccountID')->unique()->comment('ID from tblAccounts');
            $table->boolean('IsActive')->default(1)->comment('Master switch for notifications');

            // حقل JSON لدعم تعدد الأرقام وفلترة العمليات بشكل مرن
            $table->json('Settings')->nullable()->comment('Custom settings e.g. [{"phone": "...", "events": ["receipt", "all"]}]');

            // التوقيتات حسب نظامك الحالي
            $table->dateTime('CreatedDate')->useCurrent();
            $table->dateTime('ModifiedDate')->useCurrent()->useCurrentOnUpdate();

            // الربط مع جدول الحسابات
            $table->foreign('AccountID')->references('ID')->on('tblAccounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblAccountWhatsAppConfig');
    }
};
