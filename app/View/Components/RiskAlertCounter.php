<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\RiskAssessment;
use Illuminate\Support\Facades\Cache;

class RiskAlertCounter extends Component
{
    /**
     * Número de alertas de riesgo críticas activas.
     *
     * @var int
     */
    public $count;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Utilizamos caché con un tiempo más corto para ver actualizaciones rápidamente
        // pero manteniendo algo de eficiencia
        $this->count = Cache::remember('risk_alert_count', 15, function () {
            // Buscar evaluaciones con alto nivel de riesgo independientemente del estado
            $count = RiskAssessment::where(function($query) {
                    $query->where('risk_level', 'alto')
                          ->orWhere('risk_level', 'crítico')
                          ->orWhere('risk_level', 'critico');
                })
                ->count();
                
            // Si hay conteo, registramos un log para facilitar la depuración
            if ($count > 0) {
                \Illuminate\Support\Facades\Log::info("Contador de alertas de riesgo: {$count} alertas activas");
            }
            
            return $count;
        });
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.risk-alert-counter');
    }
}
