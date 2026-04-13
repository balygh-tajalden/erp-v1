<?php

namespace App\Filament\Resources\SimpleEntries\Pages;

use App\Filament\Resources\SimpleEntries\SimpleEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSimpleEntry extends EditRecord
{
    protected static string $resource = SimpleEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
