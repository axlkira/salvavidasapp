<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarningSign extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'risk_assessment_id',
        'description',
        'is_critical',
        'created_at',
        'updated_at',
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'is_critical' => 'boolean',
    ];

    /**
     * Obtener la evaluación de riesgo a la que pertenece esta señal.
     */
    public function riskAssessment()
    {
        return $this->belongsTo(RiskAssessment::class);
    }
}
