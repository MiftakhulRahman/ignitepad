<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(10);
        return view('notifications.index', compact('notifications'));
    }

    public function read(DatabaseNotification $notification)
    {
        $notification->markAsRead();

        if (isset($notification->data['project_id'])) {
            $project = \App\Models\Project::find($notification->data['project_id']);
            if ($project) {
                return redirect()->route('projects.show', $project);
            }
        }

        return redirect()->back();
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi telah ditandai sebagai sudah dibaca.');
    }

    public function destroyAll()
    {
        Auth::user()->notifications()->delete();
        return back()->with('success', 'Semua notifikasi telah dihapus.');
    }

    public function destroy(DatabaseNotification $notification)
    {
        $notification->delete();
        return back()->with('success', 'Notifikasi telah dihapus.');
    }
}
