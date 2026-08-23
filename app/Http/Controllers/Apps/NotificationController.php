<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function page(Request $request)
    {
        $query = AppNotification::forUser($request->user()->id)->latest();

        if ($request->filled('search')) {
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$request->search}%")
                ->orWhere('message', 'like', "%{$request->search}%"));
        }

        return Inertia::render('Apps/Notifications/Index', [
            'notifications' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }
    public function index(Request $request): JsonResponse
    {
        $notifications = AppNotification::forUser($request->user()->id)
            ->latest()
            ->limit(20)
            ->get();

        $unreadCount = AppNotification::forUser($request->user()->id)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $success = NotificationService::markAsRead($id, $request->user()->id);

        return response()->json(['success' => $success]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $count = NotificationService::markAllRead($request->user()->id);

        return response()->json(['success' => true, 'count' => $count]);
    }

    public function refresh(): JsonResponse
    {
        NotificationService::generateAll();

        return response()->json(['success' => true]);
    }
}
