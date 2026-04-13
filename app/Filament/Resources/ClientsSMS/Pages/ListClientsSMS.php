<?php

namespace App\Filament\Resources\ClientsSMS\Pages;

use App\Filament\Resources\ClientsSMS\ClientsSMSResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientsSMS extends ListRecords
{
    protected static string $resource = ClientsSMSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
