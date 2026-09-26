<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Activity log (admin only) with optional action / user filters.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $action = $request->query('action');
        $userId = $request->query('user');

        $activities = ActivityLog::with('user')
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->latest()
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('activity.index', [
            'activities' => $activities,
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
