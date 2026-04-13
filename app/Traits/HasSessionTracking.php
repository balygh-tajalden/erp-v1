<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

trait HasSessionTracking
{
    /**
     * Cache for the active session ID during the request lifecycle.
     */
    protected static ?int $activeSessionId = null;

    /**
     * Boot the trait to automatically set SessionID.
     */
    protected static function bootHasSessionTracking(): void
    {
        static::creating(function ($model) {
            $sessionId = static::getActiveAccountingSessionId();

            if ($sessionId && $model->hasSessionColumn()) {
                $model->SessionID = $sessionId;
            }
        });
    }

    /**
     * Get the active accounting session ID.
     */
    public static function getActiveAccountingSessionId(): ?int
    {
        if (static::$activeSessionId !== null) {
            return static::$activeSessionId;
        }

        // 1. Try Laravel Session
        $sessionId = Session::get('accounting_session_id');

        // 2. Fallback: Search Database for active session
        if (! $sessionId && Auth::check()) {
            $sessionId = \App\Models\AccountingSession::query()
                ->where('user_id', Auth::id())
                ->active()
                ->value('legacy_id');

            if ($sessionId) {
                Session::put('accounting_session_id', $sessionId);
            }
        }

        return static::$activeSessionId = $sessionId;
    }

    /**
     * Check if the model has a SessionID column.
     */
    protected function hasSessionColumn(): bool
    {
        return array_key_exists('SessionID', $this->getAttributes()) || 
               $this->isFillable('SessionID') || 
               Schema::hasColumn($this->getTable(), 'SessionID');
    }

    /**
     * Get the session associated with this record.
     */
    public function session()
    {
        return $this->belongsTo(\App\Models\AccountingSession::class, 'SessionID', 'legacy_id');
    }
}
