<?php

namespace App\Http\Controllers;

use App\Models\NotificacionInterna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionesController extends Controller
{
    public function index()
    {
        $notificaciones = NotificacionInterna::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        $noLeidas = NotificacionInterna::where('user_id', Auth::id())
            ->where('leida', false)
            ->count();

        return view('notificaciones.index', compact('notificaciones', 'noLeidas'));
    }

    public function marcarLeida(NotificacionInterna $notificacion)
    {
        abort_unless($notificacion->user_id === Auth::id(), 403);

        $notificacion->update(['leida' => true, 'leida_en' => now()]);

        if (request()->has('redirect') && $notificacion->url) {
            return redirect($notificacion->url);
        }

        return back()->with('status', 'Notificación marcada como leída.');
    }

    public function marcarTodasLeidas()
    {
        NotificacionInterna::where('user_id', Auth::id())
            ->where('leida', false)
            ->update(['leida' => true, 'leida_en' => now()]);

        return back()->with('status', 'Todas las notificaciones marcadas como leídas.');
    }

    public function eliminar(NotificacionInterna $notificacion)
    {
        abort_unless($notificacion->user_id === Auth::id(), 403);
        $notificacion->delete();
        return back()->with('status', 'Notificación eliminada.');
    }

    public function eliminarTodas()
    {
        NotificacionInterna::where('user_id', Auth::id())->delete();
        return back()->with('status', 'Todas las notificaciones eliminadas.');
    }
}