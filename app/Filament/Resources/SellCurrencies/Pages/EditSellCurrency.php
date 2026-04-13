<?php

namespace App\Filament\Resources\SellCurrencies\Pages;

use App\Filament\Resources\SellCurrencies\SellCurrencyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use App\DTOs\Accounting\SellCurrencyDTO;
use Illuminate\Database\Eloquent\Model;
use App\Services\AccountingService;

class EditSellCurrency extends EditRecord
{

    protected static string $resource = SellCurrencyResource::class;


    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // 1. تحويل البيانات القادمة من الواجهة إلى DTO
        $dto = SellCurrencyDTO::fromArray($data);

        // 2. استدعاء الخدمة المحاسبية لتحديث السجل والقيد المحاسبي في Transaction واحدة
        return app(AccountingService::class)->updateSellCurrency($record, $dto);
    }
}
