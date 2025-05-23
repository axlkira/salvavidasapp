<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIRiskAnalysisService;
use Illuminate\Support\Facades\Log;

class AnalyzeRiskCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk:analyze 
                            {--days=7 : Número de días desde la última actividad} 
                            {--provider= : Proveedor de IA a utilizar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analiza las conversaciones recientes para detectar riesgo de suicidio';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando análisis de riesgo...');
        
        $days = $this->option('days');
        $provider = $this->option('provider');
        
        $this->info("Analizando conversaciones con actividad en los últimos $days días...");
        if ($provider) {
            $this->info("Usando proveedor de IA: $provider");
        }
        
        try {
            $riskService = new AIRiskAnalysisService();
            $results = $riskService->analyzeRecentConversations($days, $provider);
            
            $this->info('Análisis completado con éxito:');
            $this->info("Total de conversaciones: {$results['total']}");
            $this->info("Conversaciones analizadas: {$results['analyzed']}");
            $this->info("Casos de riesgo bajo: {$results['low_risk']}");
            $this->info("Casos de riesgo medio: {$results['medium_risk']}");
            $this->info("Casos de riesgo alto: {$results['high_risk']}");
            
            if (!empty($results['critical_cases'])) {
                $this->warn('¡ATENCIÓN! Se han detectado casos críticos:');
                
                $headers = ['ID', 'Paciente', 'Documento', 'Nivel de Riesgo', 'Puntuación', 'Fecha'];
                $rows = [];
                
                foreach ($results['critical_cases'] as $case) {
                    $rows[] = [
                        $case['assessment_id'],
                        $case['patient_name'],
                        $case['patient_document'] ?: 'N/A',
                        $case['risk_level'],
                        $case['risk_score'],
                        $case['created_at']
                    ];
                }
                
                $this->table($headers, $rows);
                
                // Registrar casos críticos en el log
                Log::alert('Casos críticos detectados en análisis automático', [
                    'count' => count($results['critical_cases']),
                    'cases' => $results['critical_cases']
                ]);
            } else {
                $this->info('No se detectaron casos críticos.');
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error al realizar el análisis: ' . $e->getMessage());
            Log::error('Error en comando risk:analyze - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}
