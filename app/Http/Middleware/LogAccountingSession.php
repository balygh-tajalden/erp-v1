<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AccountingSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LogAccountingSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sessionId = Session::get('accounting_session_id');
            
            // 1. If an ID exists in session, confirm it exists in DB
            if ($sessionId) {
                $sessionExists = AccountingSession::where('legacy_id', $sessionId)->exists();
                
                if (!$sessionExists) {
                    // Force Logout: The session record was deleted or is invalid
                    Auth::logout();
                    Session::invalidate();
                    Session::regenerateToken();
                    
                    return redirect()->route('filament.admin.auth.login');
                }

                // Refresh activity time (EnterTime) for the current session
                AccountingSession::where('legacy_id', $sessionId)
                    ->where('IsEnded', false)
                    ->update(['EnterTime' => now()]);

            } else {
                // 2. Create a new accounting session if one doesn't exist (first login)
                // Ensure no other "Open" accounting sessions exist for this user before starting a new one
                AccountingSession::where('user_id', $user->ID)
                    ->where('IsEnded', false)
                    ->update([
                        'IsEnded' => true, 
                        'EndTime' => now(),
                        'Notes' => 'Closed automatically by system'
                    ]);

                $userAgent = $request->userAgent();
                $systemInfo = AccountingSession::parseSystemInfo($userAgent);

                // Create a new accounting session record
                $accountingSession = AccountingSession::create([
                    'legacy_id' => AccountingSession::getNextLegacyID(),
                    'user_id' => $user->ID,
                    'BranchID' => $user->BranchID,
                    'StartTime' => now(),
                    'EnterTime' => now(),
                    'ip_address' => $request->ip(),
                    'OSVersion' => $systemInfo['os'],
                    'MachineName' => $systemInfo['machine'],
                    'OSUserName' => $user->UserName,
                    'IsEnded' => false,
                    'Notes' => "Dashboard Session: " . $user->UserName,
                ]);

                // Store the ID in the session for later access (AuditTrail, Models, etc.)
                Session::put('accounting_session_id', $accountingSession->legacy_id);
            }
        }

        return $next($request);
    }
}
