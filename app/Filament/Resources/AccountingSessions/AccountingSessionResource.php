<?php

namespace App\Filament\Resources\AccountingSessions;

use App\Filament\Resources\AccountingSessions\Pages\CreateAccountingSession;
use App\Filament\Resources\AccountingSessions\Pages\EditAccountingSession;
use App\Filament\Resources\AccountingSessions\Pages\ListAccountingSessions;
use App\Filament\Resources\AccountingSessions\Schemas\AccountingSessionForm;
use App\Filament\Resources\AccountingSessions\Tables\AccountingSessionsTable;
use App\Models\AccountingSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountingSessionResource extends Resource
{
    protected static ?string $model = AccountingSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'الجلسات';
    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';
    protected static ?string $navigationLabel = 'الجلسات';

    public static function form(Schema $schema): Schema
    {
        return AccountingSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountingSessionsTable::configure($table);
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
            'index' => ListAccountingSessions::route('/'),
        ];
    }
}
