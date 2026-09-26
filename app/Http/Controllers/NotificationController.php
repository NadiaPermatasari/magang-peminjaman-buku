<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List the signed in user's notifications.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->when($request->query('filter') === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($request->filled('search'), fn ($q) => $q->where('data', 'like', '%'.$request->query('search').'%'))
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark one notification as read and follow its link (if any).
     */
    public function read(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return $url ? redirect($url) : back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return back()->with('success', 'Notification deleted.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $request->user()->notifications()->delete();

        return back()->with('success', 'All notifications cleared.');
    }
}
