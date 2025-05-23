<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterventionGuide extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'risk_assessment_id',
        'steps',
        'techniques',
        'resources',
        'follow_up_plan',
        'communication_strategies',
        'safety_plan',
        'created_at',
        'updated_at',
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'steps' => 'array',
        'techniques' => 'array',
        'resources' => 'array',
        'follow_up_plan' => 'array',
        'communication_strategies' => 'array',
        'safety_plan' => 'array',
    ];

    /**
     * Obtener la evaluación de riesgo a la que pertenece esta guía.
     */
    public function riskAssessment()
    {
        return $this->belongsTo(RiskAssessment::class);
    }
}
