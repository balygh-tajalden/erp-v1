<?php

namespace App\Filament\Resources\BuyCurrencies;

use App\Filament\Resources\BuyCurrencies\Pages\CreateBuyCurrency;
use App\Filament\Resources\BuyCurrencies\Pages\EditBuyCurrency;
use App\Filament\Resources\BuyCurrencies\Pages\ListBuyCurrencies;
use App\Filament\Resources\BuyCurrencies\Schemas\BuyCurrencyForm;
use App\Filament\Resources\BuyCurrencies\Tables\BuyCurrenciesTable;
use App\Models\BuyCurrency;
use App\Models\Account;
use App\Filament\Resources\BaseResource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BuyCurrencies\Schemas\BuyCurrencyInfolist;

class BuyCurrencyResource extends BaseResource
{
    protected static ?string $model = BuyCurrency::class;

    protected static ?int $documentTypeID = 7; // سند شراء عمله


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownLeft;

    protected static ?string $recordTitleAttribute = 'شراء عملة';
    protected static ?string $modelLabel = 'شراء عملة';
    protected static ?string $pluralModelLabel = 'شراء العملات';
    protected static string|\UnitEnum|null $navigationGroup = 'بيع وشراء العملات';
    protected static ?string $navigationLabel = 'شراء عملة';

    public static function form(Schema $schema): Schema
    {
        return BuyCurrencyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BuyCurrencyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BuyCurrenciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('tblBuyCurrencies.*') 
            ->with(['viewDetails']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBuyCurrencies::route('/'),
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
