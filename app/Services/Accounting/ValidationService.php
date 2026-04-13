<?php

namespace App\Services\Accounting;

use App\Services\BaseService;
use App\Models\EntryDetail;
use Filament\Notifications\Notification;

class ValidationService extends BaseService
{
    /**
     * Replaces sp_ValidateEntry
     */
    public function validateEntry($entryId)
    {
        $sum = EntryDetail::where('ParentID', $entryId)->sum('Amount');
        return round($sum, 4) === 0.0000;
    }

    /**
     * التحقق من أن العملة المحددة ليست العملة المحلية
     */
    public function validateForeignCurrency($currencyId, $actionName = 'العملية المحددة')
    {
        if ((int) $currencyId === 1) { // 1 = ریال يمني
            Notification::make()
                ->title('خطأ في العملة')
                ->body("لا يمكن {$actionName} العملة المحلية (الريال اليمني).")
                ->danger()
                ->send();
            return false;
        }
        return true;
    }

    /**
     * التحقق من وجود سعر صرف بين عملتين
     */
    public function validateExchangeRate($sourceId, $targetId)
    {
        if (!$sourceId || !$targetId) return true;
        if ($sourceId == $targetId) return true;

        $rate = app(\App\Services\AccountingService::class)->getExchangeRate($sourceId, $targetId);

        if (empty($rate)) {
            Notification::make()
                ->title('خطأ في سعر الصرف')
                ->body('لا يوجد سعر صرف مسجل لهذه العملات.')
                ->danger()
                ->send();
            return false;
        }

        return true;
    }

    /**
     * التحقق من أن العملتين مختلفتين
     */
    public function validateDifferentCurrencies($sourceId, $targetId)
    {
        if ($sourceId && $targetId && $sourceId == $targetId) {
            Notification::make()
                ->title('خطأ في العملات')
                ->body('يجب أن تكون العملتان (الأولى والثانية) مختلفتين.')
                ->danger()
                ->send();
            return false;
        }
        return true;
    }

    /**
     * التحقق من فرادة رقم السند للسنة والفرع
     */
    public function validateTheNumberUnique($model, $number, $branchId, $year, $excludeId = null)
    {
        $query = $model::where('TheNumber', $number)
            ->where('BranchID', $branchId)
            ->whereYear('TheDate', $year);

        if ($excludeId) {
            $query->where('ID', '!=', $excludeId);
        }

        if ($query->exists()) {
            Notification::make()
                ->title('رقم مكرر')
                ->body('رقم السند هذا مستخدم مسبقاً لهذا الفرع والسنة.')
                ->danger()
                ->send();
            return false;
        }
        return true;
    }
}
