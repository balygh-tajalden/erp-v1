<?php

namespace App\Services\System;

use App\Models\SimpleEntry;
use App\Models\SellCurrency;
use App\Models\BuyCurrency;
use App\Models\AccountWhatsAppConfig;
use App\Models\AccountBalance;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class WhatsAppService
 * خدمة إشعارات الواتساب الاحترافية والمستقلة (Decoupled Architecture)
 */
class WhatsAppService
{
    // تعريف أنواع العمليات كقيم ثابتة لسهولة التحكم
    const EVENT_SIMPLE_ENTRY  = 'simple_entry';
    const EVENT_CURRENCY_SELL = 'currency_sell';
    const EVENT_CURRENCY_BUY  = 'currency_buy';
    const EVENT_WALLET_TRANSACTION = 'wallet_transaction';

    /**
     * 1. الدالة الأساسية لبناء وتوزيع الرسالة (Core Dispatcher)
     */
    public function dispatchMessage($accountId, $eventType, $docTitle, $docNumber, $date, $word, $amount, $currencyName, $currencyId, $note, $trxId = null)
    {
        // جلب الأرقام المفعلة لهذا العميل وهذا الحدث
        $numbers = $this->getSubscriberNumbers($accountId, $eventType);

        // بناء نص الرسالة بناءً على القالب الموحد
        $message = $this->constructTemplate([
            'title'      => $docTitle,
            'number'     => $docNumber,
            'date'       => $date->format('d-m-Y'),
            'amount'     => $this->formatAmount($amount),
            'currency'   => $currencyName,
            'word'       => $word,
            'note'       => $note,
            'balance'    => $this->getFormattedBalance($accountId, $currencyId, $currencyName),
            'reference'  => $trxId,
            'is_wallet'  => ($eventType === self::EVENT_WALLET_TRANSACTION),
            'time'       => now()->format('h:i:s d-m-Y') . (now()->format('A') === 'AM' ? ' ص' : ' م'),
        ]);

        $session = env('WHATSAPP_SESSION_NAME', 'my-session');

        // إذا لم توجد أرقام، نسجل "تخطى" (Skipped) في جدول السجلات للفائدة
        if (empty($numbers)) {
            Log::info("[WhatsAppService] Skipped: No active numbers for event '{$eventType}' and account {$accountId}");
            return;
        }

        // الإرسال لجميع الأرقام المرتبطة
        foreach ($numbers as $phoneNumber) {
            $this->sendNotification($session, $phoneNumber, $message, [
                'AccountID' => $accountId,
                'TransactionType' => $eventType,
                'TransactionID' => $trxId,
            ]);
        }
    }

