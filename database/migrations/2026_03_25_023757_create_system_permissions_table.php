<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblSystemPermissions', function (Blueprint $table) {
            $table->id('ID');
            $table->string('PermissionCode', 100)->unique();
            $table->string('ArabicName', 255);
            $table->string('Category', 100);
            $table->boolean('IsActive')->default(true);
        });

        // Auto-seed the official dictionary
        $permissions = [
            // القيود والسندات
            ['allow_backdate', 'السماح بتاريخ سابق للمستند', '1. القيود والسندات'],
            ['allow_future_date', 'السماح بتاريخ مستقبلي', '1. القيود والسندات'],
            ['edit_posted_entry', 'تعديل السندات والقيود المرحلة', '1. القيود والسندات'],
            ['unpost_entry', 'إلغاء ترحيل قيد/سند', '1. القيود والسندات'],
            ['view_others_entries', 'رؤية مستندات الزملاء الآخرين', '1. القيود والسندات'],
            ['reprint_document', 'إعادة طباعة مستند', '1. القيود والسندات'],
            ['change_entry_currency', 'تغيير العملة الافتراضية للسند', '1. القيود والسندات'],

            // المبيعات والعملاء
            ['sell_below_cost', 'البيع بأقل من التكلفة', '2. المبيعات والعملاء'],
            ['exceed_credit_limit', 'تجاوز حد المديونية', '2. المبيعات والعملاء'],
            ['change_invoice_price', 'تعديل سعر الصنف بالفاتورة', '2. المبيعات والعملاء'],
            ['exceed_max_discount', 'تجاوز سقف الخصم الأعلى', '2. المبيعات والعملاء'],
            ['view_customer_balance', 'إظهار رصيد العميل بالشاشة', '2. المبيعات والعملاء'],
            ['delete_printed_invoice', 'تعديل/حذف فاتورة بعد طباعتها', '2. المبيعات والعملاء'],
            ['credit_sale_to_cash_customer', 'البيع بالآجل لعميل نقدي', '2. المبيعات والعملاء'],
            ['view_invoice_profit', 'إظهار أرباح الفاتورة', '2. المبيعات والعملاء'],

            // المستودعات والأصناف
            ['view_cost_price', 'إظهار سعر التكلفة للأصناف', '3. المستودعات والأصناف'],
            ['sell_negative_stock', 'السماح بالبيع بالسالب', '3. المستودعات والأصناف'],
            ['edit_item_cost', 'تعديل التكلفة برمجياً', '3. المستودعات والأصناف'],
            ['approve_inventory_adjustment', 'اعتماد الفروقات الجردية', '3. المستودعات والأصناف'],
            ['view_all_warehouses', 'رؤية جرد كل المستودعات والفروع', '3. المستودعات والأصناف'],

            // الحسابات
            ['view_account_statement', 'طباعة كشف حساب (للغير)', '4. الحسابات'],
            ['exceed_petty_cash_limit', 'الدفع متجاوزاً عهدة الصندوق', '4. الحسابات'],
            ['transfer_between_wallets', 'نقل نقدية بين الصناديق', '4. الحسابات'],
            ['view_branch_profit', 'استعراض أرباح الفرع', '4. الحسابات'],
            ['edit_chart_of_accounts', 'إضافة وتعديل شجرة الحسابات', '4. الحسابات'],

            // النظام والأمان
            ['change_session_date', 'تغيير تاريخ الجلسة (للكاشير)', '5. النظام والأمان'],
            ['login_outside_hours', 'الدخول خارج أوقات الدوام', '5. النظام والأمان'],
            ['export_to_excel', 'تصدير البيانات بصيغة Excel', '5. النظام والأمان'],
            ['view_system_logs', 'رؤية سجلات المراقبة Logs', '5. النظام والأمان'],
            ['bypass_approval_workflow', 'تخطي دورة المستندات والموافقات', '5. النظام والأمان'],
        ];

        $data = [];
        foreach ($permissions as $p) {
            $data[] = [
                'PermissionCode' => $p[0],
                'ArabicName'     => $p[1],
                'Category'       => $p[2],
                'IsActive'       => 1,
            ];
        }

        DB::table('tblSystemPermissions')->insert($data);
    }

    public function down(): void
    {
        Schema::dropIfExists('tblSystemPermissions');
    }
};
