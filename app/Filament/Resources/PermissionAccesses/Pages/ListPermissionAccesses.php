<?php

namespace App\Filament\Resources\PermissionAccesses\Pages;

use App\Filament\Resources\PermissionAccesses\PermissionAccessResource;
use App\Models\DocumentType;
use App\Models\PermissionAccess;
use App\Models\User;
use App\Models\UserGroup;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListPermissionAccesses extends ListRecords
{
    protected static string $resource = PermissionAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── الإضافة الفردية العادية ──────────────────────────────
            CreateAction::make()
                ->label('إضافة صلاحية'),

            // ── تعيين صلاحيات لجميع الشاشات دفعة واحدة ─────────────
            Action::make('assignAllScreens')
                ->label(' تعيين لجميع الشاشات')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->schema([
                    Select::make('target_type')
                        ->label('تطبيق الصلاحية على')
                        ->options([
                            'User'  => ' مستخدم فردي',
                            'Group' => ' مجموعة مستخدمين',
                        ])
                        ->required()
                        ->live(),

                    Select::make('target_id')
                        ->label('المستخدم / المجموعة')
                        ->required()
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search, callable $get): array {
                            return match ($get('target_type')) {
                                'User'  => User::where('UserName', 'like', "%{$search}%")->limit(50)->pluck('UserName', 'ID')->toArray(),
                                'Group' => UserGroup::where('GroupName', 'like', "%{$search}%")->limit(50)->pluck('GroupName', 'ID')->toArray(),
                                default => [],
                            };
                        })
                        ->getOptionLabelUsing(function ($value, callable $get): ?string {
                            return match ($get('target_type')) {
                                'User'  => User::find($value)?->UserName,
                                'Group' => UserGroup::find($value)?->GroupName,
                                default => null,
                            };
                        }),

                    CheckboxList::make('permissions')
                        ->label('الصلاحيات التي ستُطبَّق على جميع الشاشات')
                        ->options([
                            'view'   => 'عرض',
                            'add'    => 'إضافة',
                            'edit'   => 'تعديل',
                            'delete' => 'حذف',
                        ])
                        ->gridDirection('row')
                        ->columns([4])
                        ->bulkToggleable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $screens     = DocumentType::all();
                    $permsString = implode(',', $data['permissions']);
                    $now         = now();
                    $inserted    = 0;

                    DB::transaction(function () use ($screens, $data, $permsString, $now, &$inserted) {
                        foreach ($screens as $screen) {
                            // تجنب التكرار — تحديث إذا موجود، إضافة إذا غير موجود
                            $existing = PermissionAccess::where('TargetType', $data['target_type'])
                                ->where('TargetID', $data['target_id'])
                                ->where('FormCode', $screen->ID)
                                ->first();

                            if ($existing) {
                                $existing->update(['PermissionValues' => $permsString]);
                            } else {
                                PermissionAccess::create([
                                    'TargetType'       => $data['target_type'],
                                    'TargetID'         => $data['target_id'],
                                    'FormCode'         => $screen->ID,
                                    'PermissionValues' => $permsString,
                                    'CreatedDate'      => $now,
                                ]);
                                $inserted++;
                            }
                        }
                    });

                    Notification::make()
                        ->title('تم تعيين الصلاحيات بنجاح ')
                        ->body("تم تطبيق الصلاحيات على {$screens->count()} شاشة ({$inserted} جديدة، " . ($screens->count() - $inserted) . ' محدّثة)')
                        ->success()
                        ->send();
                }),
        ];
    }
}
