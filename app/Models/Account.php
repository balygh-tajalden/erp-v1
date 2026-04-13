<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblAccounts';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'FatherNumber',
        'AccountName',
        'AccountNumber',
        'AccountReference',
        'CreatedBy',
        'BranchID',
        'SessionID',
        'AccountCode',
        'AccountTypeID',
        'ModifiedBy',
        'IsCustomer',
        'IsSupplier',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'date', // Note: migration says date, not dateTime for ModifiedDate in tblAccounts
        'IsCustomer' => 'boolean',
        'IsSupplier' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'FatherNumber', 'AccountNumber');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'FatherNumber', 'AccountNumber');
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'AccountTypeID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function balances()
    {
        return $this->hasMany(AccountBalance::class, 'AccountID', 'ID');
    }

    public function limits()
    {
        return $this->hasMany(AccountLimit::class, 'AccountID', 'ID');
    }

    public function wallets()
    {
        return $this->hasMany(AccountWallet::class, 'AccountID', 'ID');
    }

    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public function isLeaf()
    {
        return $this->children()->count() === 0;
    }

    public static function generateNextChildNumber($parentId)
    {
        if (!$parentId) return null;

        // Ensure we use the AccountNumber, whether $parentId is an ID or already an AccountNumber
        $parent = self::where('ID', $parentId)->orWhere('AccountNumber', $parentId)->first();
        
        if (!$parent) return null;
        
        $parentNumber = (string) $parent->AccountNumber;

        // Search for the maximum child number using the parent's AccountNumber
        $maxChildNumber = self::withTrashed()->where('FatherNumber', $parentNumber)->max('AccountNumber');

        if ($maxChildNumber) {
            return (int)$maxChildNumber + 1;
        }

        // Logic for first child: Suffix '1' or '01'
        return strlen($parentNumber) == 1 ? (int)($parentNumber . '1') : (int)($parentNumber . '01');
    }

    /**
     * التحقق من إمكانية الحذف وإرجاع سبب المنع إن وُجد.
     */
    public function getDeletionPreventionReason(): ?string
    {
        // 1. هل لديه حسابات فرعية؟
        if ($this->children()->exists()) {
            return 'لا يمكن حذف حساب يمتلك حسابات متفرعة منه.';
        }

        // 2. هل تم استخدامه في قيود يومية (حركات مالية)؟
        if (DB::table('tblEntryDetails')->where('AccountID', $this->ID)->exists()) {
             return 'لا يمكن حذف حساب مسجل عليه حركات وقيود مالية سابقة.';
        }

        // 3. ارتباطه بجهة (عميل أو مورد)
        if ($this->IsCustomer || $this->IsSupplier) {
             return 'لا يمكن الحذف لأن الحساب مرتبط كعميل أو مورد بالمنشأة.';
        }

        // 4. هل يمتلك حدود ائتمان؟
        if ($this->limits()->exists()) {
            return 'لا يمكن حذف الحساب نظراً لارتباطه بحدود ائتمان.';
        }

        // 5. هل توجد سندات أو قيود منفردة معلقة؟
        if (DB::table('tblSimpleEntries')
                ->where('FromAccountID', $this->ID)
                ->orWhere('ToAccountID', $this->ID)->exists()) {
             return 'الحساب مرتبط بسندات وقيود منفردة سابقة أو معلقة.';
        }
        
        // 6. حسابات النظام الافتراضية الرئيسية (1: أصول، 2: خصوم، 3: إيرادات، 4: مصروفات)
        $systemAccounts = ['1', '2', '3', '4', '5'];
        if (in_array((string)$this->AccountNumber, $systemAccounts)) {
             return 'لا يمكن حذف الحسابات الافتراضية والأساسية للنظام.';
        }

        return null; // الحذف مسموح
    }
}
