<?php

namespace App\Filament\Resources\SellCurrencies;

use App\Filament\Resources\SellCurrencies\Pages\CreateSellCurrency;
use App\Filament\Resources\SellCurrencies\Pages\EditSellCurrency;
use App\Filament\Resources\SellCurrencies\Pages\ListSellCurrencies;
use App\Filament\Resources\SellCurrencies\Schemas\SellCurrencyForm;
use App\Filament\Resources\SellCurrencies\Tables\SellCurrenciesTable;
use App\Models\SellCurrency;
use App\Filament\Resources\BaseResource;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\SellCurrencies\Schemas\SellCurrencyInfolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SellCurrencyResource extends BaseResource
{
    protected static ?string $model = SellCurrency::class;

    protected static ?int $documentTypeID = 8; // سند بيع عمله

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpRight;

    protected static ?string $modelLabel = 'بيع عملة';
    protected static ?string $pluralModelLabel = 'بيع العملات';
    protected static ?string $recordTitleAttribute = 'بيع عملة';
    protected static string|\UnitEnum|null $navigationGroup = 'بيع وشراء العملات';
    protected static ?string $navigationLabel = 'بيع عملة';

    public static function form(Schema $schema): Schema
    {
        return SellCurrencyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SellCurrencyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SellCurrenciesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('tblSellCurrencies.*')
            ->with(['viewDetails']);
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
            'index' => ListSellCurrencies::route('/'),

        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
