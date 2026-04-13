<?php

namespace App\Filament\Resources\ClientsSMS;

use App\Filament\Resources\ClientsSMS\Pages\CreateClientsSMS;
use App\Filament\Resources\ClientsSMS\Pages\EditClientsSMS;
use App\Filament\Resources\ClientsSMS\Pages\ListClientsSMS;
use App\Filament\Resources\ClientsSMS\Schemas\ClientsSMSForm;
use App\Filament\Resources\ClientsSMS\Tables\ClientsSMSTable;
use App\Filament\Resources\ClientsSMS\RelationManagers\SmsRechargesRelationManager;
use App\Filament\Resources\ClientsSMS\RelationManagers\DeviceLicensesRelationManager;
use App\Models\ClientsSMS;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClientsSMSResource extends Resource
{
    protected static ?string $model = ClientsSMS::class;

    protected static ?string $navigationLabel = 'عملاء الـ API';
    protected static ?string $pluralModelLabel = 'عملاء الـ API';
    protected static ?string $modelLabel = 'عميل API';
    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ClientsSMSForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsSMSTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DeviceLicensesRelationManager::class,
            SmsRechargesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientsSMS::route('/'),
            'view' => \App\Filament\Resources\ClientsSMS\Pages\ViewClientsSMS::route('/{record}'),
        ];
    }
}
