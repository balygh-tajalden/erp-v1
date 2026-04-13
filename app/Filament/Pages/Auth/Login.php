<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component as FormComponent;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\System\WhatsAppService;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Session;

class Login extends BaseLogin
{
    // Step configuration
    public int $step = 1;
    public string $otp = '';
    public ?User $pendingUser = null;

    public function mount(): void
    {
        try {
            parent::mount();
        } catch (\Exception $e) {
            if (Auth::check()) {
                redirect()->intended(filament()->getUrl());
            }
        }

        $this->step = 1;
        $this->otp = '';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getStep1Schema(),
            ])
            ->statePath('data');
    }

    protected function getStep1Schema(): FormComponent
    {
        return \Filament\Schemas\Components\Group::make([
            TextInput::make('UserName')
                ->label(__('Username'))
                ->required()
                ->autocomplete()
                ->autofocus()
                ->extraAttributes(['class' => 'rtl-input']),
            $this->getPasswordFormComponent(),
        ])
        ->key('step1_group');
    }

    protected function getStep2Schema(): FormComponent
    {
        return TextInput::make('otp_code')
            ->label(__('WhatsApp Verification Code'))
            ->placeholder('000000')
            ->required()
            ->maxLength(6)
            ->key('step2_otp')
            ->extraAttributes(['class' => 'text-center text-2xl tracking-widest font-bold']);
    }

    /**
     * Override the standard password component to use UserPassword
     */
    protected function getPasswordFormComponent(): FormComponent
    {
        return TextInput::make('password')
            ->label(__('Password'))
            ->password()
            ->required()
            ->hidden($this->step !== 1)
            ->autocomplete('current-password')
            ->extraAttributes(['class' => 'rtl-input']);
    }

    /**
     * Handle the multi-step authentication logic
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->danger()
                ->send();

            return null;
        }

        // Use Schema state path
        $data = $this->form->getState();

        if ($this->step === 1) {
            return $this->handleStep1($data);
        }

        return $this->handleStep2($data);
    }

    protected function handleStep1(array $data): ?LoginResponse
    {
        $user = User::where('UserName', $data['UserName'])->active()->first();

        if (!$user || !Auth::validate(['UserName' => $data['UserName'], 'password' => $data['password']])) {
            throw ValidationException::withMessages([
                'data.UserName' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        /* 
        // Bypassing OTP for now as requested
        if ($user->Phone) {
            $this->step = 2;
            $this->resetErrorBag();
            
            Session::put('auth_pending_user_id', $user->ID);
            Session::put('auth_remember', $data['remember'] ?? false);
            
            $otpCode = rand(100000, 999999);
            Session::put('auth_otp_code', (string)$otpCode);
            Session::put('auth_otp_expires_at', now()->addMinutes(5));

            app(WhatsAppService::class)->sendOtp($user->Phone, (string)$otpCode);

            Notification::make()
                ->title(__('Verification code sent to your WhatsApp'))
                ->success()
                ->send();

            return null;
        }
        */

        Auth::login($user, $data['remember'] ?? false);
        return app(LoginResponse::class);
    }

    protected function handleStep2(array $data): ?LoginResponse
    {
        $userId = Session::get('auth_pending_user_id');
        $storedOtp = Session::get('auth_otp_code');
        $expiresAt = Session::get('auth_otp_expires_at');

        if (!$userId || !$storedOtp || now()->gt($expiresAt)) {
            $this->step = 1;
            Session::forget(['auth_pending_user_id', 'auth_otp_code', 'auth_otp_expires_at']);
            
            Notification::make()
                ->title(__('Session expired, please try again'))
                ->danger()
                ->send();
            return null;
        }

        if ($data['otp_code'] !== (string)$storedOtp) {
            throw ValidationException::withMessages([
                'data.otp_code' => __('Invalid verification code'),
            ]);
        }

        $user = User::find($userId);
        Auth::login($user, Session::get('auth_remember', false));

        Session::forget(['auth_pending_user_id', 'auth_otp_code', 'auth_otp_expires_at', 'auth_remember']);

        return app(LoginResponse::class);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'UserName' => $data['UserName'],
            'password' => $data['password'],
        ];
    }
}
