<?php

namespace App\Filament\Resources\BuyCurrencies\Pages;

use App\Filament\Resources\BuyCurrencies\BuyCurrencyResource;
use Filament\Resources\Pages\CreateRecord;
use App\DTOs\Accounting\BuyCurrencyDTO;
use App\Services\AccountingService;
use Illuminate\Database\Eloquent\Model;
class CreateBuyCurrency extends CreateRecord
{
    protected static string $resource = BuyCurrencyResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $dto = BuyCurrencyDTO::fromArray($data);
        return app(AccountingService::class)->postBuyCurrency($dto);
    }
}
