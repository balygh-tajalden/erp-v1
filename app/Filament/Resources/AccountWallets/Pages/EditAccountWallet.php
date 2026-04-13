<?php

namespace App\Filament\Resources\AccountWallets\Pages;

use App\Filament\Resources\AccountWallets\AccountWalletResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountWallet extends EditRecord
{
    protected static string $resource = AccountWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
