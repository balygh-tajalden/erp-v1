<?php

namespace App\Filament\Resources\PermissionAccesses\Schemas;

use App\Models\DocumentType;
use App\Models\SystemPermission;
use App\Models\User;
use App\Models\UserGroup;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use App\Filament\Resources\PermissionAccesses\PermissionAccessResource;

class PermissionAccessForm
{
    // ─────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────

    /** كود كل فئة ديناميكياً بدون تعارض في أسماء الحقول */
    private static function catField(string $category): string
    {
        return 'Adv_' . md5($category);
    }

    /** قراءة كل الفئات النشطة مجمّعة */
    private static function groupedPermissions()
    {
        return SystemPermission::where('IsActive', true)
            ->orderBy('Category')
            ->get()
            ->groupBy('Category');
    }

    // ─────────────────────────────────────────────────────────────────
    //  Schema
    // ─────────────────────────────────────────────────────────────────

    public static function configure(Schema $schema): Schema
    {
        // بناء الأقسام المطوية للصلاحيات المتقدمة ديناميكياً من قاعدة البيانات
        $advancedSections = [];
        foreach (self::groupedPermissions() as $category => $perms) {
            $advancedSections[] = Section::make($category)
                ->schema([
                    CheckboxList::make(self::catField($category))
                        ->label('')
                        ->options($perms->pluck('ArabicName', 'PermissionCode')->toArray())
                        ->columns([3])
                        ->bulkToggleable()
                        ->dehydrated(false)
                        ->live(),
                ])
                ->compact()
                ->collapsible()
                ->collapsed();
        }

        return $schema
            ->components([

                // ── قسم بيانات تعيين الصلاحية ───────────────────────
                Section::make('بيانات تعيين الصلاحية')
                    ->description('حدد الجهة التي ستطبق عليها الصلاحية والشاشة المعنية')
                    ->schema([
                        Select::make('TargetType')
                            ->label('تطبيق الصلاحية على')
                            ->options([
                                'User'  => ' مستخدم ',
                                'Group' => ' مجموعة',
                            ])
                            ->required()
                            ->live()
                            ->placeholder('اختر نوع الجهة المستهدفة'),

                        Select::make('TargetID')
                            ->label('المستخدم / المجموعة')
                            ->searchable()
                            ->required()
                            ->placeholder('ابحث عن المستخدم أو المجموعة')
                            ->getSearchResultsUsing(function (string $search, callable $get): array {
                                return match ($get('TargetType')) {
                                    'User'  => User::where('UserName', 'like', "%{$search}%")->limit(50)->pluck('UserName', 'ID')->toArray(),
                                    'Group' => UserGroup::where('GroupName', 'like', "%{$search}%")->limit(50)->pluck('GroupName', 'ID')->toArray(),
                                    default => [],
                                };
                            })
                            ->getOptionLabelUsing(function ($value, callable $get): ?string {
                                return match ($get('TargetType')) {
                                    'User'  => User::find($value)?->UserName,
                                    'Group' => UserGroup::find($value)?->GroupName,
                                    default => null,
                                };
                            }),

                        Select::make('FormCode')
                            ->label('المستند / الشاشة')
                            ->helperText('الشاشة التي ستُطبَّق عليها هذه الصلاحية')
                            ->options(PermissionAccessResource::getDocumentTypeOptions())
                            ->required()
                            ->searchable()
                            ->placeholder('ابحث عن المستند'),
                    ])
                    ->columns([3])
                    ->columnSpanFull(),

                // ── تبويبات الصلاحيات ───────────────────────────────
                Tabs::make('الصلاحيات')
                    ->tabs([
                        Tab::make('أساسية (CRUD)')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                CheckboxList::make('CrudPermissions')
                                    ->label('الصلاحيات الأساسية')
                                    ->options([
                                        'view'   => 'عرض',
                                        'add'    => 'إضافة',
                                        'edit'   => 'تعديل',
                                        'delete' => 'حذف',
                                    ])
                                    ->columns(4)
                                    ->bulkToggleable()
                                    ->dehydrated(false)
                                    ->live(),
                            ]),

                        Tab::make('متقدمة (Business Rules)')
                            ->icon('heroicon-o-lock-closed')
                            ->schema($advancedSections),
                    ])
                    ->columnSpanFull(),

                // ── حقل التخزين المدمج (Hidden) ─────────────────────
                Hidden::make('PermissionValues')
                    ->default('')
                    ->afterStateHydrated(function ($component, $state, callable $set) {
                        $all       = array_filter(explode(',', str_replace(' ', '', (string) $state)));
                        $basicKeys = ['view', 'add', 'edit', 'delete'];

                        // الصلاحيات الأساسية
                        $set('CrudPermissions', array_values(array_intersect($all, $basicKeys)));

                        // الصلاحيات المتقدمة — كل فئة في حقلها
                        foreach (self::groupedPermissions() as $category => $perms) {
                            $catKeys = $perms->pluck('PermissionCode')->toArray();
                            $set(self::catField($category), array_values(array_intersect($all, $catKeys)));
                        }
                    })
                    ->dehydrateStateUsing(function (callable $get): string {
                        $crud    = $get('CrudPermissions') ?? [];
                        $advanced = [];

                        foreach (self::groupedPermissions() as $category => $perms) {
                            $advanced = array_merge($advanced, $get(self::catField($category)) ?? []);
                        }

                        return implode(',', array_filter(array_merge($crud, $advanced)));
                    }),

            ]);
    }
}
