<?php

namespace App\Filament\Resources\SimpleEntries\Pages;

use App\Filament\Resources\SimpleEntries\SimpleEntryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSimpleEntry extends ViewRecord
{
    protected static string $resource = SimpleEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
