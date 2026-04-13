<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\System\WhatsApp\IncomingMessageManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected IncomingMessageManager $manager
    ) {}

    /**
     * استقبال طلبات الواتساب الواردة (Webhook)
     */
    public function handle(Request $request)
    {
        // تسجيل الطلب للتصحيح (Debugging) عند الحاجة
        Log::info('WhatsApp Webhook Received:', $request->all());

        /**
         * تنسيق البيانات يعتمد على مقدم الخدمة، 
         * لكننا سنفترض التنسيق الشائع: number/phone و message/text
         */
        $phone = $request->input('number') ?? $request->input('phone') ?? $request->input('from');
        $message = $request->input('message') ?? $request->input('text.body') ?? $request->input('text');

        if (!$phone || !$message) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        try {
            // تمرير الطلب للمعالج الذكي
            $this->manager->handle($phone, $message);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("WhatsApp Webhook Error: " . $e->getMessage());
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
