<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function read(DatabaseNotification $notification)
    {
        // Pastikan notifikasi ini milik user yang sedang login
        if (Auth::id() !== $notification->notifiable_id) {
            abort(403);
        }

        $notification->markAsRead();

        // Redirect ke halaman review proyek
        return redirect()->route('lecturer.reviews.show', $notification->data['project_slug']);
    }
}
