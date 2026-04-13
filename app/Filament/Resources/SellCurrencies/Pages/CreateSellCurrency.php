<?php

namespace App\Filament\Resources\SellCurrencies\Pages;

use App\Filament\Resources\SellCurrencies\SellCurrencyResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\DTOs\Accounting\SellCurrencyDTO;
use App\Services\AccountingService;

class CreateSellCurrency extends CreateRecord
{

    protected static string $resource = SellCurrencyResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // 1. تحويل البيانات القادمة من الواجهة إلى DTO
        $dto = SellCurrencyDTO::fromArray($data);
        // 2. استدعاء الخدمة المحاسبية وإرجاع النتيجة
        return app(AccountingService::class)->postSellCurrency($dto);
    }
}
