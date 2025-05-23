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
        // Datos de demostración sin necesidad de base de datos
        // Estadísticas demo
        $stats = [
            'total_patients' => 1250,
            'total_assessments' => 218,
            'high_risk_count' => 42,
            'total_conversations' => 356
        ];
        
        // No necesitamos datos reales para la demo
        $highRiskPatients = [];
        $latestAssessments = [];
        $latestConversations = [];
        
        // Datos demo para el gráfico
        $chartData = [
            'months' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'],
            'low_risk' => [42, 35, 50, 45, 58],
            'medium_risk' => [18, 25, 30, 22, 28],
            'high_risk' => [8, 12, 15, 10, 12]
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
