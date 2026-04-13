<?php

namespace App\Filament\Resources\CurrencyPrices;

use App\Filament\Resources\CurrencyPrices\Pages\CreateCurrencyPrice;
use App\Filament\Resources\CurrencyPrices\Pages\EditCurrencyPrice;
use App\Filament\Resources\CurrencyPrices\Pages\ListCurrencyPrices;
use App\Filament\Resources\CurrencyPrices\Schemas\CurrencyPriceForm;
use App\Filament\Resources\CurrencyPrices\Tables\CurrencyPricesTable;
use App\Filament\Resources\BaseResource;
use App\Models\CurrencyPrice;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


class CurrencyPriceResource extends BaseResource
{
    protected static ?string $model = CurrencyPrice::class;

    protected static string|\UnitEnum|null $navigationGroup = 'العملات';

    protected static ?string $navigationLabel = 'أسعار العملات';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $pluralLabel = 'أسعار العملات';

    protected static ?string $modelLabel = 'سعر العملة';

    public static function form(Schema $schema): Schema
    {
        return CurrencyPriceForm::configure($schema);
    }


    public static function table(Table $table): Table
    {
        return CurrencyPricesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCurrencyPrices::route('/'),
        ];
    }
}
