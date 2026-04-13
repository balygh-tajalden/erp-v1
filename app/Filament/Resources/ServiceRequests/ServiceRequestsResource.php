<?php

namespace App\Filament\Resources\ServiceRequests;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\ServiceRequests\Pages\CreateServiceRequests;
use App\Filament\Resources\ServiceRequests\Pages\EditServiceRequests;
use App\Filament\Resources\ServiceRequests\Pages\ListServiceRequests;
use App\Filament\Resources\ServiceRequests\Schemas\ServiceRequestsForm;
use App\Filament\Resources\ServiceRequests\Tables\ServiceRequestsTable;
use App\Models\ServiceRequest;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceRequestsResource extends BaseResource
{
    protected static ?string $model = ServiceRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'خدمات الشحن';

    protected static ?string $navigationLabel = 'تاريخ العمليات';

    protected static ?string $pluralLabel = 'تاريخ العمليات';

    protected static ?string $modelLabel = 'عملية شحن';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ServiceRequestsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceRequests::route('/'),
        ];
    }
}
