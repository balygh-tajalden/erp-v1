<?php

namespace App\Filament\Resources\AccountingSessions\Pages;

use App\Filament\Resources\AccountingSessions\AccountingSessionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountingSession extends EditRecord
{
    protected static string $resource = AccountingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('close_session')
                ->label('إغلاق الجلسة')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => ! $this->record->IsEnded)
                ->action(fn () => $this->record->close()),
            DeleteAction::make(),
        ];
    }
}
