<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

/**
 * Security overview for Super Admin (spec §25). Never displays secrets —
 * everything here is aggregated/derived from the append-only audit log.
 */
class SecurityDashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('security-dashboard.view'), 403);

        $today = now()->startOfDay();

        $stats = [
            'loginSuccessToday' => ActivityLog::where('action', 'LOGIN_SUCCESS')->where('created_at', '>=', $today)->count(),
            'loginFailedToday' => ActivityLog::where('action', 'LOGIN_FAILED')->where('created_at', '>=', $today)->count(),
            'twoFactorFailedToday' => ActivityLog::where('action', '2FA_FAILED')->where('created_at', '>=', $today)->count(),
            'activeSessions' => config('session.driver') === 'database'
                ? DB::table('sessions')->whereNotNull('user_id')->where('last_activity', '>=', now()->subMinutes(15)->timestamp)->distinct('user_id')->count('user_id')
                : null,
        ];

        $recentFailedLogins = ActivityLog::where('action', 'LOGIN_FAILED')
            ->latest()
            ->take(10)
            ->get();

        $recentSecurityEvents = ActivityLog::whereIn('action', [
            'LOGIN_FAILED', '2FA_FAILED', '2FA_DISABLED', 'RECOVERY_CODES_REGENERATED',
            'ROLE_ASSIGNED', 'ROLE_REMOVED', 'PERMISSION_CHANGED', 'USER_DISABLED', 'SETTING_CHANGED',
        ])
            ->with('user')
            ->latest()
            ->take(15)
            ->get();

        return view('security.index', [
            'stats' => $stats,
            'recentFailedLogins' => $recentFailedLogins,
            'recentSecurityEvents' => $recentSecurityEvents,
        ]);
    }
}
