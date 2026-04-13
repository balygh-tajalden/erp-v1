<?php

namespace App\Filament\Resources\CurrencyPrices\Pages;

use App\Filament\Resources\CurrencyPrices\CurrencyPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCurrencyPrices extends ListRecords
{
    protected static string $resource = CurrencyPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
