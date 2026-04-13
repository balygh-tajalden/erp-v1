<?php

namespace App\Filament\Resources\ClientsSMS\Pages;

use App\Filament\Resources\ClientsSMS\ClientsSMSResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClientsSMS extends ViewRecord
{
    protected static string $resource = ClientsSMSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
