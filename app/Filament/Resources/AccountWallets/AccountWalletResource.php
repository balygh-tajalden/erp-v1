<?php

namespace App\Filament\Resources\AccountWallets;

use App\Filament\Resources\AccountWallets\Pages\CreateAccountWallet;
use App\Filament\Resources\AccountWallets\Pages\EditAccountWallet;
use App\Filament\Resources\AccountWallets\Pages\ListAccountWallets;
use App\Filament\Resources\AccountWallets\Schemas\AccountWalletForm;
use App\Filament\Resources\AccountWallets\Tables\AccountWalletsTable;
use App\Models\AccountWallet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountWalletResource extends Resource
{
    protected static ?string $model = AccountWallet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;
    protected static string|\UnitEnum|null $navigationGroup = 'المحافظ الإلكترونية';
    protected static ?string $navigationLabel = 'ربط الحسابات بالمحافظ';
    protected static ?string $pluralLabel = 'ربط الحسابات بالمحافظ';
    protected static ?string $modelLabel = 'ربط محفظة';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AccountWalletForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountWalletsTable::configure($table);
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
            'index' => ListAccountWallets::route('/'),
            
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
