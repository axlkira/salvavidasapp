<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskAssessment;
use App\Models\RiskFactor;
use App\Models\WarningSign;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Individual;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Mostrar el dashboard principal de analíticas
     */
    public function index()
    {
        // Datos para el panel principal
        $stats = $this->getBasicStats();
        $riskTrends = $this->getRiskTrends();
        $commonFactors = $this->getCommonRiskFactors();
        
        return view('analytics.index', [
            'stats' => $stats,
            'riskTrends' => $riskTrends,
            'commonFactors' => $commonFactors
        ]);
    }
    
    /**
     * Mostrar análisis de tendencias de riesgo por período
     */
    public function riskTrends(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonths(6);
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        
        $trends = $this->getRiskTrendsForPeriod($period, $startDate, $endDate);
        
        return view('analytics.risk-trends', [
            'trends' => $trends,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    /**
     * Mostrar análisis de factores de riesgo comunes
     */
    public function riskFactors()
    {
        $commonFactors = $this->getCommonRiskFactors(15); // Top 15 factores
        $factorsByRiskLevel = $this->getRiskFactorsByLevel();
        
        return view('analytics.risk-factors', [
            'commonFactors' => $commonFactors,
            'factorsByRiskLevel' => $factorsByRiskLevel
        ]);
    }
    
    /**
     * Mostrar análisis de la eficacia de las intervenciones
     */
    public function interventionEffectiveness()
    {
        $effectiveness = $this->getInterventionEffectiveness();
        $followUpStats = $this->getFollowUpStats();
        
        return view('analytics.intervention-effectiveness', [
            'effectiveness' => $effectiveness,
            'followUpStats' => $followUpStats
        ]);
    }
    
    /**
     * Mostrar análisis de patrones de conversación
     */
    public function conversationPatterns()
    {
        $patterns = $this->getConversationPatterns();
        $keyPhrases = $this->getKeyPhrases();
        
        return view('analytics.conversation-patterns', [
            'patterns' => $patterns,
            'keyPhrases' => $keyPhrases
        ]);
    }
    
    /**
     * Obtener estadísticas básicas
     */
    private function getBasicStats()
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        
        // Evaluaciones totales
        $totalAssessments = RiskAssessment::count();
        
        // Evaluaciones por nivel de riesgo
        $highRiskCount = RiskAssessment::whereIn('risk_level', ['alto', 'crítico', 'critico'])->count();
        $mediumRiskCount = RiskAssessment::where('risk_level', 'medio')->count();
        $lowRiskCount = RiskAssessment::where('risk_level', 'bajo')->count();
        
        // Evaluaciones por estado
        $pendingCount = RiskAssessment::where('status', 'pending')->count();
        $reviewedCount = RiskAssessment::where('status', 'reviewed')->count();
        $urgentCount = RiskAssessment::where('status', 'urgent')->count();
        
        // Evaluaciones este mes vs mes anterior
        $thisMonthCount = RiskAssessment::whereBetween('created_at', [$monthStart, $now])->count();
        $lastMonthCount = RiskAssessment::whereBetween('created_at', [$lastMonth, $monthStart])->count();
        $percentChange = $lastMonthCount > 0 ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1) : 0;
        
        return [
            'total' => $totalAssessments,
            'high_risk' => $highRiskCount,
            'medium_risk' => $mediumRiskCount,
            'low_risk' => $lowRiskCount,
            'risk_percentage' => [
                'high' => $totalAssessments > 0 ? round(($highRiskCount / $totalAssessments) * 100, 1) : 0,
                'medium' => $totalAssessments > 0 ? round(($mediumRiskCount / $totalAssessments) * 100, 1) : 0,
                'low' => $totalAssessments > 0 ? round(($lowRiskCount / $totalAssessments) * 100, 1) : 0,
            ],
            'status' => [
                'pending' => $pendingCount,
                'reviewed' => $reviewedCount,
                'urgent' => $urgentCount
            ],
            'this_month' => $thisMonthCount,
            'last_month' => $lastMonthCount,
            'percent_change' => $percentChange,
            'date_range' => [
                'this_month' => $monthStart->format('Y-m-d'),
                'current' => $now->format('Y-m-d')
            ]
        ];
    }
    
    /**
     * Obtener tendencias de riesgo
     */
    private function getRiskTrends()
    {
        $now = Carbon::now();
        $sixMonthsAgo = $now->copy()->subMonths(6);
        
        // Tendencias por mes para los últimos 6 meses
        $monthlyTrends = RiskAssessment::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, risk_level, COUNT(*) as count')
            ->whereBetween('created_at', [$sixMonthsAgo, $now])
            ->groupBy('month', 'risk_level')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(function ($group) {
                return [
                    'high' => $group->whereIn('risk_level', ['alto', 'crítico', 'critico'])->sum('count'),
                    'medium' => $group->where('risk_level', 'medio')->sum('count'),
                    'low' => $group->where('risk_level', 'bajo')->sum('count'),
                    'total' => $group->sum('count')
                ];
            });
        
        // Preparar datos para gráficos
        $labels = [];
        $highRiskData = [];
        $mediumRiskData = [];
        $lowRiskData = [];
        
        // Asegurar que tenemos datos para todos los meses
        $current = $sixMonthsAgo->copy();
        while ($current <= $now) {
            $monthKey = $current->format('Y-m');
            $labels[] = $current->format('M Y');
            
            if ($monthlyTrends->has($monthKey)) {
                $data = $monthlyTrends[$monthKey];
                $highRiskData[] = $data['high'];
                $mediumRiskData[] = $data['medium'];
                $lowRiskData[] = $data['low'];
            } else {
                $highRiskData[] = 0;
                $mediumRiskData[] = 0;
                $lowRiskData[] = 0;
            }
            
            $current->addMonth();
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Alto Riesgo',
                    'data' => $highRiskData,
                    'backgroundColor' => 'rgba(220, 53, 69, 0.6)'
                ],
                [
                    'label' => 'Riesgo Medio',
                    'data' => $mediumRiskData,
                    'backgroundColor' => 'rgba(255, 193, 7, 0.6)'
                ],
                [
                    'label' => 'Bajo Riesgo',
                    'data' => $lowRiskData,
                    'backgroundColor' => 'rgba(40, 167, 69, 0.6)'
                ]
            ],
            'raw_data' => $monthlyTrends
        ];
    }
    
    /**
     * Obtener tendencias de riesgo para un período específico
     */
    private function getRiskTrendsForPeriod($period, $startDate, $endDate)
    {
        $format = '%Y-%m-%d';
        $interval = '1 DAY';
        
        if ($period === 'weekly') {
            $format = '%Y-%u'; // Año-Semana
            $interval = '1 WEEK';
        } elseif ($period === 'monthly') {
            $format = '%Y-%m'; // Año-Mes
            $interval = '1 MONTH';
        }
        
        $trends = RiskAssessment::selectRaw("DATE_FORMAT(created_at, '{$format}') as time_period, risk_level, COUNT(*) as count")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('time_period', 'risk_level')
            ->orderBy('time_period')
            ->get()
            ->groupBy('time_period');
        
        // Preparar datos para gráficos con interpolación para períodos sin datos
        $labels = [];
        $highRiskData = [];
        $mediumRiskData = [];
        $lowRiskData = [];
        
        // Generar todas las etiquetas de tiempo entre startDate y endDate
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $periodKey = '';
            
            if ($period === 'daily') {
                $periodKey = $current->format('Y-m-d');
                $labels[] = $current->format('d M');
                $current->addDay();
            } elseif ($period === 'weekly') {
                $periodKey = $current->format('Y-W');
                $labels[] = 'Sem ' . $current->format('W');
                $current->addWeek();
            } elseif ($period === 'monthly') {
                $periodKey = $current->format('Y-m');
                $labels[] = $current->format('M Y');
                $current->addMonth();
            }
            
            if ($trends->has($periodKey)) {
                $data = $trends[$periodKey];
                $highRiskData[] = $data->whereIn('risk_level', ['alto', 'crítico', 'critico'])->sum('count');
                $mediumRiskData[] = $data->where('risk_level', 'medio')->sum('count');
                $lowRiskData[] = $data->where('risk_level', 'bajo')->sum('count');
            } else {
                $highRiskData[] = 0;
                $mediumRiskData[] = 0;
                $lowRiskData[] = 0;
            }
        }
        
        return [
            'period' => $period,
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Alto Riesgo',
                    'data' => $highRiskData,
                    'backgroundColor' => 'rgba(220, 53, 69, 0.6)',
                    'borderColor' => 'rgb(220, 53, 69)',
                    'tension' => 0.1
                ],
                [
                    'label' => 'Riesgo Medio',
                    'data' => $mediumRiskData,
                    'backgroundColor' => 'rgba(255, 193, 7, 0.6)',
                    'borderColor' => 'rgb(255, 193, 7)',
                    'tension' => 0.1
                ],
                [
                    'label' => 'Bajo Riesgo',
                    'data' => $lowRiskData,
                    'backgroundColor' => 'rgba(40, 167, 69, 0.6)',
                    'borderColor' => 'rgb(40, 167, 69)',
                    'tension' => 0.1
                ]
            ]
        ];
    }
    
    /**
     * Obtener los factores de riesgo más comunes
     */
    private function getCommonRiskFactors($limit = 10)
    {
        // Agrupar y contar los factores de riesgo
        $factors = RiskFactor::select('description', DB::raw('COUNT(*) as count'))
            ->groupBy('description')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
        
        return [
            'labels' => $factors->pluck('description')->toArray(),
            'data' => $factors->pluck('count')->toArray(),
            'raw_data' => $factors
        ];
    }
    
    /**
     * Obtener factores de riesgo agrupados por nivel de riesgo
     */
    private function getRiskFactorsByLevel()
    {
        $factors = RiskFactor::join('risk_assessments', 'risk_factors.risk_assessment_id', '=', 'risk_assessments.id')
            ->select('risk_factors.description', 'risk_assessments.risk_level', DB::raw('COUNT(*) as count'))
            ->groupBy('description', 'risk_level')
            ->orderByDesc('count')
            ->get()
            ->groupBy('risk_level');
        
        // Preparar datos por nivel de riesgo
        $highRiskFactors = [];
        $mediumRiskFactors = [];
        $lowRiskFactors = [];
        
        if ($factors->has('alto')) {
            $highRiskFactors = $factors['alto']->take(10)->map(function ($item) {
                return ['description' => $item->description, 'count' => $item->count];
            })->toArray();
        }
        
        if ($factors->has('medio')) {
            $mediumRiskFactors = $factors['medio']->take(10)->map(function ($item) {
                return ['description' => $item->description, 'count' => $item->count];
            })->toArray();
        }
        
        if ($factors->has('bajo')) {
            $lowRiskFactors = $factors['bajo']->take(10)->map(function ($item) {
                return ['description' => $item->description, 'count' => $item->count];
            })->toArray();
        }
        
        return [
            'high' => $highRiskFactors,
            'medium' => $mediumRiskFactors,
            'low' => $lowRiskFactors
        ];
    }
    
    /**
     * Obtener estadísticas de efectividad de intervenciones
     */
    private function getInterventionEffectiveness()
    {
        // Calculamos la efectividad basada en si los pacientes de alto riesgo
        // mejoraron su estado en evaluaciones posteriores
        $assessments = RiskAssessment::whereIn('risk_level', ['alto', 'crítico', 'critico'])
            ->where('status', 'reviewed')
            ->get();
        
        $improved = 0;
        $stable = 0;
        $worsened = 0;
        
        foreach ($assessments as $assessment) {
            // Buscar evaluaciones posteriores para el mismo paciente
            $followUps = RiskAssessment::where('patient_document', $assessment->patient_document)
                ->where('created_at', '>', $assessment->created_at)
                ->orderBy('created_at', 'asc')
                ->get();
            
            if ($followUps->count() > 0) {
                $latestFollowUp = $followUps->last();
                
                // Comparar niveles de riesgo
                $riskLevels = [
                    'bajo' => 1,
                    'medio' => 2,
                    'alto' => 3,
                    'crítico' => 4,
                    'critico' => 4
                ];
                
                $initialRisk = $riskLevels[strtolower($assessment->risk_level)] ?? 0;
                $followUpRisk = $riskLevels[strtolower($latestFollowUp->risk_level)] ?? 0;
                
                if ($followUpRisk < $initialRisk) {
                    $improved++;
                } elseif ($followUpRisk === $initialRisk) {
                    $stable++;
                } else {
                    $worsened++;
                }
            }
        }
        
        $total = $improved + $stable + $worsened;
        
        return [
            'improved' => $improved,
            'stable' => $stable,
            'worsened' => $worsened,
            'total' => $total,
            'percentage' => [
                'improved' => $total > 0 ? round(($improved / $total) * 100, 1) : 0,
                'stable' => $total > 0 ? round(($stable / $total) * 100, 1) : 0,
                'worsened' => $total > 0 ? round(($worsened / $total) * 100, 1) : 0
            ]
        ];
    }
    
    /**
     * Obtener estadísticas de seguimiento
     */
    private function getFollowUpStats()
    {
        $highRiskAssessments = RiskAssessment::whereIn('risk_level', ['alto', 'crítico', 'critico'])->count();
        $followedUp = 0;
        
        $assessments = RiskAssessment::whereIn('risk_level', ['alto', 'crítico', 'critico'])->get();
        
        foreach ($assessments as $assessment) {
            $hasFollowUp = RiskAssessment::where('patient_document', $assessment->patient_document)
                ->where('created_at', '>', $assessment->created_at)
                ->exists();
            
            if ($hasFollowUp) {
                $followedUp++;
            }
        }
        
        return [
            'high_risk_total' => $highRiskAssessments,
            'followed_up' => $followedUp,
            'not_followed' => $highRiskAssessments - $followedUp,
            'percentage' => $highRiskAssessments > 0 ? round(($followedUp / $highRiskAssessments) * 100, 1) : 0
        ];
    }
    
    /**
     * Obtener patrones de conversación
     */
    private function getConversationPatterns()
    {
        $highRiskConversations = Conversation::whereHas('riskAssessment', function ($query) {
            $query->whereIn('risk_level', ['alto', 'crítico', 'critico']);
        })->count();
        
        $totalConversations = Conversation::count();
        
        // Longitud promedio de conversaciones de alto riesgo vs otras
        $highRiskMessageCount = Message::whereHas('conversation', function ($query) {
            $query->whereHas('riskAssessment', function ($subQuery) {
                $subQuery->whereIn('risk_level', ['alto', 'crítico', 'critico']);
            });
        })->count();
        
        $otherMessageCount = Message::count() - $highRiskMessageCount;
        
        $highRiskAvgLength = 0;
        if ($highRiskConversations > 0) {
            $highRiskAvgLength = round($highRiskMessageCount / $highRiskConversations, 1);
        }
        
        $otherAvgLength = 0;
        $otherConversations = $totalConversations - $highRiskConversations;
        if ($otherConversations > 0) {
            $otherAvgLength = round($otherMessageCount / $otherConversations, 1);
        }
        
        return [
            'high_risk_conversations' => $highRiskConversations,
            'other_conversations' => $otherConversations,
            'high_risk_avg_length' => $highRiskAvgLength,
            'other_avg_length' => $otherAvgLength,
            'conversation_percentage' => $totalConversations > 0 ? 
                round(($highRiskConversations / $totalConversations) * 100, 1) : 0
        ];
    }
    
    /**
     * Obtener frases clave encontradas en conversaciones de alto riesgo
     */
    private function getKeyPhrases()
    {
        // Simulación de análisis de frases clave
        // En una implementación real, esto sería un análisis NLP más sofisticado
        return [
            [
                'phrase' => 'No quiero vivir más',
                'frequency' => 15,
                'risk_level' => 'alto'
            ],
            [
                'phrase' => 'No le veo sentido a la vida',
                'frequency' => 12,
                'risk_level' => 'alto'
            ],
            [
                'phrase' => 'Siento que soy una carga',
                'frequency' => 10,
                'risk_level' => 'alto'
            ],
            [
                'phrase' => 'Me siento desesperado',
                'frequency' => 9,
                'risk_level' => 'medio'
            ],
            [
                'phrase' => 'Ya no puedo más',
                'frequency' => 8,
                'risk_level' => 'medio'
            ],
            [
                'phrase' => 'Nadie me va a extrañar',
                'frequency' => 7,
                'risk_level' => 'alto'
            ],
            [
                'phrase' => 'He estado pensando en desaparecer',
                'frequency' => 6,
                'risk_level' => 'alto'
            ],
            [
                'phrase' => 'Me siento solo',
                'frequency' => 5,
                'risk_level' => 'medio'
            ]
        ];
    }
}
