<?php

namespace App\Filament\Resources\ServiceProviders\Pages;

use App\Filament\Resources\ServiceProviders\ServiceProvidersResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceProviders extends EditRecord
{
    protected static string $resource = ServiceProvidersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
