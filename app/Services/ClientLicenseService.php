<?php

namespace App\Services;

use App\Models\ClientsSMS;
use App\Models\DeviceLicense;
use Illuminate\Support\Facades\Hash;

class ClientLicenseService
{
    /**
     * Authenticate a client using username and password
     */
    public function authenticate($username, $password)
    {
        $account = ClientsSMS::where('Username', $username)->first();

        // 1. التحقق من صحة بيانات الحساب
        if (!$account || !Hash::check($password, $account->PasswordHash)) {
            return [
                'success' => false,
                'error_code' => 'INVALID_CREDENTIALS',
                'message' => 'بيانات الدخول غير صحيحة',
                'status_code' => 401
            ];
        }

        // 2. التحقق من نشاط الحساب
        if (!$account->IsActive) {
            return [
                'success' => false,
                'error_code' => 'ACCOUNT_SUSPENDED',
                'message' => 'حساب العميل موقوف بالكامل',
                'status_code' => 403
            ];
        }

        return [
            'success' => true,
            'account' => $account
        ];
    }

    /**
     * Check device license status
     */
    public function checkDeviceStatus($account, $subscriptionKey)
    {
        $license = $account->deviceLicenses()->where('DeviceKey', $subscriptionKey)->first();

        // 3. التحقق من وجود ترخيص لهذا الجهاز المرتبط بهذا الحساب
        if (!$license) {
            return [
                'success' => false,
                'error_code' => 'DEVICE_NOT_REGISTERED',
                'message' => 'هذا الجهاز غير مسجل نهائيا في النظام لهذا الحساب',
                'status_code' => 401
            ];
        }

        // 4. التحقق من حالة الموافقة
        if ($license->Status !== 'approved') {
            return [
                'success' => false,
                'error_code' => $license->Status === 'pending' ? 'DEVICE_PENDING' : 'DEVICE_REJECTED',
                'message' => $license->Status === 'pending' ? 'هذا الجهاز قيد المراجعة، بانتظار الموافقة' : 'طلب ترخيص هذا الجهاز تم رفضه',
                'status_code' => 403
            ];
        }

        return [
            'success' => true,
            'balance' => $account->SMSBalance
        ];
    }

    /**
     * Handle a new device license request
     */
    public function handleLicenseRequest($account, $subscriptionKey, $deviceName)
    {
        // البحث عن الترخيص في جدول التراخيص بالكامل لتجنب تكرار المفتاح
        $existingLicense = DeviceLicense::where('DeviceKey', $subscriptionKey)->first();

        if ($existingLicense) {
            if ($existingLicense->Status === 'approved') {
                return [
                    'success' => false,
                    'error_code' => 'DEVICE_ALREADY_ACTIVE',
                    'message' => 'هذا الجهاز مفعل بالفعل ولدينا تسجيل مسبق له',
                    'status_code' => 403
                ];
            }

            // تحديث حالة الجهاز الذي كان مرفوضاً أو معلقاً ليعود للانتظار ويرتبط بالحساب الحالي
            $existingLicense->update([
                'Status' => 'pending',
                'licenseable_id' => $account->ClientID,
                'licenseable_type' => get_class($account),
                'DeviceName' => $deviceName ?? $existingLicense->DeviceName,
            ]);

            return [
                'success' => true,
                'message' => 'تم تحديث وإرسال طلب التفعيل للجهاز بنجاح، بانتظار موافقة الإدارة (تحديث سجل سابق)'
            ];
        }

        // التحقق من عدد الأجهزة المسموح بها
        $approvedCount = $account->deviceLicenses()->where('Status', 'approved')->count();
        if ($approvedCount >= $account->MaxDevices) {
            return [
                'success' => false,
                'error_code' => 'MAX_DEVICES_REACHED',
                'message' => 'عذراً، لقد وصلت للحد الأقصى من الأجهزة المسموح بها (' . $account->MaxDevices . ')',
                'status_code' => 403
            ];
        }

        // إنشاء طلب ترخيص جديد بالكامل
        $account->deviceLicenses()->create([
            'SystemName' => 'SMS_Gateway',
            'DeviceKey' => $subscriptionKey,
            'DeviceName' => $deviceName ?? 'جهاز غير معروف',
            'Status' => 'pending',
        ]);

        return [
            'success' => true,
            'message' => 'تم إرسال طلب الترخيص للجهاز الجديد بنجاح، بانتظار موافقة الإدارة'
        ];
    }
}
