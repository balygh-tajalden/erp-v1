<?php

namespace App\Filament\Resources\SystemPermissions\Pages;

use App\Filament\Resources\SystemPermissions\SystemPermissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSystemPermissions extends ManageRecords
{
    protected static string $resource = SystemPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
