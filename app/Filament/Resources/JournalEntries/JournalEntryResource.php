<?php

namespace App\Filament\Resources\JournalEntries;

use App\Filament\Resources\JournalEntries\Pages\CreateJournalEntry;
use App\Filament\Resources\JournalEntries\Pages\EditJournalEntry;
use App\Filament\Resources\JournalEntries\Pages\ListJournalEntries;
use App\Filament\Resources\JournalEntries\Pages\ViewJournalEntry;
use App\Filament\Resources\JournalEntries\Schemas\JournalEntryForm;
use App\Filament\Resources\JournalEntries\Schemas\JournalEntryInfolist;
use App\Filament\Resources\JournalEntries\Tables\JournalEntriesTable;
use App\Filament\Resources\BaseResource;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Entry;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JournalEntryResource extends BaseResource
{
    protected static ?string $model = Entry::class;
    
    protected static ?int $documentTypeID = 5; // سند قيد مزدوج

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('DocumentID', static::$documentTypeID);
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|\UnitEnum|null $navigationGroup = 'الحسابات';

    protected static ?string $navigationLabel = 'قيد مزدوج';

    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 2;

    protected static ?string $pluralLabel = 'قيود مزدوجة';

    protected static ?string $modelLabel = 'قيود مزدوجة';

    protected static ?string $recordTitleAttribute = 'record_number';

    public static function form(Schema $schema): Schema
    {
        return JournalEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JournalEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JournalEntriesTable::configure($table);
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
            'index' => ListJournalEntries::route('/'),
        ];
    }

}
