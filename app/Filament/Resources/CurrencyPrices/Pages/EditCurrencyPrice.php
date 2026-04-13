<?php

namespace App\Filament\Resources\CurrencyPrices\Pages;

use App\Filament\Resources\CurrencyPrices\CurrencyPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCurrencyPrice extends EditRecord
{
    protected static string $resource = CurrencyPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
