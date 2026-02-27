<?php

namespace App\Http\Controllers;

use App\Models\NotificationRecipient;
use App\Services\Notification\MarkAsReadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Events\NotificationUnreadCountUpdated;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = NotificationRecipient::with('notification.laporan')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($notifications);
    }

    public function show(NotificationRecipient $recipient)
    {
        Gate::authorize('notification.view', $recipient);

        return response()->json(
            $recipient->load('notification.laporan')
        );
    }

    public function markAsRead(
    NotificationRecipient $recipient,
    MarkAsReadService $service
) {
    Gate::authorize('notification.read', $recipient);

    $service->execute($recipient);

    $count = NotificationRecipient::where('user_id', auth()->id())
        ->where('is_read', false)
        ->count();

    broadcast(new NotificationUnreadCountUpdated(
        auth()->id(),
        $count
    ));

    return response()->json([
        'message' => 'Notification marked as read',
    ]);
}

    public function markAllAsRead()
    {
        DB::transaction(function () {
            NotificationRecipient::where('user_id', auth()->id())
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        });

        $count = 0;

        broadcast(new NotificationUnreadCountUpdated(
            auth()->id(),
            $count
        ));

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }

    public function destroy(NotificationRecipient $recipient)
    {
        Gate::authorize('notification.delete', $recipient);

        $recipient->delete();

        return response()->json([
            'message' => 'Notification deleted',
        ]);
    }

    public function unreadCount()
    {
        $count = NotificationRecipient::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }
}
