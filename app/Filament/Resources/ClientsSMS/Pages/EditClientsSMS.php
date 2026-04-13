<?php

namespace App\Filament\Resources\ClientsSMS\Pages;

use App\Filament\Resources\ClientsSMS\ClientsSMSResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientsSMS extends EditRecord
{
    protected static string $resource = ClientsSMSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
