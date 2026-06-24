<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->orderByRaw('read_at IS NULL DESC')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => Notification::query()
                ->where('user_id', $request->user()->id)
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
