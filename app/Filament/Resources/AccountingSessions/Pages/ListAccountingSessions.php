<?php

namespace App\Filament\Resources\AccountingSessions\Pages;

use App\Filament\Resources\AccountingSessions\AccountingSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountingSessions extends ListRecords
{
    protected static string $resource = AccountingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
