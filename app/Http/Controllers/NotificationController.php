<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Listado de notificaciones.
     * Empleados y gerentes: solo las propias.
     * Admin y RH: todas las notificaciones del sistema.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $viewAll = $user->hasAnyRole(['ADMIN', 'HR_MANAGER']);

        if ($viewAll) {
            $notifications = DatabaseNotification::with('notifiable')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(15);
        }

        return view('notifications.index', [
            'notifications' => $notifications,
            'viewAll' => $viewAll,
        ]);
    }

    /**
     * Marcar una notificación como leída y redirigir a la URL asociada.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        $url = $notification->data['url'] ?? route('dashboard');

        return redirect($url);
    }

    /**
     * Marcar todas como leídas.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')->with('success', 'Todas las notificaciones se han marcado como leídas.');
    }
}
