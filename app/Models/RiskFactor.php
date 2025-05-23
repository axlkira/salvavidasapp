<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskFactor extends Model
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
        'created_at',
        'updated_at',
    ];

    /**
     * Obtener la evaluación de riesgo a la que pertenece este factor.
     */
    public function riskAssessment()
    {
        return $this->belongsTo(RiskAssessment::class);
    }
}
