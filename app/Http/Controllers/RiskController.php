<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RiskAssessment;
use App\Models\Individual;

class RiskController extends Controller
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
     * Mostrar la página principal de evaluaciones de riesgo
     */
    public function index()
    {
        return view('risk.index');
    }
    
    /**
     * Mostrar la página de detalles de una evaluación específica
     */
    public function show($id)
    {
        $assessment = RiskAssessment::findOrFail($id);
        
        // Verificar que el usuario actual tiene permiso para ver esta evaluación
        if ($assessment->professional_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tiene permiso para ver esta evaluación');
        }
        
        return view('risk.show', ['assessmentId' => $id]);
    }
    
    /**
     * Mostrar la página para crear una nueva evaluación
     */
    public function create()
    {
        return view('risk.create');
    }
    
    /**
     * Mostrar la página de dashboard de riesgo
     */
    public function dashboard()
    {
        // Obtener estadísticas para el dashboard
        $stats = [
            'total' => RiskAssessment::count(),
            'high_risk' => RiskAssessment::whereIn('risk_level', ['alto', 'crítico', 'critico'])->count(),
            'pending' => RiskAssessment::where('status', 'pending')->count(),
            'recent' => RiskAssessment::with('patient')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
        ];
        
        return view('risk.dashboard', ['stats' => $stats]);
    }
    
    /**
     * Mostrar la página de evaluación de riesgo para un paciente específico
     */
    public function patient($document)
    {
        // Verificar que el paciente existe
        $patient = Individual::where('Documento', $document)->first();
        
        if (!$patient) {
            abort(404, 'Paciente no encontrado');
        }
        
        return view('risk.patient', ['patientDocument' => $document]);
    }
}
