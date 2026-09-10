<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->loanNotifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, int $notification)
    {
        $notification = $request->user()
            ->loanNotifications()
            ->findOrFail($notification);

        $notification->update([
            'read_at' => now(),
        ]);

        $url = $notification->url;
        if (! $url) {
            $url = route('home');
        }

        return redirect($url);
    }

    public function readAll(Request $request)
    {
        $request->user()
            ->unreadLoanNotifications()
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
