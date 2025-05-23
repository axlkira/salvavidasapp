<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskAssessment;
use App\Models\PrincipalIntegrante;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

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
        $query = RiskAssessment::query()->with(['conversation', 'riskFactors']);
        
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
            ]
        ]);
    }
    
    /**
     * Mostrar detalle de una evaluación de riesgo
     */
    public function show($id)
    {
        $assessment = RiskAssessment::with(['conversation', 'riskFactors', 'warningSigns'])->findOrFail($id);
        
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
        $conversation = $assessment->conversation;
        
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
        switch ($assessment->risk_level) {
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
        
        // Asignar clase según el estado
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
        
        // Obtener los factores de riesgo
        $riskFactors = $assessment->riskFactors()->get();
        
        // Obtener las señales de advertencia
        $warningSigns = $assessment->warningSigns()->get();
        
        return view('risk-assessment.detail', compact(
            'assessment',
            'patientName',
            'professionalName',
            'reviewerName',
            'conversation',
            'relevantMessages',
            'riskHeaderClass',
            'riskCircleClass',
            'statusBadgeClass',
            'statusLabel',
            'riskFactors',
            'warningSigns'
        ));
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
        
        return redirect()->route('risk-assessment.show', $assessment->id)
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
}
