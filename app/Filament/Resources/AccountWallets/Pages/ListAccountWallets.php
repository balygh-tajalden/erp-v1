<?php

namespace App\Filament\Resources\AccountWallets\Pages;

use App\Filament\Resources\AccountWallets\AccountWalletResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountWallets extends ListRecords
{
    protected static string $resource = AccountWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
