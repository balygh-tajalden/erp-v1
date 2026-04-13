<?php

namespace App\Services\System;

use App\Services\BaseService;
use App\Models\SystemEvent;
use Illuminate\Support\Facades\Auth;

class LoggingService extends BaseService
{
    /**
     * Replaces sp_LogSystemEvent
     */
    public function log($type, $description, $userId = null)
    {
        return SystemEvent::create([
            'EventType' => $type,
            'EventDescription' => $description,
            'UserID' => $userId ?? Auth::id(),
            'EventDate' => now(),
            'BranchID' => session('branch_id', 1),
            'SessionID' => session('session_id', 1),
        ]);
    }
}
