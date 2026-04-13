<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\DeleteAction;
use App\Models\Account;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()
                ->iconButton()
                ->disabled(fn (Account $record) => $record->getDeletionPreventionReason() !== null)
                ->tooltip(fn (Account $record) => $record->getDeletionPreventionReason() ?? 'حذف'),
        ];
    }
}
