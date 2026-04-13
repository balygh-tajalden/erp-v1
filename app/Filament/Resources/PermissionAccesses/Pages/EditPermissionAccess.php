<?php

namespace App\Filament\Resources\PermissionAccesses\Pages;

use App\Filament\Resources\PermissionAccesses\PermissionAccessResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPermissionAccess extends EditRecord
{
    protected static string $resource = PermissionAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
