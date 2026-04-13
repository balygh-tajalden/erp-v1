<?php

namespace App\Filament\Resources\ServiceProviders;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\ServiceProviders\Pages\CreateServiceProviders;
use App\Filament\Resources\ServiceProviders\Pages\EditServiceProviders;
use App\Filament\Resources\ServiceProviders\Pages\ListServiceProviders;
use App\Filament\Resources\ServiceProviders\Schemas\ServiceProvidersForm;
use App\Filament\Resources\ServiceProviders\Tables\ServiceProvidersTable;
use App\Models\ServiceProvider;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceProvidersResource extends BaseResource
{
    protected static ?string $model = ServiceProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServer;

    protected static string|\UnitEnum|null $navigationGroup = 'خدمات الشحن';

    protected static ?string $navigationLabel = 'إدارة الشبكات';

    protected static ?string $pluralLabel = 'شبكات الشحن';

    protected static ?string $modelLabel = 'شبكة شحن';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ServiceProvidersForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceProvidersTable::configure($table);
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
            'index' => ListServiceProviders::route('/'),

        ];
    }
}
