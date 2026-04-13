<?php

namespace App\Filament\Resources\SimpleEntries;

use App\Filament\Resources\SimpleEntries\Pages\CreateSimpleEntry;
use App\Filament\Resources\SimpleEntries\Pages\EditSimpleEntry;
use App\Filament\Resources\SimpleEntries\Pages\ListSimpleEntries;
use App\Filament\Resources\SimpleEntries\Pages\ViewSimpleEntry;
use App\Filament\Resources\SimpleEntries\Schemas\SimpleEntryForm;
use App\Filament\Resources\SimpleEntries\Schemas\SimpleEntryInfolist;
use App\Filament\Resources\SimpleEntries\Tables\SimpleEntriesTable;
use App\Filament\Resources\BaseResource;
use App\Models\SimpleEntry;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SimpleEntryResource extends BaseResource
{
    protected static ?string $model = SimpleEntry::class;
    
    protected static ?int $documentTypeID = 4; // سند قيد بسيط

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|\UnitEnum|null $navigationGroup = 'الحسابات';

    protected static ?string $navigationLabel = 'قيد بسيط';

    protected static ?int $navigationSort = 3;

    protected static ?string $pluralLabel = 'قيود بسيطة';

    protected static ?string $modelLabel = 'قيد بسيط';

    protected static ?string $recordTitleAttribute = 'record_number';

    public static function form(Schema $schema): Schema
    {
        return SimpleEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SimpleEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SimpleEntriesTable::configure($table);
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
            'index' => ListSimpleEntries::route('/'),
            
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
