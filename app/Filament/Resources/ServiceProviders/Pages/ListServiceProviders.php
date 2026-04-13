<?php

namespace App\Filament\Resources\ServiceProviders\Pages;

use App\Filament\Resources\ServiceProviders\ServiceProvidersResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceProviders extends ListRecords
{
    protected static string $resource = ServiceProvidersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
