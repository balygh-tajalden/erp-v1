<?php

namespace App\Filament\Resources\BuyCurrencies\Pages;

use App\Filament\Resources\BuyCurrencies\BuyCurrencyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\DTOs\Accounting\BuyCurrencyDTO;
use App\Services\AccountingService;


class EditBuyCurrency extends EditRecord
{
    protected static string $resource = BuyCurrencyResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $dto = BuyCurrencyDTO::fromArray($data);
        return app(AccountingService::class)->updateBuyCurrency($record, $dto);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function (Model $record) {
                    app(AccountingService::class)->deleteBuyCurrency($record);
                }),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
