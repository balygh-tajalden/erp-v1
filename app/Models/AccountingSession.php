<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingSession extends Model
{
    protected $table = 'tblAccountingSessions';
    protected $primaryKey = 'legacy_id'; // Original accounting ID
    public $timestamps = false; // Using custom StartTime/EndTime

    protected $fillable = [
        'user_id',
        'ip_address',
        'legacy_id',
        'RowVersion',
        'PrevRowVersion',
        'StartTime',
        'EndTime',
        'PCID',
        'OSVersion',
        'MachineName',
        'OSUserName',
        'Notes',
        'BranchID',
        'EnterTime',
        'IsEnded',
        'ServiceAddress',
        'SessionID',
        'ISHasToken',
        'ISHasValidationCode',
    ];

    protected $casts = [
        'StartTime' => 'datetime',
        'EndTime' => 'datetime',
        'EnterTime' => 'datetime',
        'IsEnded' => 'boolean',
        'ISHasToken' => 'boolean',
        'ISHasValidationCode' => 'boolean',
        'user_id' => 'integer',
        'BranchID' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function history()
    {
        return $this->hasMany(History::class, 'SessionID', 'legacy_id');
    }

    /**
     * تجلب الرقم التعريفي التالي للجلسات (legacy_id)
     */
    public static function getNextLegacyID(): int
    {
        return (int) (static::max('legacy_id') ?? 1000) + 1;
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('IsEnded', false);
    }

    /**
     * Close the accounting session
     */
    public function close(): bool
    {
        return $this->update([
            'IsEnded' => true,
            'EndTime' => now(),
        ]);
    }

    /**
     * Parse system and machine info from User Agent (Simple version)
     */
    public static function parseSystemInfo($userAgent): array
    {
        $os = "Unknown OS";
        $machine = "Unknown Machine";

        if (preg_match('/Windows NT 10.0/i', $userAgent)) $os = "Windows 10/11";
        elseif (preg_match('/Windows NT 6.3/i', $userAgent)) $os = "Windows 8.1";
        elseif (preg_match('/Windows NT 6.2/i', $userAgent)) $os = "Windows 8";
        elseif (preg_match('/Windows NT 6.1/i', $userAgent)) $os = "Windows 7";
        elseif (preg_match('/Macintosh|Mac OS X/i', $userAgent)) $os = "Mac OS";
        elseif (preg_match('/Linux/i', $userAgent)) $os = "Linux";

        // We can't easily get the real Machine Name from HTTP, so we use browser info
        if (preg_match('/Chrome/i', $userAgent)) $machine = "Chrome Browser";
        elseif (preg_match('/Firefox/i', $userAgent)) $machine = "Firefox Browser";
        elseif (preg_match('/Safari/i', $userAgent)) $machine = "Safari Browser";

        return [
            'os' => $os,
            'machine' => $machine
        ];
    }
}
