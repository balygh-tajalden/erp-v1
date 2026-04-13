<?php

namespace App\Filament\Resources\BuyCurrencies\Pages;

use App\Filament\Resources\BuyCurrencies\BuyCurrencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Services\AccountingService;
use App\DTOs\Accounting\BuyCurrencyDTO;

class ListBuyCurrencies extends ListRecords
{
    protected static string $resource = BuyCurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->color('success')
                ->modalIcon('heroicon-o-arrow-down-left')
                ->modalIconColor('success')
                ->modalDescription('تسجيل عملية شراء عملة أجنبية وخصم قيمتها من الصندوق')
                ->modalWidth('4xl')
                ->using(fn(array $data) => app(AccountingService::class)->postBuyCurrency(BuyCurrencyDTO::fromArray($data))),
        ];
    }
}
