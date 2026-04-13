<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\History;
use Illuminate\Support\Facades\Log;

trait HasAuditTrail
{
    public $__audit_old_data = null;
    public $__audit_new_data = null;

    /**
     * Get the user who created the record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'ID');
    }

    public static function bootHasAuditTrail()
    {
        static::creating(function ($model) {
            if (!$model->CreatedBy && Auth::check()) {
                $model->CreatedBy = Auth::user()->ID;
            }
        });

        static::created(function ($model) {
            static::recordHasAuditHistory($model, 'insert');
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->ModifiedBy = Auth::user()->ID;
            }
            // تخزين البيانات القديمة والجديدة بمتغيرات خصائص (Properties) صريحة لمنع لارافل من تخيلها كأعمدة
            $model->__audit_old_data = $model->getOriginal();
            $model->__audit_new_data = $model->getDirty();
        });

        static::updated(function ($model) {
            static::recordHasAuditHistory($model, 'update');
        });

        static::deleted(function ($model) {
            static::recordHasAuditHistory($model, 'delete');
        });
    }

    protected static function recordHasAuditHistory($model, $actionType)
    {
        // تجنب صنع حلقة لانهائية إذا استخدم الموديل History هذا المتتبع بمحض الصدفة
        if ($model->getTable() === 'tblhistory') return;

        $oldData = null;
        $newData = null;

        if ($actionType === 'insert') {
            $newData = $model->toArray();
        } elseif ($actionType === 'delete') {
            $oldData = $model->toArray();
        } elseif ($actionType === 'update') {
            $dirty = $model->__audit_new_data ?? [];
            $original = $model->__audit_old_data ?? [];
            
            $oldData = [];
            $newData = [];
            
            foreach ($dirty as $key => $value) {
                // تجاهل الحقول الروتينية التي تتعدل آلياً مع كل حفظ
                if (in_array($key, ['updated_at', 'ModifiedDate', 'ModifiedBy', 'deleted_at'])) {
                    continue;
                }
                $oldData[$key] = $original[$key] ?? null;
                $newData[$key] = $value;
            }
            
            // تنظيف المتغيرات المؤقتة
            $model->__audit_old_data = null;
            $model->__audit_new_data = null;
            
            // إذا لم يتم تعديل حقول مهمة، نتجاهل العملية ولا نسجلها
            if (empty($newData)) {
                return;
            }
        }

        // جدول tblhistory يتوقع أن يكون نوع العملية رقماً (1: إضافة، 2: تعديل، 3: حذف)
        $actionIntMap = [
            'insert' => 1,
            'update' => 2,
            'delete' => 3
        ];
        $actionTypeValue = $actionIntMap[$actionType] ?? 0;

        try {
            History::create([
                'TableName' => $model->getTable(),
                'RecordID'  => $model->getKey(),
                'ChangedBy' => Auth::id(),
                'ChangeDate'=> now(),
                'OldData'   => $oldData,
                'NewData'   => $newData,
                'ActionType' => $actionTypeValue,
                'UserID'    => Auth::id(),
                'BranchID'  => $model->BranchID ?? null,
                'SessionID' => $model->SessionID ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error("Audit Log Auto-Failure: " . $e->getMessage());
        }
    }

    /**
     * Get the user who last modified the record.
     */
    public function updater()
    {
        if (!property_exists($this, 'ModifiedBy') && !array_key_exists('ModifiedBy', $this->getAttributes())) {
            return null;
        }
        return $this->belongsTo(User::class, 'ModifiedBy', 'ID');
    }
}
