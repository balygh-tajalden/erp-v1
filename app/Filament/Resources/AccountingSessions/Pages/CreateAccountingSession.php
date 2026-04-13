<?php

namespace App\Filament\Resources\AccountingSessions\Pages;

use App\Filament\Resources\AccountingSessions\AccountingSessionResource;
use App\Models\AccountingSession;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CreateAccountingSession extends CreateRecord
{
    protected static string $resource = AccountingSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Check for existing active session for this user
        $activeSession = AccountingSession::active()
            ->where('user_id', Auth::id())
            ->first();

        if ($activeSession) {
            Notification::make()
                ->title('لديك جلسة مفتوحة بالفعل!')
                ->body('يرجى إغلاق الجلسة الحالية قبل فتح جلسة جديدة.')
                ->danger()
                ->send();

            $this->halt();
        }

        $userAgent = request()->header('User-Agent');
        $systemInfo = AccountingSession::parseSystemInfo($userAgent);

        $data['legacy_id'] = AccountingSession::getNextLegacyID();
        $data['user_id'] = Auth::id();
        $data['StartTime'] = now();
        $data['EnterTime'] = now();
        $data['ip_address'] = request()->ip();
        $data['OSVersion'] = $systemInfo['os'];
        $data['MachineName'] = $systemInfo['machine'];
        $data['IsEnded'] = false;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Set the active session in the Laravel session
        Session::put('accounting_session_id', $this->record->legacy_id);
    }
}
