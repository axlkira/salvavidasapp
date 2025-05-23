<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'professional_id',
        'patient_document',
        'title',
        'provider',
        'model',
        'created_at',
        'updated_at',
    ];

    /**
     * Obtener todos los mensajes de esta conversación.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Obtener el profesional que inició esta conversación.
     */
    public function professional()
    {
        // Asumiendo que existe un modelo Professional
        return $this->belongsTo(User::class, 'professional_id');
    }

    /**
     * Obtener el paciente relacionado con esta conversación.
     */
    public function patient()
    {
        return $this->belongsTo(PrincipalIntegrante::class, 'patient_document', 'documento');
    }

    /**
     * Obtener la evaluación de riesgo asociada con esta conversación, si existe.
     */
    public function riskAssessment()
    {
        return $this->hasOne(RiskAssessment::class);
    }
    
    /**
     * Obtener las historias clínicas asociadas al paciente de esta conversación.
     */
    public function historiasClincias()
    {
        return $this->hasManyThrough(
            Individual::class,
            PrincipalIntegrante::class,
            'documento', // Clave foránea en PrincipalIntegrante
            'Documento', // Clave foránea en Individual
            'patient_document', // Clave local en Conversation
            'documento' // Clave local en PrincipalIntegrante
        );
    }
}
