<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Obtener notificaciones del usuario autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min(50, (int) $request->get('limit', 20));

        $notifications = UserNotification::forUser($user->id)
            ->recent()
            ->limit($limit)
            ->get();

        $unreadCount = NotificationService::unreadCount($user);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Obtener solo notificaciones no leídas
     */
    public function unread(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = UserNotification::forUser($user->id)
            ->unread()
            ->recent()
            ->limit(20)
            ->get();

        $unreadCount = NotificationService::unreadCount($user);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $notification = UserNotification::forUser($user->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => NotificationService::unreadCount($user),
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        UserNotification::forUser($user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Contador de notificaciones no leídas (para el badge)
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'unread_count' => NotificationService::unreadCount($user),
        ]);
    }
}
