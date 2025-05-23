<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Individual extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'familiam_buenvivir.t_individual';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indica si el modelo debe tener timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'idcruce',
        'FechaInicio',
        'FechaCierre',
        'Profesional',
        'PuntoAtencion',
        'tipo_profesional',
        'TipoIntervencion',
        'tipoacompanamiento',
        'AntecedentesClinicosFisicosMentales',
        'PersonalesPsicosociales',
        'Familiares',
        'ProblematicaActual',
        'NivelConcienciaProblematica',
        'ActitudFamiliarespersonassignificativas',
        'ImprecionDiagnostica',
        'Observaciones',
        'EnfoqueOrientadorProceso',
        'OtroEnfoque',
        'TecnicasUsadas',
        'OtraTecnica',
        'LogroObjetivos',
        'seremite',
        'seremitea',
        'tiposerviciohc',
        'inteproceso',
        'MotivoInterrupcion',
        'MotivoInterrupcionOtro',
        'InterrupcionLogrossesion',
        'desercion',
        'numsessiondeserto',
        'intervencionFinal',
        'Recomendaciones',
        'Documento',
        'FechaActual',
        'Digitador',
        'Docdigitador',
        'idestado',
        'motivoconsulta',
        'UPSE',
        'PARCEROS'
    ];

    /**
     * Obtener el paciente asociado con esta historia clínica.
     */
    public function paciente()
    {
        return $this->belongsTo(PrincipalIntegrante::class, 'Documento', 'documento');
    }

    /**
     * Obtener el nombre completo del paciente.
     */
    public function getNombrePacienteAttribute()
    {
        if ($this->paciente) {
            return trim(implode(' ', [
                $this->paciente->nombre1 ?? '',
                $this->paciente->nombre2 ?? '',
                $this->paciente->apellido1 ?? '',
                $this->paciente->apellido2 ?? ''
            ]));
        }
        
        return 'Paciente sin datos personales';
    }
    
    /**
     * Método para extraer factores de riesgo potenciales del texto clínico
     */
    public function extractRiskFactors()
    {
        $factors = [];
        $texts = [
            $this->ProblematicaActual,
            $this->ImprecionDiagnostica,
            $this->Observaciones,
            $this->PersonalesPsicosociales,
            $this->AntecedentesClinicosFisicosMentales
        ];
        
        // Lista de términos para buscar (palabras clave relacionadas con riesgo suicida)
        $keyTerms = [
            'suicid', 'autolesion', 'desesperanza', 'depresion', 'depresión', 'crisis', 
            'ideacion', 'muerte', 'morir', 'acabar', 'angustia', 'no quiero vivir', 
            'sin salida', 'abandonar', 'no puedo más', 'desesperación', 'trauma', 
            'abuso', 'violencia', 'maltrato', 'desesperado', 'no vale la pena', 'dolor'
        ];
        
        foreach ($texts as $text) {
            if (empty($text)) continue;
            
            foreach ($keyTerms as $term) {
                if (stripos($text, $term) !== false) {
                    $factors[] = $text;
                    break; // Si encontramos un término, añadimos el texto completo y pasamos al siguiente
                }
            }
        }
        
        return $factors;
    }
    
    /**
     * Obtener todos los datos relevantes del paciente para el análisis de riesgo
     */
    public function getDataForAnalysis()
    {
        return [
            'id' => $this->id,
            'Documento' => $this->Documento,
            'Nombre_Usuario' => $this->getNombrePacienteAttribute(),
            'FechaInicio' => $this->FechaInicio,
            'FechaCierre' => $this->FechaCierre,
            'TipoIntervencion' => $this->TipoIntervencion,
            'AntecedentesClinicosFisicosMentales' => $this->AntecedentesClinicosFisicosMentales,
            'PersonalesPsicosociales' => $this->PersonalesPsicosociales,
            'Familiares' => $this->Familiares,
            'ProblematicaActual' => $this->ProblematicaActual,
            'NivelConcienciaProblematica' => $this->NivelConcienciaProblematica,
            'ActitudFamiliarespersonassignificativas' => $this->ActitudFamiliarespersonassignificativas,
            'ImprecionDiagnostica' => $this->ImprecionDiagnostica,
            'Observaciones' => $this->Observaciones,
            'EnfoqueOrientadorProceso' => $this->EnfoqueOrientadorProceso,
            'TecnicasUsadas' => $this->TecnicasUsadas,
            'motivoconsulta' => $this->motivoconsulta,
        ];
    }
}
