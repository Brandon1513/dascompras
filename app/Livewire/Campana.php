<?php

namespace App\Livewire;

use App\Models\NotificacionInterna;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class Campana extends Component
{
    public int $noLeidas = 0;

    public function mount(): void
    {
        $this->noLeidas = NotificacionInterna::where('user_id', Auth::id())
            ->where('leida', false)
            ->count();
    }

    public function marcarLeida(int $id): void
    {
        NotificacionInterna::where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['leida' => true, 'leida_en' => now()]);

        $this->noLeidas = NotificacionInterna::where('user_id', Auth::id())
            ->where('leida', false)->count();
    }

    public function marcarTodasLeidas(): void
    {
        NotificacionInterna::where('user_id', Auth::id())
            ->where('leida', false)
            ->update(['leida' => true, 'leida_en' => now()]);
        $this->noLeidas = 0;
    }

    public function eliminar(int $id): void
    {
        NotificacionInterna::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();
        $this->noLeidas = NotificacionInterna::where('user_id', Auth::id())
            ->where('leida', false)->count();
    }

    public function render()
    {
        $notificaciones = NotificacionInterna::where('user_id', Auth::id())
            ->latest()->limit(20)->get();
        $this->noLeidas = $notificaciones->where('leida', false)->count();
        return view('livewire.campana', compact('notificaciones'));
    }
}