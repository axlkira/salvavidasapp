<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PrincipalIntegrante;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Individual;
use App\Models\UsuarioProtocolo;
use App\Models\RiskAssessment;
use App\Services\AI\OllamaProvider;
use App\Services\RiskDetectionService;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
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
     * Mostrar la interfaz principal de chat con listado de conversaciones
     */
    public function index()
    {
        // Obtener conversaciones recientes para mostrar
        $conversations = Conversation::with(['messages' => function($query) {
            $query->orderBy('created_at', 'desc')->first();
        }])->orderBy('updated_at', 'desc')->take(10)->get();
        
        return view('chat.index', ['conversations' => $conversations]);
    }
    
    /**
     * Mostrar la página de una conversación específica
     */
    public function show($id)
    {
        // Obtener la conversación con todos sus mensajes
        $conversation = Conversation::with(['messages' => function($query) {
            $query->orderBy('created_at', 'asc');
        }])->findOrFail($id);
        
        // Obtener detalles del paciente e historias clínicas si está disponible
        $patient = null;
        $historiasClinicas = null;
        
        if ($conversation->patient_document) {
            // Obtener información del paciente
            $patient = PrincipalIntegrante::where('documento', $conversation->patient_document)->first();
            
            // Obtener historias clínicas del paciente
            if ($patient) {
                $historiasClinicas = Individual::where('Documento', $conversation->patient_document)
                                    ->orderBy('FechaInicio', 'desc')
                                    ->get();
                
                // Registrar información para debugging
                Log::info('Historias clínicas cargadas', [
                    'patient_document' => $conversation->patient_document,
                    'count' => $historiasClinicas->count()
                ]);
            }
        }
        
        return view('chat.show', [
            'conversation' => $conversation,
            'patient' => $patient,
            'historiasClinicas' => $historiasClinicas
        ]);
    }
    
    /**
     * Mostrar la página para buscar pacientes
     */
    public function patients()
    {
        return view('chat.patients');
    }
    
    /**
     * Buscar pacientes para iniciar una conversación
     */
    public function searchPatients(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query) || strlen($query) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'La búsqueda debe tener al menos 3 caracteres'
            ]);
        }
        
        $patients = PrincipalIntegrante::where('documento', 'like', '%' . $query . '%')
            ->orWhere('nombre', 'like', '%' . $query . '%')
            ->orWhere('apellidos', 'like', '%' . $query . '%')
            ->take(10)
            ->get();
            
        return response()->json([
            'success' => true,
            'patients' => $patients
        ]);
    }
    
    /**
     * Crear una nueva conversación
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'patient_document' => 'nullable|string',
            'title' => 'required|string|max:255',
        ]);
        
        // Crear nueva conversación
        $conversation = new Conversation();
        $conversation->professional_id = Auth::id() ?? 1; // ID del usuario actual o demo
        $conversation->patient_document = $validated['patient_document'] ?? null;
        $conversation->title = $validated['title'];
        $conversation->provider = 'ollama';
        $conversation->model = 'llama3';
        $conversation->save();
        
        // Añadir mensaje inicial del sistema con el prompt mejorado
        $systemMessage = new Message();
        $systemMessage->conversation_id = $conversation->id;
        $systemMessage->role = 'system';
        $systemMessage->content = "Eres un asistente psicológico de élite con conocimientos especializados en salud mental, particularmente en la evaluación y gestión del riesgo suicida. Tu función es ayudar a profesionales de la salud mental a analizar casos y proporcionar orientación basada en evidencia científica.

Sigue estas directrices al responder:

1. Adopta un tono profesional, empático y educativo.
2. Estructura tus respuestas de manera clara con encabezados y listas cuando sea apropiado.
3. Basa tus respuestas en prácticas clínicas actuales y evidencia científica.
4. Considera factores biopsicosociales y culturales en tus análisis.
5. Proporciona orientación práctica que pueda ser implementada por profesionales de la salud mental.
6. Reconoce los límites de tu conocimiento y recomienda consultar con especialistas cuando sea necesario.
7. Enfatiza la importancia de la seguridad del paciente y las prácticas éticas.
8. Respeta la confidencialidad y privacidad del paciente.
9. Evita hacer diagnósticos definitivos, pero puedes discutir posibles diagnósticos diferenciales.
10. Prioriza la intervención en crisis cuando se identifique riesgo suicida agudo.
11. IMPORTANTE: NO utilices emojis o caracteres especiales en tus respuestas bajo ninguna circunstancia.
12. Usa exclusivamente texto plano y formato Markdown básico (negritas, listas) para tus respuestas.
13. Tus recomendaciones deben ser prácticas y aplicables en contextos terapéuticos reales.
14. Respeta la autonomía profesional del terapeuta.
15. Tu objetivo es elevar la calidad de la atención ofreciendo insights clínicamente relevantes, reconociendo siempre los límites de tus capacidades y la importancia del juicio profesional humano.";
        $systemMessage->save();
        
        // Redirigir a la página de la conversación
        return redirect()->route('chat.show', ['id' => $conversation->id])
                         ->with('success', 'Conversación creada exitosamente');
    }
    
    /**
     * Enviar un mensaje en una conversación y obtener respuesta de la IA
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);
        
        try {
            // Obtener la conversación
            $conversation = Conversation::findOrFail($conversationId);
            
            // Guardar el mensaje del usuario
            $userMessage = new Message();
            $userMessage->conversation_id = $conversationId;
            $userMessage->role = 'user';
            $userMessage->content = $validated['message'];
            $userMessage->save();
            
            // Obtener todos los mensajes de la conversación para dar contexto a la IA
            $messages = Message::where('conversation_id', $conversationId)
                               ->orderBy('created_at', 'asc')
                               ->get()
                               ->map(function ($msg) {
                                   return [
                                       'role' => $msg->role,
                                       'content' => $msg->content
                                   ];
                               })
                               ->toArray();
            
            // Verificar si la conversación tiene un paciente asociado o si es una consulta general
            if ($conversation->patient_document) {
                // Obtener información del paciente y su historia clínica
                $patient = PrincipalIntegrante::where('documento', $conversation->patient_document)->first();
                $historiasClinicas = Individual::where('Documento', $conversation->patient_document)
                                             ->orderBy('FechaInicio', 'desc')
                                             ->first();
                
                // Si encontramos al paciente y tiene historias clínicas, añadir esta información al contexto
                if ($patient && $historiasClinicas) {
                    // Preparar un resumen de la información clínica para incluir en el contexto
                    $infoClinica = "";
                    // Usar el query completo para obtener información detallada del paciente y profesional
                    $historiasCompletas = \DB::select("select *, CONCAT_WS(' ',tp.nombre1,tp.nombre2,tp.apellido1,tp.apellido2) as Nombre_Usuario, 
                                            CONCAT_WS(' ',tu.nombre1,tu.nombre2,tu.apellido1,tu.apellido2) as Nombre_profesional 
                                            from familiam_buenvivir.t_individual i
                                            left join familiam_bdprotocoloservidor.t_usuarioprotocolo tu on i.Profesional = tu.documento 
                                            left join familiam_modulo_cif.t1_principalintegrantes tp on i.Documento = tp.documento
                                            where i.Documento = ?
                                            order by i.FechaInicio desc", [$patient->documento]);

                    $infoClinica = "INFORMACIÓN DEL PACIENTE:\n";
                    $infoClinica .= "Nombre: " . ($patient->nombre1 . ' ' . $patient->nombre2 . ' ' . $patient->apellido1 . ' ' . $patient->apellido2) . "\n";
                    $infoClinica .= "Documento: " . $patient->documento . "\n";
                    $infoClinica .= "Edad: " . ($patient->edad ?? 'No disponible') . "\n";
                    $infoClinica .= "Sexo: " . ($patient->sexo ?? 'No disponible') . "\n";
                    
                    // Agregar información de profesionales si está disponible
                    if (!empty($historiasCompletas)) {
                        if (isset($historiasCompletas[0]->Nombre_profesional)) {
                            $infoClinica .= "\nPROFESIONALES TRATANTES:\n";
                            $infoClinica .= "Último profesional: " . $historiasCompletas[0]->Nombre_profesional . " (" . date('d/m/Y', strtotime($historiasCompletas[0]->FechaInicio)) . ")\n";
                            
                            // Mostrar historial de profesionales (máximo 3 profesionales diferentes)
                            $profesionalesUnicos = [];
                            foreach ($historiasCompletas as $index => $historia) {
                                if ($index === 0) continue; // Saltamos el primero que ya lo mostramos
                                if (!empty($historia->Nombre_profesional) && !in_array($historia->Nombre_profesional, $profesionalesUnicos)) {
                                    $profesionalesUnicos[] = $historia->Nombre_profesional;
                                    $infoClinica .= "Profesional previo: " . $historia->Nombre_profesional . " (" . date('d/m/Y', strtotime($historia->FechaInicio)) . ")\n";
                                    if (count($profesionalesUnicos) >= 2) break; // Limitamos a 2 profesionales previos
                                }
                            }
                        }
                    }
                    
                    $infoClinica .= "\nHISTORIA CLÍNICA:\n";
                        
                        // Añadir campos relevantes de la historia clínica
                        if ($historiasClinicas->AntecedentesClinicosFisicosMentales) {
                            $infoClinica .= "Antecedentes Clínicos: " . $historiasClinicas->AntecedentesClinicosFisicosMentales . "\n";
                        }
                    if ($historiasClinicas->PersonalesPsicosociales) {
                        $infoClinica .= "Antecedentes Psicosociales: " . $historiasClinicas->PersonalesPsicosociales . "\n";
                    }
                    
                    if ($historiasClinicas->Familiares) {
                        $infoClinica .= "Antecedentes Familiares: " . $historiasClinicas->Familiares . "\n";
                    }
                    
                    if ($historiasClinicas->ProblematicaActual) {
                        $infoClinica .= "Problemática Actual: " . $historiasClinicas->ProblematicaActual . "\n";
                    }
                    
                    if ($historiasClinicas->ImprecionDiagnostica) {
                        $infoClinica .= "Impresión Diagnóstica: " . $historiasClinicas->ImprecionDiagnostica . "\n";
                    }
                    
                    // Añadir un mensaje del sistema con la información clínica
                    // Esto se inserta después del primer mensaje del sistema (que contiene las instrucciones generales)
                    array_splice($messages, 1, 0, [[
                        'role' => 'system',
                        'content' => $infoClinica
                    ]]);
                    
                    Log::info('Información clínica añadida al contexto', [
                        'patient_document' => $conversation->patient_document,
                        'has_historia_clinica' => !empty($historiasClinicas)
                    ]);
                }
            } else if (stripos($userMessage->content, 'riesgo') !== false || 
                       stripos($userMessage->content, 'suicidio') !== false || 
                       stripos($userMessage->content, 'pacientes') !== false) {
                // Si la conversación es general pero pregunta sobre riesgo o pacientes
                try {
                    // Establecer un límite de tiempo para esta operación
                    set_time_limit(30); // 30 segundos máximo
                    
                    // Añadir un mensaje simplificado si la consulta es sobre pacientes en riesgo
                    $infoRiesgo = "Puedo analizar las historias clínicas para identificar pacientes con posibles factores de riesgo suicida. "
                               . "Para hacer esto de manera eficiente, por favor, realiza una pregunta específica sobre qué tipo de riesgo "
                               . "te interesa evaluar, o si prefieres, inicia una nueva conversación con el número de documento de un paciente específico."; 
                    
                    array_splice($messages, 1, 0, [[
                        'role' => 'system',
                        'content' => $infoRiesgo
                    ]]);
                    
                    Log::info('Información sobre capacidad de análisis de riesgo añadida al contexto');
                } catch (\Exception $e) {
                    Log::error('Error al procesar información de riesgo', ['error' => $e->getMessage()]);
                }
            }
            
            // Conectar con el proveedor de IA (Ollama)
            $ollamaProvider = new OllamaProvider();
            $result = $ollamaProvider->chat($messages);
            
            // Guardar la respuesta de la IA
            if ($result['success']) {
                // Sanitizar el contenido antes de guardarlo
                $sanitizedContent = $this->sanitizeContent($result['content']);
                
                $aiMessage = new Message();
                $aiMessage->conversation_id = $conversationId;
                $aiMessage->role = 'assistant';
                $aiMessage->content = $sanitizedContent;
                $aiMessage->save();
                
                // Actualizar la fecha de última modificación de la conversación
                $conversation->touch();
                
                // Analizar riesgo en segundo plano en estos casos:
                // 1. Si la conversación tiene 3 o más mensajes (análisis estándar)
                // 2. Si el profesional está consultando específicamente sobre historia clínica o riesgo
                $shouldAnalyzeRisk = false;
                
                if ($conversation->messages()->count() >= 3) {
                    $shouldAnalyzeRisk = true;
                } else {
                    // Analizar el contenido del mensaje para ver si se consulta historia clínica o riesgo
                    $keywords = ['historia clínica', 'antecedentes', 'riesgo', 'suicida', 'ideación', 
                                'autolesiones', 'valoración', 'diagnóstico', 'evaluación',
                                'daño', 'peligro'];
                    
                    $content = strtolower($request->input('content'));
                    foreach ($keywords as $keyword) {
                        if (strpos($content, $keyword) !== false) {
                            $shouldAnalyzeRisk = true;
                            Log::info('Análisis de riesgo activado por palabra clave en consulta del profesional', [
                                'keyword' => $keyword,
                                'conversation_id' => $conversation->id
                            ]);
                            break;
                        }
                    }
                }
                
                if ($shouldAnalyzeRisk) {
                    try {
                        // Ejecutar el análisis de riesgo de manera no bloqueante
                        $this->analyzeRiskAsync($conversation->id);
                    } catch (\Exception $e) {
                        Log::error('Error al programar análisis de riesgo', [
                            'conversation_id' => $conversation->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $aiMessage->content
                ]);
            } else {
                Log::error('Error al comunicarse con Ollama', [
                    'error' => $result['error'] ?? 'Desconocido',
                    'conversation_id' => $conversationId
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Error al comunicarse con el asistente de IA: ' . ($result['error'] ?? 'Desconocido')
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Excepción al procesar mensaje', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al procesar tu mensaje: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sanitiza el contenido para evitar problemas de codificación/colación
     *
     * @param string $content Contenido a sanitizar
     * @return string Contenido sanitizado
     */
    private function sanitizeContent($content)
    {
        // Convertir caracteres especiales a entidades HTML
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        
        // Eliminar caracteres invisibles problemáticos
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content);
        
        // Eliminar pensamientos internos del modelo (texto en inglés antes de la respuesta en español)
        if (preg_match('/^Okay,\s+the\s+user.+?(?=\n\n|\. \n|[A-ZÁ-Ú][a-zá-ú])/s', $content, $matches)) {
            $content = trim(substr($content, strlen($matches[0])));
        }
        
        // Eliminar símbolos de estructura interna como ####
        $content = preg_replace('/(^|\n)\s*#{2,}\s*/', '$1', $content);
        
        // Eliminar separadores largos como ---
        $content = preg_replace('/---+/', '', $content);
        
        // Eliminar asteriscos redundantes
        $content = preg_replace('/\*{3,}/', '**', $content);
        
        // Limpiar espacios múltiples innecesarios
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);
        
        // Eliminar solamente emojis y otros caracteres problemáticos, pero preservar acentos y ñ
        $content = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $content);
        
        return $content;
    }
    
    /**
     * Obtiene una lista de pacientes con posible riesgo de suicidio basado en su historia clínica
     * 
     * @return string Texto con información de pacientes en riesgo
     */
    private function obtenerPacientesEnRiesgo()
    {
        // Establecer límite de tiempo de ejecución más alto para esta operación
        ini_set('max_execution_time', 60); // 60 segundos
        
        // Palabras clave que podrían indicar riesgo de suicidio
        $palabrasClave = [
            'suicid', 'autolesion', 'desesperanza', 'depresi', 'crisis', 
            'muerte', 'morir', 'acabar', 'sin salida', 'intento'
        ];
        
        // Obtener solo las historias clínicas más recientes y limitar la cantidad
        $historias = Individual::orderBy('FechaInicio', 'desc')
                     ->limit(100) // Limitar a 100 registros recientes
                     ->get();
        
        Log::info('Buscando pacientes en riesgo', ['cantidad_historias' => count($historias)]);
        
        $pacientesRiesgo = [];
        
        foreach ($historias as $historia) {
            $factoresRiesgo = [];
            $textosClinicos = [
                $historia->AntecedentesClinicosFisicosMentales,
                $historia->PersonalesPsicosociales,
                $historia->ProblematicaActual,
                $historia->ImprecionDiagnostica,
                $historia->Observaciones
            ];
            
            foreach ($textosClinicos as $texto) {
                if (empty($texto)) continue;
                
                foreach ($palabrasClave as $palabra) {
                    if (stripos($texto, $palabra) !== false) {
                        // Paciente con posible riesgo
                        $paciente = PrincipalIntegrante::where('documento', $historia->Documento)->first();
                        if ($paciente) {
                            $nombre = trim($paciente->nombre1 . ' ' . $paciente->nombre2 . ' ' . 
                                         $paciente->apellido1 . ' ' . $paciente->apellido2);
                            
                            $factorRiesgo = $palabra . ': ' . substr($texto, max(0, stripos($texto, $palabra) - 30), 60);
                            
                            if (!isset($pacientesRiesgo[$paciente->documento])) {
                                $pacientesRiesgo[$paciente->documento] = [
                                    'nombre' => $nombre,
                                    'documento' => $paciente->documento,
                                    'factores' => []
                                ];
                            }
                            
                            if (!in_array($factorRiesgo, $pacientesRiesgo[$paciente->documento]['factores'])) {
                                $pacientesRiesgo[$paciente->documento]['factores'][] = $factorRiesgo;
                            }
                        }
                        
                        break; // Si encontramos un término, no seguimos buscando más en este texto
                    }
                }
            }
        }
        
        // Limitar a los 5 pacientes con más factores de riesgo
        usort($pacientesRiesgo, function($a, $b) {
            return count($b['factores']) - count($a['factores']);
        });
        
        $pacientesRiesgo = array_slice($pacientesRiesgo, 0, 5);
        
        // Formatear respuesta
        if (empty($pacientesRiesgo)) {
            return "No se han encontrado pacientes con factores de riesgo de suicidio en la base de datos.";
        }
        
        $respuesta = "INFORMACIÓN DE PACIENTES CON POSIBLES FACTORES DE RIESGO SUICIDA:\n\n";
        
        foreach ($pacientesRiesgo as $paciente) {
            $respuesta .= "- Paciente: {$paciente['nombre']} (Documento: {$paciente['documento']})\n";
            $respuesta .= "  Factores de riesgo identificados: " . count($paciente['factores']) . "\n";
            $respuesta .= "  Ejemplos: \n";
            
            // Mostrar solo los primeros 3 factores para no sobrecargar
            $factoresMostrados = array_slice($paciente['factores'], 0, 3);
            foreach ($factoresMostrados as $factor) {
                $respuesta .= "    * {$factor}\n";
            }
            
            $respuesta .= "\n";
        }
        
        $respuesta .= "\nNOTA: Esta información se basa en un análisis automático de palabras clave ";
        $respuesta .= "en la historia clínica y debe ser verificada por un profesional. ";
        $respuesta .= "Puedes solicitar más detalles sobre un paciente específico iniciando una ";
        $respuesta .= "nueva conversación con su número de documento.";
        
        return $respuesta;
    }
    
    /**
     * Analiza el riesgo de una conversación en segundo plano
     *
     * @param int $conversationId ID de la conversación a analizar
     * @return void
     */
    protected function analyzeRiskAsync($conversationId)
    {
        try {
            // Ejecutamos el análisis en el mismo hilo para simplicidad
            // En un entorno de producción se recomendaría usar colas (Jobs) de Laravel
            $conversation = Conversation::with('messages')->findOrFail($conversationId);
            $riskService = new RiskDetectionService();
            $assessment = $riskService->analyzeConversation($conversation);
            
            if ($assessment && ($assessment->risk_level == 'alto' || $assessment->risk_level == 'crítico')) {
                Log::alert('ALERTA: Se ha detectado un paciente con alto riesgo de suicidio', [
                    'conversation_id' => $conversationId,
                    'patient_document' => $conversation->patient_document,
                    'risk_level' => $assessment->risk_level,
                    'risk_score' => $assessment->risk_score,
                ]);
                
                // Aquí se podría implementar un sistema de notificaciones
                // para alertar a los profesionales sobre el riesgo detectado
            }
        } catch (\Exception $e) {
            Log::error('Error al analizar riesgo de conversación', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
