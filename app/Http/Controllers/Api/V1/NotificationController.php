<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->paginate(20);
        return response()->json($notifications);
    }

    public function unread(Request $request)
    {
        $user = $request->user();
        $notifications = $user->unreadNotifications()->latest()->paginate(20);
        return response()->json($notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }
}
