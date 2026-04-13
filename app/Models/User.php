<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable implements HasName, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $table = 'tblUsers';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    /**
     * الحقول المسموح بتعبئتها
     */
    protected $fillable = [
        'UserName',
        'Phone',
        'UserGroupID',
        'UserPassword',
        'IsActive',
        'CreatedBy',
        'BranchID',
        'Notes',
        'LastLoginDate',
        'ModifiedBy',
    ];

    /**
     * الحقول المخفية
     */
    protected $hidden = [
        'UserPassword',
        'remember_token',
    ];

    /**
     * تخصيص حقل كلمة المرور لـ Laravel Auth
     */
    public function getAuthPasswordName()
    {
        return 'UserPassword';
    }

    /**
     * تخصيص حقل المعرف (Username) لـ Laravel Auth
     */
    public function getAuthIdentifierName()
    {
        return 'ID';
    }

    /**
     * التحقق من الصلاحية من جدول tblPermissionsAccess
     */
    public function hasPermission(string $formCode, string $permissionType): bool
    {
        // إذا كان المستخدم admin، فله كل الصلاحيات
        if ($this->UserName === 'admin') {
            return true;
        }

        $version = Cache::rememberForever('perm_version', fn() => time());
        $cacheKey = "user_perm_{$this->ID}_{$formCode}_{$permissionType}_{$version}";

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($formCode, $permissionType) {
            return DB::table('tblPermissionsAccess')
                ->where('FormCode', $formCode)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        // البحث بصلاحية المستخدم مباشرة
                        $q->where('TargetType', 'User')
                            ->where('TargetID', $this->ID);
                    })->orWhere(function ($q) {
                        // البحث بصلاحية المجموعة التي ينتمي إليها المستخدم
                        $q->where('TargetType', 'Group')
                            ->where('TargetID', $this->UserGroupID);
                    });
                })
                ->where('PermissionValues', 'like', "%$permissionType%")
                ->exists();
        });
    }

    /**
     * تحويل أنواع البيانات
     */
    protected function casts(): array
    {
        return [
            'CreatedDate' => 'datetime',
            'ModifiedDate' => 'datetime',
            'LastLoginDate' => 'datetime',
            'IsActive' => 'boolean',
            'UserPassword' => 'hashed',
        ];
    }

    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class, 'UserGroupID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function sessions()
    {
        return $this->hasMany(AccountingSession::class, 'user_id', 'ID');
    }

    public function groups()
    {
        return $this->belongsToMany(UserGroup::class, 'tblUserGroupMapping', 'UserID', 'GroupID')
            ->withPivot('AssignedBy', 'AssignedDate');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }

    public function getFilamentName(): string
    {
        return $this->UserName;
    }

    /**
     * التحقق من صلاحية دخول لوحة التحكم
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // يسمح بالدخول للمستخدم admin أو أي مستخدم نشط
        return $this->IsActive;
    }
}
