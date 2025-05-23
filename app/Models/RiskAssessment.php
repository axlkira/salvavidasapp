<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'conversation_id',
        'individual_id',
        'patient_document',
        'professional_id',
        'risk_score',
        'risk_level',
        'provider',
        'model',
        'status', // 'pending', 'reviewed', 'archived'
        'reviewed_at',
        'reviewed_by',
        'created_at',
        'updated_at',
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'risk_score' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Obtener los factores de riesgo asociados con esta evaluación.
     */
    public function riskFactors()
    {
        return $this->hasMany(RiskFactor::class);
    }

    /**
     * Obtener las señales de alerta asociadas con esta evaluación.
     */
    public function warningSigns()
    {
        return $this->hasMany(WarningSign::class);
    }

    /**
     * Obtener la conversación asociada con esta evaluación.
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Obtener la historia clínica asociada con esta evaluación.
     */
    public function individual()
    {
        return $this->belongsTo(Individual::class, 'individual_id');
    }

    /**
     * Obtener el paciente asociado con esta evaluación.
     */
    public function patient()
    {
        return $this->belongsTo(PrincipalIntegrante::class, 'patient_document', 'documento');
    }

    /**
     * Obtener el profesional que realizó esta evaluación.
     */
    public function professional()
    {
        return $this->belongsTo(User::class, 'professional_id');
    }

    /**
     * Obtener el profesional que revisó esta evaluación.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Obtener la guía de intervención asociada con esta evaluación, si existe.
     */
    public function interventionGuide()
    {
        return $this->hasOne(InterventionGuide::class);
    }
}
