<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\RiskAssessment;

class Notification extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'user_id',
        'title',
        'message',
        'data',
        'read_at',
        'risk_assessment_id'
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array'
    ];

    /**
     * Obtener el usuario asociado a esta notificación.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener la evaluación de riesgo asociada a esta notificación, si existe.
     */
    public function riskAssessment()
    {
        return $this->belongsTo(RiskAssessment::class);
    }

    /**
     * Marcar la notificación como leída.
     */
    public function markAsRead()
    {
        $this->read_at = now();
        $this->save();
        
        return $this;
    }

    /**
     * Verificar si la notificación ha sido leída.
     */
    public function isRead()
    {
        return $this->read_at !== null;
    }

    /**
     * Obtener todas las notificaciones no leídas.
     */
    public static function unread()
    {
        return static::whereNull('read_at')->get();
    }

    /**
     * Obtener todas las notificaciones de riesgo alto no leídas.
     */
    public static function unreadHighRisk()
    {
        return static::whereNull('read_at')
            ->where('type', 'high_risk_detected')
            ->get();
    }
}
