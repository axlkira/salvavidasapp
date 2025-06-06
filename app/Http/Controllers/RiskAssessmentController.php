<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskAssessment;
use App\Models\PrincipalIntegrante;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RiskAssessmentController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Autenticación - descomentar en producción
        // $this->middleware('auth');
    }
    
    /**
     * Mostrar listado de evaluaciones de riesgo
     */
    public function index(Request $request)
    {
        // Verificar si se solicitó recalcular todas las evaluaciones
        if ($request->has('recalculate') || $request->has('recalculate_all')) {
            // Recalcular todas las evaluaciones usando el servicio actualizado
            $this->recalculateAllRiskAssessments();
            return redirect()->route('risk-assessment.index')
                ->with('success', 'Todas las evaluaciones de riesgo han sido recalculadas con el nuevo algoritmo');
        }
        
        // Sincronizar todas las evaluaciones de alto riesgo antes de mostrar
        $this->syncAllHighRiskAssessments();
        
        // Forzar sincronización con la base de datos
        \Illuminate\Support\Facades\DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        // Limpiar caché
        \Illuminate\Support\Facades\Cache::forget('risk_alert_count');
        
        // Añadir botón para recalcular todos los riesgos
        $showRecalculateButton = true;
        
        $query = RiskAssessment::query()->with(['conversation', 'riskFactors'])->withoutGlobalScopes();
        
        // Filtrar por nivel de riesgo
        if ($request->has('risk_level') && $request->risk_level !== 'all') {
            $query->where('risk_level', $request->risk_level);
        }
        
        // Filtrar por estado
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filtrar por fecha
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Ordenar
        $query->orderBy('created_at', 'desc');
        
        $assessments = $query->paginate(10);
        
        // Preparar los datos para la vista
        $assessmentsData = $assessments->map(function($assessment) {
            $patientName = 'Paciente anónimo';
            $patientDocument = $assessment->patient_document;
            
            if ($patientDocument) {
                $patient = PrincipalIntegrante::where('documento', $patientDocument)->first();
                if ($patient) {
                    $patientName = trim($patient->nombre1 . ' ' . $patient->nombre2 . ' ' . 
                                  $patient->apellido1 . ' ' . $patient->apellido2);
                }
            }
            
            // Obtener los factores de riesgo principales
            $riskFactors = $assessment->riskFactors()->take(3)->get()->pluck('description')->toArray();
            
            return [
                'id' => $assessment->id,
                'patient_name' => $patientName,
                'patient_document' => $patientDocument,
                'risk_level' => $assessment->risk_level,
                'risk_score' => $assessment->risk_score,
                'status' => $assessment->status,
                'created_at' => $assessment->created_at,
                'risk_factors' => $riskFactors,
                'conversation_id' => $assessment->conversation_id
            ];
        });
        
        return view('risk-assessment.index', [
            'assessments' => $assessments,
            'assessmentsData' => $assessmentsData,
            'filters' => [
                'risk_level' => $request->risk_level ?? 'all',
                'status' => $request->status ?? 'all',
                'date_from' => $request->date_from ?? '',
                'date_to' => $request->date_to ?? ''
            ],
            'show_recalculate_button' => $showRecalculateButton ?? false,
            'can_recalculate' => true
        ]);
    }
    
    /**
     * Mostrar detalle de una evaluación de riesgo
     */
    public function show($id)
    {
        // Forzar consulta reciente de la base de datos
        \Illuminate\Support\Facades\DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        // Refrescar desde la base de datos sin caché
        $assessment = RiskAssessment::withoutGlobalScopes()
                    ->with(['conversation', 'riskFactors', 'warningSigns'])
                    ->where('id', $id)
                    ->firstOrFail();
        
        // Obtener la versión más reciente directamente de la base de datos
        \Illuminate\Support\Facades\DB::connection()->getpdo()->exec('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
        $freshData = \Illuminate\Support\Facades\DB::table('risk_assessments')->where('id', $id)->first();
        
        if ($freshData) {
            // Actualizar con los datos más recientes
            $assessment->risk_level = $freshData->risk_level;
            $assessment->risk_score = $freshData->risk_score;
            $assessment->status = $freshData->status;
        }
        
        // No forzamos puntuación de riesgo, confiamos en la evaluación original del servicio
        
        // Detectar mensajes con contenido suicida
        $suicidalContent = false;
        $conversation = $assessment->conversation;
        if ($conversation) {
            $recentMessages = $conversation->messages()
                            ->where('role', '!=', 'system')
                            ->orderBy('created_at', 'desc')
                            ->take(10)
                            ->get();
                            
            $riskKeywords = [
                'suicid', 'matarme', 'quitarme la vida', 'no quiero vivir', 
                'me quiero morir', 'terminar con todo', 'acabar con mi vida',
                'no vale la pena vivir', 'mejor estar muerto'
            ];
            
            foreach ($recentMessages as $message) {
                $content = strtolower($message->content);
                foreach ($riskKeywords as $keyword) {
                    if (strpos($content, strtolower($keyword)) !== false) {
                        $suicidalContent = true;
                        break 2; // Salir de ambos loops
                    }
                }
            }
            
            // Si hay contenido suicida explícito, marcar para revisión pero no forzar nivel alto
            if ($suicidalContent && !in_array(strtolower($assessment->risk_level), ['alto', 'crítico', 'critico'])) {
                // Solo actualizamos el estado para que sea revisado, pero respetamos la puntuación original
                $assessment->status = 'pending';
                $assessment->save();
            }
        }
        
        // Obtener nombre del paciente
        $patientName = 'Paciente anónimo';
        $patientDocument = $assessment->patient_document;
        
        if ($patientDocument) {
            $patient = PrincipalIntegrante::where('documento', $patientDocument)->first();
            if ($patient) {
                $patientName = trim($patient->nombre1 . ' ' . $patient->nombre2 . ' ' . 
                              $patient->apellido1 . ' ' . $patient->apellido2);
            }
        }
        
        // Obtener nombre del profesional
        $professionalName = 'Sistema';
        if ($assessment->professional_id) {
            $professional = User::find($assessment->professional_id);
            if ($professional) {
                $professionalName = $professional->name;
            }
        }
        
        // Obtener nombre del revisor si existe
        $reviewerName = '';
        if ($assessment->reviewed_by) {
            $reviewer = User::find($assessment->reviewed_by);
            if ($reviewer) {
                $reviewerName = $reviewer->name;
            }
        }
        
        // Obtener mensajes relevantes de la conversación
        $relevantMessages = collect([]);
        
        if ($conversation) {
            // Buscar mensajes con palabras clave relacionadas con el riesgo
            $keywords = [
                'suicidio', 'suicidarme', 'quitarme la vida', 'matarme', 'morir', 
                'sin esperanza', 'no quiero vivir', 'me quiero morir', 'depresión', 
                'angustia', 'ansiedad', 'crisis', 'no vale la pena'
            ];
            
            $allMessages = $conversation->messages()->where('role', '!=', 'system')->orderBy('created_at')->get();
            
            foreach ($allMessages as $message) {
                $content = strtolower($message->content);
                foreach ($keywords as $keyword) {
                    if (strpos($content, strtolower($keyword)) !== false) {
                        $relevantMessages->push($message);
                        break;
                    }
                }
            }
            
            // Limitar a los 10 mensajes más relevantes
            $relevantMessages = $relevantMessages->take(10);
        }
        
        // Preparar clases de estilo según el nivel de riesgo
        $riskHeaderClass = 'bg-success';
        $riskCircleClass = 'risk-low';
        $statusBadgeClass = 'bg-secondary';
        $statusLabel = 'Pendiente';
        
        // Asignar clases según el nivel de riesgo
        switch (strtolower($assessment->risk_level)) {
            case 'bajo':
                $riskHeaderClass = 'bg-success';
                $riskCircleClass = 'risk-low';
                break;
            case 'medio':
                $riskHeaderClass = 'bg-warning';
                $riskCircleClass = 'risk-medium';
                break;
            case 'alto':
                $riskHeaderClass = 'bg-danger';
                $riskCircleClass = 'risk-high';
                break;
            case 'crítico':
            case 'critico':
                $riskHeaderClass = 'bg-dark';
                $riskCircleClass = 'risk-critical';
                break;
        }
        
        // Asignar clase según el estado y nivel de riesgo
        // Si el nivel de riesgo es alto, mostrar como urgente independientemente del estado
        $riskLevelHigh = in_array(strtolower($assessment->risk_level), ['alto', 'crítico', 'critico']);
        
        if ($riskLevelHigh) {
            // Es una evaluación de alto riesgo - mostrar como urgente
            $statusBadgeClass = 'bg-danger';
            $statusLabel = 'URGENTE - Alto Riesgo';
        } else {
            // Mostrar según el estado normal
            switch ($assessment->status) {
                case 'pending':
                    $statusBadgeClass = 'bg-secondary';
                    $statusLabel = 'Pendiente';
                    break;
                case 'reviewed':
                    $statusBadgeClass = 'bg-success';
                    $statusLabel = 'Revisado';
                    break;
                case 'archived':
                    $statusBadgeClass = 'bg-info';
                    $statusLabel = 'Archivado';
                    break;
            }
        }
        
        // Devolver la vista con todos los datos
        return view('risk-assessment.detail', [
            'assessment' => $assessment,
            'patientName' => $patientName,
            'professionalName' => $professionalName,
            'reviewerName' => $reviewerName,
            'conversation' => $conversation,
            'relevantMessages' => $relevantMessages,
            'riskHeaderClass' => $riskHeaderClass,
            'riskCircleClass' => $riskCircleClass,
            'statusBadgeClass' => $statusBadgeClass,
            'statusLabel' => $statusLabel,
            'riskFactors' => $assessment->riskFactors,
            'warningSigns' => $assessment->warningSigns
        ]);
    }
    
    /**
     * Actualizar el estado de una evaluación de riesgo
     */
    public function updateStatus(Request $request, $id)
    {
        $assessment = RiskAssessment::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,reviewed,archived'
        ]);
        
        $assessment->status = $request->status;
        
        // Si el estado es 'reviewed', actualizar la información de revisión
        if ($request->status === 'reviewed') {
            $assessment->reviewed_at = now();
            $assessment->reviewed_by = Auth::id(); // Si hay un usuario autenticado
        }
        
        $assessment->save();
        
        // Limpiar la caché del contador de alertas
        \Illuminate\Support\Facades\Cache::forget('risk_alert_count');
        
        return redirect()->route('risk-assessment.show', $id)
            ->with('success', 'Estado de la evaluación actualizado correctamente');
    }
    
    /**
     * Marcar una evaluación como crítica
     */
    public function markAsCritical($id)
    {
        $assessment = RiskAssessment::findOrFail($id);
        $assessment->risk_level = 'crítico';
        $assessment->save();
        
        return redirect()->route('risk-assessment.show', $assessment->id)
            ->with('success', 'Evaluación marcada como crítica');
    }
    
    /**
     * Mostrar listado de alertas críticas de riesgo
     */
    public function alerts()
    {
        // Forzar sincronización con la base de datos
        \Illuminate\Support\Facades\DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        $query = RiskAssessment::query()->with(['conversation', 'riskFactors'])->withoutGlobalScopes()
            ->where('status', 'urgent')
            ->where(function($query) {
                $query->where('risk_level', 'alto')
                      ->orWhere('risk_level', 'crítico')
                      ->orWhere('risk_level', 'critico');
            });
        
        // Ordenar por fecha (más recientes primero)
        $query->orderBy('created_at', 'desc');
        
        $assessments = $query->paginate(10);
        
        // Preparar los datos para la vista
        $assessmentsData = $assessments->map(function($assessment) {
            $patientName = 'Paciente anónimo';
            $patientDocument = $assessment->patient_document;
            
            if ($patientDocument) {
                $patient = PrincipalIntegrante::where('documento', $patientDocument)->first();
                if ($patient) {
                    $patientName = trim($patient->nombre1 . ' ' . $patient->nombre2 . ' ' . 
                                  $patient->apellido1 . ' ' . $patient->apellido2);
                }
            }
            
            // Obtener los factores de riesgo principales
            $riskFactors = $assessment->riskFactors()->take(3)->get()->pluck('description')->toArray();
            
            return [
                'id' => $assessment->id,
                'patient_name' => $patientName,
                'patient_document' => $patientDocument,
                'risk_level' => $assessment->risk_level,
                'risk_score' => $assessment->risk_score,
                'status' => $assessment->status,
                'created_at' => $assessment->created_at,
                'risk_factors' => $riskFactors,
                'conversation_id' => $assessment->conversation_id
            ];
        });
        
        return view('risk-assessment.alerts', [
            'assessments' => $assessments,
            'assessmentsData' => $assessmentsData,
        ]);
    }
    
    /**
     * Recalcular todas las evaluaciones de riesgo
     */
    public function recalculateAllRiskAssessments()
    {
        // Obtener todas las evaluaciones de riesgo
        $assessments = RiskAssessment::all();
        $count = 0;
        
        try {
            foreach ($assessments as $assessment) {
                // Obtener la conversación asociada
                $conversation = $assessment->conversation;
                if (!$conversation) {
                    continue; // Saltar si no hay conversación asociada
                }
                
                // Crear instancia del servicio de detección de riesgo
                $riskService = app(\App\Services\RiskDetectionService::class);
                
                // Recalcular el riesgo basado en la conversación
                $updatedAssessment = $riskService->detectRiskFromConversation($conversation);
                
                // Actualizar la evaluación de riesgo existente
                $assessment->risk_score = $updatedAssessment->risk_score;
                $assessment->risk_level = $updatedAssessment->risk_level;
                $assessment->save();
                
                // Registrar la actualización
                Log::info("Evaluación recalculada", [
                    'assessment_id' => $assessment->id,
                    'old_level' => $assessment->getOriginal('risk_level'),
                    'new_level' => $assessment->risk_level,
                    'old_score' => $assessment->getOriginal('risk_score'),
                    'new_score' => $assessment->risk_score
                ]);
                
                $count++;
            }
            
            return $count;
        } catch (\Exception $e) {
            // Loguear el error pero continuar con las demás
            Log::error("Error recalculando evaluaciones de riesgo: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * Sincronizar todas las evaluaciones de alto riesgo
     */
    public function syncAllHighRiskAssessments()
    {
        // Buscar evaluaciones de alto riesgo
        $highRiskAssessments = RiskAssessment::whereIn('risk_level', ['alto', 'crítico', 'critico'])
                              ->get();
        
        foreach ($highRiskAssessments as $assessment) {
            // Sincronizar el nivel de riesgo con el estado
            $this->syncRiskLevel($assessment);
        }
        
        // Ya no forzamos nivel de riesgo alto para todas las evaluaciones pendientes
        // Esto permite que el nivel de riesgo se determine correctamente según la puntuación
        
        // Solo actualizamos aquellas que explícitamente están marcadas como urgentes
        $urgentAssessments = RiskAssessment::where('status', 'urgent')
                            ->whereNotIn('risk_level', ['alto', 'crítico', 'critico'])
                            ->get();
        
        // Actualizar el nivel de riesgo a 'alto' solo para urgentes explícitos
        foreach ($urgentAssessments as $assessment) {
            $assessment->risk_level = 'alto';
            $assessment->risk_score = max($assessment->risk_score, 75);
            $assessment->save();
            
            Log::info('Nivel de riesgo actualizado a alto por status urgent explícito', [
                'assessment_id' => $assessment->id
            ]);
        }
        
        return true;
    }
    
    /**
     * Sincroniza el nivel de riesgo con el estado para una evaluación específica
     */
    public function syncRiskLevel(RiskAssessment $assessment)
    {
        // Verificar si hay discrepancia entre el nivel de riesgo y el estado
        $riskLevelHigh = in_array(strtolower($assessment->risk_level), ['alto', 'crítico', 'critico']);
        
        // Si el riesgo es alto pero el status no es reviewed, actualizar
        if ($riskLevelHigh && $assessment->status !== 'reviewed') {
            $assessment->status = 'reviewed';
            $assessment->save();
            
            Log::info('Estado actualizado a reviewed por alto riesgo', [
                'assessment_id' => $assessment->id,
                'risk_level' => $assessment->risk_level
            ]);
            
            // Limpiar caché del contador
            \Illuminate\Support\Facades\Cache::forget('risk_alert_count');
        }
    }
}
