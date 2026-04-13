<?php

namespace App\Filament\Resources\PermissionAccesses;

use App\Filament\Resources\PermissionAccesses\Pages\CreatePermissionAccess;
use App\Filament\Resources\PermissionAccesses\Pages\EditPermissionAccess;
use App\Filament\Resources\PermissionAccesses\Pages\ListPermissionAccesses;
use App\Filament\Resources\PermissionAccesses\Schemas\PermissionAccessForm;
use App\Filament\Resources\PermissionAccesses\Tables\PermissionAccessesTable;
use App\Filament\Resources\BaseResource;
use App\Models\PermissionAccess;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PermissionAccessResource extends BaseResource
{
    protected static ?string $model = PermissionAccess::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';
    protected static ?string $navigationLabel = 'إدارة الصلاحيات';
    protected static ?string $pluralLabel = 'الصلاحيات';
    protected static ?string $modelLabel = 'صلاحية';

    public static function form(Schema $schema): Schema
    {
        return PermissionAccessForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionAccessesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    protected static bool $shouldRegisterNavigation = false;


    public static function getPages(): array
    {
        return [
            'index' => ListPermissionAccesses::route('/'),
        ];
    }
}
