<?php

namespace App\Filament\Resources\SellCurrencies\Pages;

use App\Filament\Resources\SellCurrencies\SellCurrencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Services\AccountingService;
use App\DTOs\Accounting\SellCurrencyDTO;

class ListSellCurrencies extends ListRecords
{

    protected static string $resource = SellCurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->color('warning')
                ->modalIcon('heroicon-o-arrow-up-right')
                ->modalIconColor('warning')
                ->modalDescription('تسجيل عملية بيع عملة أجنبية وإضافة قيمتها للصندوق')
                ->modalWidth('4xl')
                ->using(fn(array $data) => app(AccountingService::class)->postSellCurrency(SellCurrencyDTO::fromArray($data))),
        ];
    }
}
