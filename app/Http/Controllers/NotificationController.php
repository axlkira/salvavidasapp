<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Servicio de notificaciones
     * 
     * @var NotificationService
     */
    protected $notificationService;
    
    /**
     * Constructor del controlador
     * 
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        // No aplicamos middleware de autenticación para el acceso a notificaciones
        // ya que la aplicación no tiene sistema de login configurado
    }
    
    /**
     * Mostrar todas las notificaciones
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $notifications = $this->notificationService->getAllNotifications();
        
        return view('notifications.index', [
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Obtener notificaciones no leídas (para AJAX)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnread()
    {
        $notifications = $this->notificationService->getUnreadNotifications();
        
        return response()->json([
            'success' => true,
            'count' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Marcar una notificación como leída
     * 
     * @param int $id ID de la notificación
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $notification = $this->notificationService->markAsRead($id);
        
        if ($notification) {
            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Notificación no encontrada'
        ], 404);
    }
    
    /**
     * Marcar todas las notificaciones como leídas
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        $count = $this->notificationService->markAllAsRead();
        
        return response()->json([
            'success' => true,
            'message' => "{$count} notificaciones marcadas como leídas",
            'count' => $count
        ]);
    }
    
    /**
     * Ver detalles de una notificación
     * 
     * @param int $id ID de la notificación
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $notification = Notification::find($id);
        
        if (!$notification) {
            return redirect()->route('notifications.index')
                ->with('error', 'Notificación no encontrada');
        }
        
        // Marcar como leída si aún no lo está
        if (!$notification->isRead()) {
            $notification->markAsRead();
        }
        
        // Si es una notificación de riesgo, redirigir a la evaluación
        if ($notification->type === 'high_risk_detected' && $notification->risk_assessment_id) {
            return redirect()->route('risk-assessment.show', $notification->risk_assessment_id)
                ->with('info', 'Has sido redirigido desde una notificación de riesgo alto');
        }
        
        return view('notifications.show', [
            'notification' => $notification
        ]);
    }
}
