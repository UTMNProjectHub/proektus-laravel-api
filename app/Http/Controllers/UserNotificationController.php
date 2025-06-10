<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json($user->notifications);
    }
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy(Request $request, $notificationId)
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->findOrFail($notificationId);
            $notification->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Удаление уведомления не успешно.'], 500);
        }

        return response()->json(['message' => 'Уведомление успешно удалено.']);
    }
}
