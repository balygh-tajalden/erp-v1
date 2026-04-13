<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSyncService
{
    /**
     * تزامن المجموعات والصلاحيات من الجداول الأصلية إلى Spatie
     */
    public function syncUser(User $user): void
    {
        // 1. مزامنة المجموعة (Role)
        $legacyGroup = DB::table('tblUserGroups')->where('ID', $user->UserGroupID)->first();
        
        if ($legacyGroup) {
            $roleName = $legacyGroup->GroupName;
            $role = Role::findOrCreate($roleName, 'web');
            
            if (!$user->hasRole($roleName)) {
                // تصفير الأدوار القديمة وإضافة الدور الجديد
                $user->syncRoles([$roleName]);
            }

            // 2. مزامنة الصلاحيات لهذه المجموعة
            $this->syncRolePermissions($role, $legacyGroup->ID);
        }

        // 3. مزامنة صلاحيات المستخدم المباشرة (اختياري حسب نظامك)
        $this->syncUserDirectPermissions($user);
    }

    protected function syncRolePermissions(Role $role, int $groupId): void
    {
        $legacyPermissions = DB::table('tblPermissionsAccess')
            ->where('TargetType', 'Group')
            ->where('TargetID', $groupId)
            ->get();

        foreach ($legacyPermissions as $legacy) {
            $formCode = $legacy->FormCode;
            $values = explode(',', $legacy->PermissionValues); // مثلاً 'View,Create,Update'

            foreach ($values as $val) {
                if (empty(trim($val))) continue;
                
                $permissionName = $formCode . '.' . trim($val);
                $permission = Permission::findOrCreate($permissionName, 'web');
                
                if (!$role->hasPermissionTo($permissionName)) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }

    protected function syncUserDirectPermissions(User $user): void
    {
        $legacyPermissions = DB::table('tblPermissionsAccess')
            ->where('TargetType', 'User')
            ->where('TargetID', $user->ID)
            ->get();

        foreach ($legacyPermissions as $legacy) {
            $formCode = $legacy->FormCode;
            $values = explode(',', $legacy->PermissionValues);

            foreach ($values as $val) {
                if (empty(trim($val))) continue;
                
                $permissionName = $formCode . '.' . trim($val);
                $permission = Permission::findOrCreate($permissionName, 'web');
                
                if (!$user->hasDirectPermission($permissionName)) {
                    $user->givePermissionTo($permission);
                }
            }
        }
    }
}
