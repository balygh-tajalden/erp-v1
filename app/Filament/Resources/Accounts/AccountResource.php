<?php

namespace App\Filament\Resources\Accounts;

use App\Filament\Resources\Accounts\Pages\CreateAccount;
use App\Filament\Resources\Accounts\Pages\EditAccount;
use App\Filament\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Resources\Accounts\Schemas\AccountForm;
use App\Filament\Resources\Accounts\Schemas\AccountInfolist;
use App\Filament\Resources\Accounts\Tables\AccountsTable;
use App\Filament\Resources\Accounts\RelationManagers\WalletsRelationManager;
use App\Filament\Resources\BaseResource;
use App\Models\Account;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountResource extends BaseResource
{
    protected static ?string $model = Account::class;

    protected static ?int $documentTypeID = 11; // دليل الحسابات

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'الحسابات';

    protected static ?string $navigationLabel = 'دليل الحسابات';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralLabel = 'الحسابات';

    protected static ?string $modelLabel = 'حساب';

    protected static ?string $recordTitleAttribute = 'AccountName';

    public static function form(Schema $schema): Schema
    {
        return AccountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            WalletsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),

        ];
    }
}
