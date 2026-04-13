<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إزالة كافة التريجرات القديمة للبدء باستخدام منطق التطبيق (Application Logic)
        DB::unprepared("DROP TRIGGER IF EXISTS trig_Entries_AfterUpdate;");
        DB::unprepared("DROP TRIGGER IF EXISTS trig_EntryDetails_AfterInsert;");
        DB::unprepared("DROP TRIGGER IF EXISTS trig_EntryDetails_AfterUpdate;");
        DB::unprepared("DROP TRIGGER IF EXISTS trig_EntryDetails_AfterDelete;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ملاحظة: لا يمكننا إعادة استعادة الـ Trigger بسهولة هنا لأنه يتطلب الكود القديم بالكامل 
        // وعادة لا نعود للـ Triggers بمجرد الانتقال للـ Logic.
    }
};
