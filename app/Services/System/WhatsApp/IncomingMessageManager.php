<?php

namespace App\Services\System\WhatsApp;

use App\Services\System\WhatsAppService;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncomingMessageManager
{
    public function __construct(
        protected WhatsAppService $whatsapp,
        protected AccountingService $accounting
    ) {}

    /**
     * معالجة الرسالة الواردة
     */
    public function handle(string $phone, string $message): void
    {
        // 1. تنظيف رقم الهاتف (إزالة المسافات أو البادئة الزائدة)
        $cleanPhone = $this->normalizePhone($phone);

        // 2. البحث عن الحساب المرتبط بهذا الرقم في tblAccountWhatsAppConfig
        // يتم البحث داخل قائمة الأرقام "numbers" في حقل Settings (JSON)
        $config = DB::table('tblAccountWhatsAppConfig')
            ->where('IsActive', 1)
            ->whereJsonContains('Settings->numbers', $phone)
            ->first();

        // تجربة ببحث مرن إذا فشل البحث المباشر (لأن الأرقام قد تخزن بـ 967 أو بدونها)
        if (!$config) {
            $cleanPhone = $this->normalizePhone($phone);
            $config = DB::table('tblAccountWhatsAppConfig')
                ->where('IsActive', 1)
                ->where(function ($query) use ($cleanPhone) {
                    $query->where('Settings->numbers', 'like', "%$cleanPhone%");
                })
                ->first();
        }

        if (!$config) {
            Log::warning("WhatsApp Bot: Received message from unknown phone: $cleanPhone");
            return;
        }

        $accountId = $config->AccountID;

        // 3. تحليل الأمر (مثلاً: 1 = طلب رصيد)
        $command = trim($message);

        switch ($command) {
            case '1':
                $this->processBalanceQuery($accountId, $phone);
                break;

            default:
                // يمكن إضافة أوامر أخرى هنا مستقبلاً
                break;
        }
    }

    /**
     * معالجة طلب الرصيد وإرسال الرد
     */
    protected function processBalanceQuery(int $accountId, string $targetPhone): void
    {
        // إرسال رسالة انتظار
        $session = env('WHATSAPP_SESSION_NAME', 'my-session');
        $this->whatsapp->sendNotification($session, $targetPhone, "عميلنا العزيز يرجى الانتظار طلبك قيد التنفيذ");

        // جلب البيانات من View ملخص الأرصدة
        $balances = DB::table('vw_accountstatementsummary')
            ->where('AccountID', $accountId)
            ->get();

        $accountName = $this->accounting->getAccountName($accountId);
        $accountNumber = DB::table('tblAccounts')->where('ID', $accountId)->value('AccountNumber');
        $date = now()->translatedFormat('l d-m-Y');

        // بناء نص الرسالة
        $response = "عميلنا: *{$accountName}*\n";
        $response .= "رقم حسابكم: *{$accountNumber}*\n";
        $response .= "رصيد حسابكم خلال يوم {$date}\n";

        if ($balances->isEmpty()) {
            $response .= "لا توجد حركات سابقة على هذا الحساب.";
        } else {
            foreach ($balances as $bal) {
                $type = $bal->{'صافي المبلغ'} > 0 ? "لكم" : "عليكم";
                $amount = number_format(abs($bal->{'صافي المبلغ'}), 2);
                $currency = $bal->{'العملة'};
                
                $response .= "{$type}: {$amount} {$currency}\n";
            }
        }

        // إرسال النتيجة النهائية
        $session = env('WHATSAPP_SESSION_NAME', 'my-session');
        $this->whatsapp->sendNotification($session, $targetPhone, $response);
    }

    /**
     * تنظيف رقم الهاتف لضمان المطابقة
     */
    protected function normalizePhone(string $phone): string
    {
        // حذف أي رموز غير رقمية
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // إذا كان يبدأ بـ 967، نأخذ ما بعده للمطابقة المرنة
        if (str_starts_with($phone, '967')) {
            return substr($phone, 3);
        }
        
        return $phone;
    }
}
