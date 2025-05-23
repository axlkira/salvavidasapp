<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\RiskAssessment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Crear una notificación de riesgo alto detectado
     *
     * @param RiskAssessment $assessment La evaluación de riesgo
     * @param User|null $user El usuario para notificar (null para todos los usuarios con permisos)
     * @return Notification La notificación creada
     */
    public function createHighRiskNotification(RiskAssessment $assessment, User $user = null)
    {
        try {
            // En esta versión de demo, no requerimos autenticación
            // Así que todas las notificaciones son para todos los profesionales
            $userId = $user ? $user->id : null;
            
            // Obtener datos del paciente
            $patientName = $assessment->conversation->patient->nombre ?? 'Paciente';
            $patientId = $assessment->conversation->patient->documento ?? 'ID no disponible';
            
            // Crear la notificación
            $notification = Notification::create([
                'type' => 'high_risk_detected',
                'user_id' => $userId,
                'title' => '¡Alerta! Riesgo de suicidio detectado',
                'message' => "Se ha detectado un nivel de riesgo {$assessment->risk_level} para {$patientName} (ID: {$patientId}). Es necesaria atención inmediata.",
                'data' => [
                    'risk_level' => $assessment->risk_level,
                    'risk_score' => $assessment->risk_score,
                    'conversation_id' => $assessment->conversation_id,
                    'patient_id' => $assessment->conversation->patient_id ?? null,
                    'patient_name' => $patientName,
                    'timestamp' => now()->toIso8601String()
                ],
                'risk_assessment_id' => $assessment->id
            ]);
            
            Log::info('Notificación de riesgo alto creada', [
                'notification_id' => $notification->id,
                'risk_assessment_id' => $assessment->id,
                'risk_level' => $assessment->risk_level
            ]);
            
            return $notification;
        } catch (\Exception $e) {
            Log::error('Error al crear notificación de riesgo alto: ' . $e->getMessage(), [
                'assessment_id' => $assessment->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Crear una notificación para cambio de estado en evaluación de riesgo
     *
     * @param RiskAssessment $assessment La evaluación de riesgo
     * @param string $oldStatus El estado anterior
     * @param string $newStatus El nuevo estado
     * @return Notification La notificación creada
     */
    public function createStatusChangeNotification(RiskAssessment $assessment, $oldStatus, $newStatus)
    {
        try {
            // Obtener datos del paciente
            $patientName = $assessment->conversation->patient->nombre ?? 'Paciente';
            
            // Crear la notificación
            $notification = Notification::create([
                'type' => 'status_changed',
                'user_id' => null, // En la versión demo, no filtramos por usuario
                'title' => 'Estado de evaluación de riesgo actualizado',
                'message' => "El estado de la evaluación de riesgo para {$patientName} ha cambiado de '{$oldStatus}' a '{$newStatus}'.",
                'data' => [
                    'risk_level' => $assessment->risk_level,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'conversation_id' => $assessment->conversation_id,
                    'timestamp' => now()->toIso8601String()
                ],
                'risk_assessment_id' => $assessment->id
            ]);
            
            Log::info('Notificación de cambio de estado creada', [
                'notification_id' => $notification->id,
                'risk_assessment_id' => $assessment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
            
            return $notification;
        } catch (\Exception $e) {
            Log::error('Error al crear notificación de cambio de estado: ' . $e->getMessage(), [
                'assessment_id' => $assessment->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Obtener todas las notificaciones no leídas 
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnreadNotifications()
    {
        // En la versión demo, mostramos todas las notificaciones sin filtrar por usuario
        return Notification::whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Obtener todas las notificaciones
     *
     * @param int $limit Límite de notificaciones a retornar
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllNotifications($limit = 50)
    {
        // En la versión demo, mostramos todas las notificaciones sin filtrar por usuario
        return Notification::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Marcar una notificación como leída
     *
     * @param int $notificationId ID de la notificación
     * @return Notification|null
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        
        if ($notification) {
            return $notification->markAsRead();
        }
        
        return null;
    }
    
    /**
     * Marcar todas las notificaciones como leídas
     *
     * @return int Número de notificaciones marcadas como leídas
     */
    public function markAllAsRead()
    {
        // En la versión demo, marcamos todas las notificaciones como leídas sin filtrar por usuario
        $count = Notification::whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return $count;
    }
}
