<?php

namespace App\Filament\Resources\AccountWhatsAppConfigs\Pages;

use App\Filament\Resources\AccountWhatsAppConfigs\AccountWhatsAppConfigResource;
use App\Models\AccountWhatsAppConfig;
use App\Services\System\WhatsAppService;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageAccountWhatsAppConfigs extends ManageRecords
{
    protected static string $resource = AccountWhatsAppConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
