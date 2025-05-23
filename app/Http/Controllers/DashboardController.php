<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RiskAssessment;
use App\Models\Conversation;
use App\Models\PrincipalIntegrante;
use App\Models\Individual;

class DashboardController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Autenticación temporalmente deshabilitada para demostración
        // $this->middleware('auth');
    }
    
    /**
     * Mostrar el dashboard principal
     */
    public function index()
    {
        // Estadísticas reales de la base de datos
        $totalPatients = PrincipalIntegrante::count();
        $totalAssessments = RiskAssessment::count();
        $highRiskCount = RiskAssessment::whereIn('risk_level', ['alto', 'crítico'])->count();
        $totalConversations = Conversation::count();
        
        $stats = [
            'total_patients' => $totalPatients,
            'total_assessments' => $totalAssessments,
            'high_risk_count' => $highRiskCount,
            'total_conversations' => $totalConversations
        ];
        
        // Pacientes con alto riesgo
        $highRiskPatients = RiskAssessment::with(['conversation'])
            ->whereIn('risk_level', ['alto', 'crítico'])
            ->orderBy('risk_score', 'desc')
            ->take(5)
            ->get()
            ->map(function($assessment) {
                $patientName = 'Paciente anónimo';
                $patientDocument = $assessment->patient_document;
                
                if ($patientDocument) {
                    $patient = PrincipalIntegrante::where('documento', $patientDocument)->first();
                    if ($patient) {
                        $patientName = trim($patient->nombre1 . ' ' . $patient->nombre2 . ' ' . 
                                          $patient->apellido1 . ' ' . $patient->apellido2);
                    }
                }
                
                $riskFactors = $assessment->riskFactors()->take(3)->get()->pluck('description')->toArray();
                
                return [
                    'id' => $assessment->id,
                    'patient_name' => $patientName,
                    'patient_document' => $patientDocument,
                    'risk_level' => $assessment->risk_level,
                    'risk_score' => $assessment->risk_score,
                    'created_at' => $assessment->created_at,
                    'risk_factors' => $riskFactors,
                    'conversation_id' => $assessment->conversation_id
                ];
            });
        
        // Últimas evaluaciones
        $latestAssessments = RiskAssessment::orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($assessment) {
                $patientName = 'Paciente anónimo';
                $patientDocument = $assessment->patient_document;
                
                if ($patientDocument) {
                    $patient = PrincipalIntegrante::where('documento', $patientDocument)->first();
                    if ($patient) {
                        $patientName = trim($patient->nombre1 . ' ' . $patient->nombre2 . ' ' . 
                                          $patient->apellido1 . ' ' . $patient->apellido2);
                    }
                }
                
                return [
                    'id' => $assessment->id,
                    'patient_name' => $patientName,
                    'risk_level' => $assessment->risk_level,
                    'risk_score' => $assessment->risk_score,
                    'created_at' => $assessment->created_at,
                    'conversation_id' => $assessment->conversation_id
                ];
            });
        
        // Últimas conversaciones
        $latestConversations = Conversation::orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($conversation) {
                $patientName = 'Conversación general';
                $patientDocument = $conversation->patient_document;
                $riskLevel = 'desconocido';
                $riskScore = 0;
                
                // Obtener nombre del paciente si existe
                if ($patientDocument) {
                    $patient = PrincipalIntegrante::where('documento', $patientDocument)->first();
                    if ($patient) {
                        $patientName = trim($patient->nombre1 . ' ' . $patient->nombre2 . ' ' . 
                                          $patient->apellido1 . ' ' . $patient->apellido2);
                    }
                    
                    // Obtener nivel de riesgo si existe
                    $assessment = RiskAssessment::where('conversation_id', $conversation->id)
                                  ->orderBy('created_at', 'desc')
                                  ->first();
                    if ($assessment) {
                        $riskLevel = $assessment->risk_level;
                        $riskScore = $assessment->risk_score;
                    }
                }
                
                // Obtener último mensaje
                $lastMessage = $conversation->messages()
                               ->where('role', '!=', 'system')
                               ->orderBy('created_at', 'desc')
                               ->first();
                
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'patient_name' => $patientName,
                    'patient_document' => $patientDocument,
                    'risk_level' => $riskLevel,
                    'risk_score' => $riskScore,
                    'updated_at' => $conversation->updated_at,
                    'last_message' => $lastMessage ? substr($lastMessage->content, 0, 100) . '...' : 'Sin mensajes'
                ];
            });
        
        // Datos para el gráfico de los últimos 6 meses
        $months = [];
        $lowRisk = [];
        $mediumRisk = [];
        $highRisk = [];
        
        // Generar datos para los últimos 6 meses
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $startDate = $date->startOfMonth()->format('Y-m-d H:i:s');
            $endDate = $date->endOfMonth()->format('Y-m-d H:i:s');
            
            $lowRisk[] = RiskAssessment::where('risk_level', 'bajo')
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->count();
                          
            $mediumRisk[] = RiskAssessment::where('risk_level', 'medio')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->count();
                            
            $highRisk[] = RiskAssessment::whereIn('risk_level', ['alto', 'crítico'])
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->count();
        }
        
        $chartData = [
            'months' => $months,
            'low_risk' => $lowRisk,
            'medium_risk' => $mediumRisk,
            'high_risk' => $highRisk
        ];
        
        return view('dashboard', compact('stats', 'highRiskPatients', 'latestAssessments', 'latestConversations', 'chartData'));
    }
    
    /**
     * Generar datos para el gráfico
     */
    private function getChartData()
    {
        // Meses recientes (hasta 5)
        $months = [];
        $lowRisk = [];
        $mediumRisk = [];
        $highRisk = [];
        
        // Obtener datos de los últimos 5 meses
        for ($i = 0; $i < 5; $i++) {
            $date = now()->subMonths($i);
            $monthName = $date->format('F');
            $year = $date->year;
            $month = $date->month;
            
            array_unshift($months, $monthName);
            
            // Contar evaluaciones por nivel de riesgo en este mes
            $low = RiskAssessment::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('risk_level', 'bajo')
                ->count();
                
            $medium = RiskAssessment::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('risk_level', 'moderado')
                ->count();
                
            $high = RiskAssessment::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->whereIn('risk_level', ['alto', 'crítico', 'critico'])
                ->count();
                
            array_unshift($lowRisk, $low);
            array_unshift($mediumRisk, $medium);
            array_unshift($highRisk, $high);
        }
        
        return [
            'months' => $months,
            'low_risk' => $lowRisk,
            'medium_risk' => $mediumRisk,
            'high_risk' => $highRisk
        ];
    }
}
