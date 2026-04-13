<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\System\WhatsAppService;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Log;

class TwoFactorService
{
    protected $otp;
    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->otp = new Otp();
        $this->whatsapp = $whatsapp;
    }

    /**
     * Generate and send OTP via WhatsApp
     */
    public function sendOtp(User $user): bool
    {
        if (!$user->Phone) {
            Log::warning("User {$user->ID} has no phone number for OTP.");
            return false;
        }

        // Generate 6-digit OTP valid for 4 minutes
        $otpResponse = $this->otp->generate($user->UserName, 'numeric', 6, 4);

        if (!$otpResponse->status) {
            return false;
        }

        $message = "كود التحقق الخاص بك هو: *{$otpResponse->token}*\nصالح لمدة 4 دقائق.";
        $session = env('WHATSAPP_SESSION_NAME', 'my-session');

        return $this->whatsapp->sendNotification($session, $user->Phone, $message, [
            'UserID' => $user->ID,
            'Type' => 'OTP'
        ]);
    }

    /**
     * Verify the provided OTP
     */
    public function verifyOtp(User $user, string $token): bool
    {
        $verification = $this->otp->validate($user->UserName, $token);
        return $verification->status;
    }
}
