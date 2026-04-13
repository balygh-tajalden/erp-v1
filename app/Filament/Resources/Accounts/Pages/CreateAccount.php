<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use App\Services\AccountingService;
use Illuminate\Support\Facades\Auth;

class CreateAccount extends CreateRecord
{
    protected static string $resource = AccountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['CreatedBy'] = Auth::id();
        
        // Auto-fill AccountCode with AccountNumber if not provided
        if (empty($data['AccountCode']) && !empty($data['AccountNumber'])) {
            $data['AccountCode'] = $data['AccountNumber'];
        }

        // Get legacy_id from the new AccountingSession system
        $data['SessionID'] = resolve(AccountingService::class)->getCurrentSessionID();

        return $data;
    }
}
