<?php

namespace App\Filament\Resources\UserGroups;

use App\Filament\Resources\UserGroups\Pages\ManageUserGroups;
use App\Filament\Resources\BaseResource;
use App\Models\UserGroup;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Auth;

class UserGroupResource extends BaseResource
{
    protected static ?string $model = UserGroup::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';
    protected static ?string $navigationLabel = 'مجموعات المستخدمين';
    protected static ?string $pluralLabel = 'المجموعات';
    protected static ?string $modelLabel = 'مجموعة';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('GroupName')
                    ->label('اسم المجموعة')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('GroupNumber')
                    ->label('رقم المجموعة')
                    ->numeric(),
                Select::make('BranchID')
                    ->label('الفرع')
                    ->options(self::getBranchOptions())
                    ->default(2)
                    ->required(),
                Toggle::make('IsActive')
                    ->label('الحالة')
                    ->default(true),
                Textarea::make('Description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                Hidden::make('CreatedBy')
                    ->default(Auth::id()),
                Hidden::make('ModifiedBy')
                    ->default(Auth::id())
                    ->dehydrateStateUsing(fn() => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('GroupNumber')
                    ->label('رقم المجموعة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('GroupName')
                    ->label('اسم المجموعة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('branch.BranchName')
                    ->label('الفرع')
                    ->sortable(),
                TextColumn::make('Description')
                    ->label('الوصف')
                    ->limit(30),
                IconColumn::make('IsActive')
                    ->label('الحالة')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                 
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUserGroups::route('/'),
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
