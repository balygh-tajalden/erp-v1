<?php

namespace App\Filament\Resources\SystemPermissions;

use App\Filament\Resources\SystemPermissions\Pages\ManageSystemPermissions;
use App\Filament\Resources\BaseResource;
use App\Models\SystemPermission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Grouping\Group;

class SystemPermissionResource extends BaseResource
{
    protected static ?string $model = SystemPermission::class;

    protected static string|\UnitEnum|null $navigationGroup = 'الأمان والصلاحيات';
    protected static ?string $modelLabel           = 'قاموس الصلاحيات';
    protected static ?string $pluralModelLabel     = 'قاموس الصلاحيات المتقدمة';
    protected static bool $shouldRegisterNavigation = false;


    // ─────────────────────────────────────────────────
    //  Form
    // ─────────────────────────────────────────────────
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('PermissionCode')
                    ->label('كود الصلاحية')
                    ->helperText('يستخدم داخلياً — مثال: allow_backdate')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->placeholder('example_permission_code'),

                TextInput::make('ArabicName')
                    ->label('الاسم العربي الرسمي')
                    ->helperText('يظهر للمستخدم في شاشة تعيين الصلاحيات')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('السماح بتعديل القيود المرحلة'),

                TextInput::make('Category')
                    ->label('التصنيف / المجموعة')
                    ->helperText('يُجمّع الصلاحيات المتشابهة في قسم واحد')
                    ->required()
                    ->maxLength(100)
                    ->datalist([
                        '1. القيود والسندات الأساسية',
                        '2. إدارة الحسابات',
                        '3. الصناديق والعملات',
                        '4. التقارير والاستعلامات',
                        '5. إعدادات النظام والأمان',
                    ])
                    ->placeholder('اختر تصنيفاً أو اكتب جديداً'),

                Toggle::make('IsActive')
                    ->label('مفعّل ويظهر في الشاشات')
                    ->helperText('إيقاف التفعيل يخفي الصلاحية من شاشة التعيين')
                    ->default(true),
            ]);
    }

    // ─────────────────────────────────────────────────
    //  Table
    // ─────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    TextColumn::make('ArabicName')
                        ->label('الاسم الرسمي')
                        ->weight('bold')
                        ->searchable(),
                    TextColumn::make('PermissionCode')
                        ->label('الكود')
                        ->color('gray')
                        ->fontFamily('mono')
                        ->copyable()
                        ->copyMessage('تم نسخ الكود!')
                        ->searchable(),
                    Split::make([
                        ToggleColumn::make('IsActive')->label('مفعّل'),
                    ]),
                ])->space(2),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->defaultGroup('Category')
            ->groups([
                Group::make('Category')
                    ->label('التصنيف')
                    ->collapsible(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSystemPermissions::route('/'),
        ];
    }
}
