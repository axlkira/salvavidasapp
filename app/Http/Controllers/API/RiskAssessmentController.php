<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RiskAssessment;
use App\Models\RiskFactor;
use App\Models\WarningSign;
use App\Models\InterventionGuide;
use App\Models\Individual;
use App\Models\PrincipalIntegrante;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RiskAssessmentController extends Controller
{
    protected $aiService;
    
    /**
     * Constructor
     */
    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }
    
    /**
     * Obtener todas las evaluaciones de riesgo
     */
    public function index(Request $request)
    {
        $query = RiskAssessment::query()
            ->with(['patient', 'individual', 'riskFactors', 'warningSigns'])
            ->orderBy('created_at', 'desc');
            
        // Filtrar por nivel de riesgo si se proporciona
        if ($request->has('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }
        
        // Filtrar por profesional
        if ($request->has('professional_id')) {
            $query->where('professional_id', $request->professional_id);
        } else {
            // Por defecto, mostrar solo las del profesional actual
            $query->where('professional_id', Auth::id());
        }
        
        // Filtrar por estado
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Paginación
        $perPage = $request->per_page ?? 15;
        $assessments = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'assessments' => $assessments
        ]);
    }
    
    /**
     * Obtener una evaluación de riesgo específica
     */
    public function show(Request $request, $id)
    {
        $assessment = RiskAssessment::with([
                'patient', 
                'individual', 
                'riskFactors', 
                'warningSigns',
                'interventionGuide',
                'professional',
                'reviewer'
            ])
            ->findOrFail($id);
        
        // Verificar permiso (solo el creador o un administrador puede ver)
        if ($assessment->professional_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para ver esta evaluación'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'assessment' => $assessment
        ]);
    }
    
    /**
     * Crear una nueva evaluación de riesgo para un paciente
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_document' => 'required|string|max:30',
            'individual_id' => 'nullable|integer',
            'conversation_id' => 'nullable|integer|exists:conversations,id',
            'provider' => 'nullable|string|max:30',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos para la evaluación',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar que el paciente existe
        $patient = PrincipalIntegrante::where('documento', $request->patient_document)->first();
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no encontrado con el documento proporcionado'
            ], 404);
        }
        
        // Obtener la historia clínica más reciente si no se proporciona una específica
        $individualId = $request->individual_id;
        if (!$individualId) {
            $latestIndividual = Individual::where('Documento', $request->patient_document)
                ->orderBy('id', 'desc')
                ->first();
                
            if ($latestIndividual) {
                $individualId = $latestIndividual->id;
            }
        }
        
        // Obtener la historia clínica para análisis
        $individual = Individual::find($individualId);
        if (!$individual) {
            return response()->json([
                'success' => false,
                'message' => 'Historia clínica no encontrada'
            ], 404);
        }
        
        // Obtener los datos para el análisis
        $patientData = $individual->getDataForAnalysis();
        
        // Realizar el análisis de riesgo con la IA
        $provider = $request->provider ?? config('ai.default_provider');
        $analysisResult = $this->aiService->analyzePatientRisk($patientData, $provider);
        
        if (!$analysisResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el análisis de riesgo: ' . ($analysisResult['error'] ?? 'Error desconocido')
            ], 500);
        }
        
        // Crear la evaluación de riesgo
        $assessment = new RiskAssessment();
        $assessment->conversation_id = $request->conversation_id;
        $assessment->individual_id = $individualId;
        $assessment->patient_document = $request->patient_document;
        $assessment->professional_id = Auth::id();
        $assessment->risk_score = $analysisResult['risk_score'] ?? 0;
        $assessment->risk_level = $analysisResult['risk_level'] ?? 'indeterminado';
        $assessment->provider = $provider;
        $assessment->model = $analysisResult['model'] ?? config('ai.providers.' . $provider . '.model');
        $assessment->status = 'pending';
        $assessment->save();
        
        // Guardar factores de riesgo
        if (isset($analysisResult['risk_factors']) && is_array($analysisResult['risk_factors'])) {
            foreach ($analysisResult['risk_factors'] as $factor) {
                $riskFactor = new RiskFactor();
                $riskFactor->risk_assessment_id = $assessment->id;
                $riskFactor->description = $factor;
                $riskFactor->save();
            }
        }
        
        // Guardar señales de alerta
        if (isset($analysisResult['warning_signs']) && is_array($analysisResult['warning_signs'])) {
            foreach ($analysisResult['warning_signs'] as $sign) {
                $warningSign = new WarningSign();
                $warningSign->risk_assessment_id = $assessment->id;
                $warningSign->description = $sign;
                $warningSign->is_critical = false; // Por defecto no es crítico
                $warningSign->save();
            }
        }
        
        // Guardar indicadores críticos como señales de alerta críticas
        if (isset($analysisResult['critical_indicators']) && is_array($analysisResult['critical_indicators'])) {
            foreach ($analysisResult['critical_indicators'] as $indicator) {
                $criticalSign = new WarningSign();
                $criticalSign->risk_assessment_id = $assessment->id;
                $criticalSign->description = $indicator;
                $criticalSign->is_critical = true;
                $criticalSign->save();
            }
        }
        
        // Generar automáticamente una guía de intervención si el riesgo es moderado o alto
        if (in_array(strtolower($assessment->risk_level), ['moderado', 'alto', 'crítico', 'critico'])) {
            $this->generateInterventionGuide($assessment->id);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Evaluación de riesgo creada correctamente',
            'assessment' => $assessment->load(['riskFactors', 'warningSigns', 'interventionGuide'])
        ], 201);
    }
    
    /**
     * Actualizar el estado de una evaluación de riesgo
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,reviewed,archived'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Estado inválido',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $assessment = RiskAssessment::findOrFail($id);
        
        // Verificar permiso (solo el creador o un administrador puede actualizar)
        if ($assessment->professional_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para actualizar esta evaluación'
            ], 403);
        }
        
        $assessment->status = $request->status;
        
        if ($request->status == 'reviewed') {
            $assessment->reviewed_at = now();
            $assessment->reviewed_by = Auth::id();
        }
        
        $assessment->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Estado de evaluación actualizado correctamente',
            'assessment' => $assessment
        ]);
    }
    
    /**
     * Generar guía de intervención para una evaluación de riesgo
     */
    public function generateInterventionGuide($assessmentId = null, Request $request = null)
    {
        // Si se llama desde la API, usar el ID del request
        if ($request !== null) {
            $assessmentId = $request->assessment_id;
            $validator = Validator::make(['assessment_id' => $assessmentId], [
                'assessment_id' => 'required|exists:risk_assessments,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de evaluación inválido',
                    'errors' => $validator->errors()
                ], 422);
            }
        }
        
        $assessment = RiskAssessment::with(['riskFactors', 'warningSigns', 'patient', 'individual'])
            ->findOrFail($assessmentId);
            
        // Verificar si ya existe una guía
        if ($assessment->interventionGuide && $request !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una guía de intervención para esta evaluación',
                'guide' => $assessment->interventionGuide
            ], 422);
        }
        
        // Preparar datos para generar la guía
        $patientData = [];
        if ($assessment->individual) {
            $patientData = $assessment->individual->getDataForAnalysis();
        }
        
        // Preparar datos de la evaluación
        $riskAssessmentData = [
            'risk_score' => $assessment->risk_score,
            'risk_level' => $assessment->risk_level,
            'risk_factors' => $assessment->riskFactors->pluck('description')->toArray(),
            'warning_signs' => $assessment->warningSigns->where('is_critical', false)->pluck('description')->toArray(),
            'critical_indicators' => $assessment->warningSigns->where('is_critical', true)->pluck('description')->toArray()
        ];
        
        // Generar la guía de intervención
        $guideResult = $this->aiService->generateInterventionGuide($patientData, $riskAssessmentData, $assessment->provider);
        
        if (!$guideResult['success']) {
            $errorMessage = 'Error al generar guía de intervención: ' . ($guideResult['error'] ?? 'Error desconocido');
            
            if ($request !== null) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            } else {
                Log::error($errorMessage);
                return false;
            }
        }
        
        // Crear o actualizar la guía de intervención
        $guide = InterventionGuide::updateOrCreate(
            ['risk_assessment_id' => $assessment->id],
            [
                'steps' => json_encode($guideResult['steps'] ?? []),
                'techniques' => json_encode($guideResult['techniques'] ?? []),
                'resources' => json_encode($guideResult['resources'] ?? []),
                'follow_up_plan' => json_encode($guideResult['follow_up_plan'] ?? []),
                'communication_strategies' => json_encode($guideResult['communication_strategies'] ?? []),
                'safety_plan' => json_encode($guideResult['safety_plan'] ?? [])
            ]
        );
        
        if ($request !== null) {
            return response()->json([
                'success' => true,
                'message' => 'Guía de intervención generada correctamente',
                'guide' => $guide
            ]);
        }
        
        return true;
    }
    
    /**
     * Obtener pacientes de alto riesgo
     */
    public function getHighRiskPatients()
    {
        $highRiskAssessments = RiskAssessment::whereIn('risk_level', ['alto', 'crítico', 'critico'])
            ->with(['patient', 'individual', 'riskFactors', 'warningSigns'])
            ->orderBy('risk_score', 'desc')
            ->take(10)
            ->get();
            
        return response()->json([
            'success' => true,
            'patients' => $highRiskAssessments
        ]);
    }
    
    /**
     * Obtener estadísticas de riesgo
     */
    public function getRiskStats()
    {
        $stats = [
            'total' => RiskAssessment::count(),
            'by_level' => [
                'bajo' => RiskAssessment::where('risk_level', 'bajo')->count(),
                'moderado' => RiskAssessment::where('risk_level', 'moderado')->count(),
                'alto' => RiskAssessment::where('risk_level', 'alto')->count(),
                'critico' => RiskAssessment::whereIn('risk_level', ['crítico', 'critico'])->count(),
            ],
            'pending_review' => RiskAssessment::where('status', 'pending')->count(),
            'reviewed' => RiskAssessment::where('status', 'reviewed')->count(),
            'recent' => RiskAssessment::orderBy('created_at', 'desc')->take(5)->get()
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