    /**
     * 2. تنفيذ الإرسال الفعلي (يستخدم أيضاً في إعادة الإرسال)
     */
    public function sendNotification(string $sessionId, string $to, string $message, array $context = [])
    {
        $apiUrl = rtrim(env('WHATSAPP_API_URL', 'http://localhost:3001'), '/');
        $apiKey = env('WHATSAPP_API_KEY');

        try {
            $response = Http::timeout(20)->withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$apiUrl}/api/send", [
                'sessionId' => $sessionId,
                'to' => $to,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("[WhatsAppService] ✅ Sent to {$to} | TrxID: " . ($context['TransactionID'] ?? '-'));
                return true;
            } else {
                Log::error("[WhatsAppService] ❌ API Error ({$response->status()}): " . $response->body());
                return false;
            }
        } catch (Exception $e) {
            Log::error("[WhatsAppService] ❌ Connection Error to {$apiUrl}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 3. معالج "بيع العملة" (إرسال رسالتين للعميل)
     */
    public function sendSellCurrencyNotification(SellCurrency $sell)
    {
        $sell->load(['entry.details', 'currency', 'exchangeCurrency']);
        if (!$sell->entry) return;

        $details = $sell->entry->details->where('AccountID', $sell->AccountID);

        foreach ($details as $detail) {
            if($detail->Amount > 0) {
                $word = 'لكم';
            }else{
                $word = 'عليكم';
            }
            $exchangeText = "بيع عملة بسعر " . $this->formatAmount($sell->Price);

            if ($detail->Amount < 0) {
                $exchangeText .= " ويقابلها " . $this->formatAmount(abs((float)$sell->ExchangeAmount)) . " " . ($sell->exchangeCurrency?->CurrencyName ?? '');
            }

            $this->dispatchMessage(
                $sell->AccountID,
                self::EVENT_CURRENCY_SELL,
                'بيع عملة',
                $sell->Thenumber,
                $sell->TheDate,
                $word,
                abs((float)$detail->Amount),
                $detail->currency?->CurrencyName,
                $detail->CurrencyID,
                $exchangeText ?: $detail->Notes,
                $sell->ID
            );
        }
    }

    /**
     * 4. معالج "شراء العملة" (إرسال رسالتين للعميل)
     */
    public function sendBuyCurrencyNotification(BuyCurrency $buy)
    {
        $buy->load(['entry.details', 'currency', 'exchangeCurrency']);
        if (!$buy->entry) return;

        $details = $buy->entry->details->where('AccountID', $buy->AccountID);

        foreach ($details as $detail) {
            if($detail->Amount > 0) {
                $word = 'لكم';
            }else{
                $word = 'عليكم';
            }
            $exchangeText = "شراء عملة بسعر " . $this->formatAmount($buy->Price);

            if ($detail->Amount < 0) {
                $exchangeText .= " ويقابلها " . $this->formatAmount(abs((float)$buy->ExchangeAmount)) . " " . ($buy->exchangeCurrency?->CurrencyName ?? '');
            }

            $this->dispatchMessage(
                $buy->AccountID,
                self::EVENT_CURRENCY_BUY,
                'شراء عملة',
                $buy->Thenumber,
                $buy->TheDate,
                $word,
                abs((float)$detail->Amount),
                $detail->currency?->CurrencyName,
                $detail->CurrencyID,
                $exchangeText ?: $buy->Notes ?: $detail->Notes,
                $buy->ID
            );
        }
    }

    /**
     * 5. معالج "القيد البسيط"
     */
    public function sendSimpleEntryNotification(SimpleEntry $simple)
    {
        $simple->load(['entry.details', 'currency']);
        if (!$simple->entry) return;

        $from = $simple->entry->details->where('AccountID', $simple->FromAccountID)->where('Amount', '<', 0)->first();
        $to   = $simple->entry->details->where('AccountID', $simple->ToAccountID)->where('Amount', '>', 0)->first();
        $currencyName = $simple->currency?->CurrencyName ?? 'ريال يمني';

        if ($from) {
            $this->dispatchMessage($simple->FromAccountID, self::EVENT_SIMPLE_ENTRY, 'سند قيد بسيط', $simple->TheNumber, $simple->TheDate, 'عليكم', abs((float)$from->Amount), $currencyName, $simple->CurrencyID, $from->Notes, $simple->ID);
        }
        if ($to) {
            $this->dispatchMessage($simple->ToAccountID, self::EVENT_SIMPLE_ENTRY, 'سند قيد بسيط', $simple->TheNumber, $simple->TheDate, 'لكم', abs((float)$to->Amount), $currencyName, $simple->CurrencyID, $to->Notes, $simple->ID);
        }
    }
    /**
     * 6. معالج "المحافظ الرقمية"
     */
    public function sendWalletTransactionNotification(\App\Models\WalletTransaction $transaction)
    {
        $transaction->load(['account', 'currency']);

        if ($transaction->IsIngoing) {
            // المعاملة واردة (إيداع)
            $word = 'لكم';
            $title = 'إيداع محفظة';
        } else {
            // المعاملة صادرة (سحب)
            $word = 'عليكم';
            $title = 'سحب محفظة';
        }

        $this->dispatchMessage(
            $transaction->AccountID,
            self::EVENT_WALLET_TRANSACTION,
            $title,
            $transaction->ID,
            $transaction->BlockTimestamp,
            $word,
            (float) $transaction->Amount,
            $transaction->currency?->CurrencyName,
            $transaction->CurrencyID,
            $transaction->Notes ?: "عملية محفظة رقمية : " . substr($transaction->TransactionHash, 0, 10) . "...",
            $transaction->ID
        );
    }

    // ==========================================
    // Yardımcı Methotlar (Private Helpers)
    // ==========================================

    private function constructTemplate(array $data): string
    {
        $template = "*نظام دليل الحسابات*\n" .
                    "*({$data['title']})*\n" .
                    "الرقم: {$data['number']}\n" .
                    "التاريخ: {$data['date']}\n" .
                    "{$data['word']} {$data['amount']} {$data['currency']}\n" .
                    "{$data['note']}\n" .
                    "الرصيد/ {$data['balance']}\n";

        // إظهار المرجع فقط للمحافظ الإلكترونية بناءً على طلب المستخدم
        if (!empty($data['is_wallet']) && !empty($data['reference'])) {
            $template .= "*مرجع:* #{$data['reference']}\n";
        }

        $template .= "{$data['time']}";

        return $template;
    }

    private function getFormattedBalance($accountId, $currencyId, $currencyName): string
    {
        $balance = AccountBalance::where('AccountID', $accountId)->where('CurrencyID', $currencyId)->sum('Balance');
        
        if ($balance == 0) {
            return "0";
        }
        
        // الرصيد الموجب (دائن) يعني للعميل = لكم
        // الرصيد السالب (مدين) يعني على العميل = عليكم
        if($balance > 0){
            $word = 'لكم';
        }else{
            $word = 'عليكم';
        }
        
        return "{$word}: " . $this->formatAmount(abs((float)$balance)) . " {$currencyName}";
    }

    /**
     * تنسيق المبلغ ذكياً: يتجاهل الأصفار بعد الفاصلة إذا كان الرقم صحيحاً
     */
    private function formatAmount($amount): string
    {
        $amount = (float)$amount;
        // إذا كان الرقم صحيحاً (لا توجد كسور)
        if (floor($amount) == $amount) {
            return number_format($amount, 0, '.', ',');
        }
        // إذا توجد كسور، يظهر رقمين بعد الفاصلة
        return number_format($amount, 2, '.', ',');
    }

    private function getSubscriberNumbers($accountId, $eventType): array
    {
        $config = AccountWhatsAppConfig::where('AccountID', $accountId)->where('IsActive', true)->first();
        if (!$config) return [];

        $subscribedEvents = $config->Settings['events'] ?? [];
        if (!in_array($eventType, $subscribedEvents)) return [];

        return $config->Settings['numbers'] ?? [];
    }

    /**
     * إرسال رمز التحقق (OTP) لتسجيل الدخول
     */
    public function sendOtp(string $phone, string $code): bool
    {
        $session = env('WHATSAPP_SESSION_NAME', 'my-session');
        $message = "رمز التحقق الخاص بك هو: *{$code}*\n" .
            "صالح لمدة 5 دقائق.";

        return $this->sendNotification($session, $phone, $message, [
            'TransactionType' => 'otp_verification',
        ]);
    }

    /**
     * إشعار "تم ربط الحساب" (يتم استدعاؤه يدوياً بطلب المستخدم)
     */
    public function notifyAccountAdded(AccountWhatsAppConfig $config): array
    {
        $numbers = $config->Settings['numbers'] ?? [];
        if (empty($numbers)) {
            return ['success' => false, 'error' => 'لا توجد أرقام هواتف مسجلة لهذا الحساب.'];
        }

        $session = env('WHATSAPP_SESSION_NAME', 'my-session');
        $message = "*شركة بليغ للبرمجيات*\n\n" .
            "إشعار تفعيل الخدمة:\n" .
            "تم ربط حسابك بنجاح لاستقبال الإشعارات المالية الفورية عبر الواتساب.";
        $success = false;
        foreach ($numbers as $phone) {
            $sent = $this->sendNotification($session, $phone, $message, [
                'AccountID' => $config->AccountID,
                'TransactionType' => 'account_activation',
            ]);
            if ($sent) $success = true;
        }

        return $success
            ? ['success' => true, 'error' => null]
            : ['success' => false, 'error' => 'فشل الإرسال عبر خادم الواتساب.'];
    }
}
