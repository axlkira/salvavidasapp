<?php

namespace App\View\Components;

use App\Models\Notification;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class NotificationCounter extends Component
{
    /**
     * Número de notificaciones no leídas.
     *
     * @var int
     */
    public $unreadCount;

    /**
     * Crear una nueva instancia del componente.
     *
     * @return void
     */
    public function __construct()
    {
        $this->unreadCount = $this->getUnreadNotificationsCount();
    }

    /**
     * Obtener el número de notificaciones no leídas.
     *
     * @return int
     */
    protected function getUnreadNotificationsCount()
    {
        if (!Auth::check()) {
            return 0;
        }

        return Notification::whereNull('read_at')
            ->where(function($query) {
                $query->where('user_id', Auth::id())
                      ->orWhereNull('user_id');
            })
            ->count();
    }

    /**
     * Obtener la vista que representa el componente.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.notification-counter');
    }
}
