<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    #[OA\Get(path: "/notifications", summary: "Get My Notifications", security: [["bearerAuth" => []]], tags: ["Notification"])]
    #[OA\Parameter(name: "is_read", in: "query", required: false, schema: new OA\Schema(type: "boolean"))]
    #[OA\Parameter(name: "type", in: "query", required: false, schema: new OA\Schema(type: "string", enum: ["application", "booking", "invitation", "review", "event", "talent"]))]
    #[OA\Parameter(name: "action", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    public function index(Request $request)
    {
        // Ambil semua notifikasi milik user yang sedang login
        $query = Notification::where('user_id', Auth::id());

        // Filter berdasarkan status sudah dibaca atau belum
        if ($request->has('is_read')) {
            $isRead = filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_read', $isRead);
        }

        // Filter berdasarkan tipe notifikasi
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter berdasarkan action notifikasi
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Ambil notifikasi terbaru dengan paginasi, plus hitung yang belum dibaca
        $notifications = $query->latest()->paginate(15);
        $unreadCount = Notification::where('user_id', Auth::id())->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'notifications' => $notifications->items(),
                'unread_count' => $unreadCount,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'last_page' => $notifications->lastPage()
                ]
            ]
        ], 200);
    }

    #[OA\Put(path: "/notifications/{id}/read", summary: "Mark Notification as Read", security: [["bearerAuth" => []]], tags: ["Notification"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 404, description: "Not Found")]
    public function markAsRead($id)
    {
        // Cari notifikasi milik user yang sedang login berdasarkan ID
        $notification = Notification::where('user_id', Auth::id())->find($id);

        // Pastikan notifikasi tersebut ada
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        // Tandai notifikasi sebagai sudah dibaca
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca'
        ], 200);
    }

    #[OA\Put(path: "/notifications/read-all", summary: "Mark All Notifications as Read", security: [["bearerAuth" => []]], tags: ["Notification"])]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    public function markAllAsRead()
    {
        // Tandai semua notifikasi yang belum dibaca sebagai sudah dibaca sekaligus
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sudah dibaca'
        ], 200);
    }
}
