<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PrincipalIntegrante;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Individual;
use App\Models\UsuarioProtocolo;
use App\Services\AI\OllamaProvider;
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
        $systemMessage->content = "Eres un asistente psicológico de élite con conocimientos especializados en salud mental, "
            . "intervención de crisis, prevención del suicidio y terapias basadas en evidencia. Tu función es proporcionar a "
            . "profesionales de la salud mental análisis precisos y estrategias de intervención efectivas basadas en las mejores "
            . "prácticas clínicas actuales.\n\n"
            . "Al analizar casos:\n"
            . "1. Integra perspectivas de múltiples escuelas terapéuticas (TCC, DBT, ACT, terapia familiar sistémica, psicoanálisis)\n"
            . "2. Prioriza la seguridad del paciente y la prevención del riesgo de autolesiones\n"
            . "3. Considera factores biológicos, psicológicos y sociales en tu análisis\n"
            . "4. Utiliza un lenguaje clínico preciso pero accesible\n"
            . "5. Basa tus recomendaciones en investigaciones recientes y guías clínicas validadas\n"
            . "6. Estructura tus respuestas de manera clara, con secciones bien definidas para facilitar su comprensión\n"
            . "7. Cuando identifiques factores de riesgo suicida, ofrece estrategias específicas de evaluación y contención\n\n"
            . "Tus recomendaciones deben ser:\n"
            . "- Personalizadas al caso específico del paciente\n"
            . "- Clínicamente fundamentadas y respaldadas por evidencia\n"
            . "- Prácticas y aplicables en contextos terapéuticos reales\n"
            . "- Respetuosas de la autonomía profesional del terapeuta\n\n"
            . "Tu objetivo es elevar la calidad de la atención ofreciendo insights clínicamente relevantes, "
            . "reconociendo siempre los límites de tus capacidades y la importancia del juicio profesional humano.";
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
            
            // Verificar si hay un paciente asociado a esta conversación
            if ($conversation->patient_document) {
                // Obtener información del paciente
                $patient = PrincipalIntegrante::where('documento', $conversation->patient_document)->first();
                
                // Obtener historias clínicas del paciente
                $historiasClinicas = Individual::where('Documento', $conversation->patient_document)
                                    ->orderBy('id', 'desc')
                                    ->take(1) // Solo la más reciente para no sobrecargar el contexto
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
            }
            
            // Conectar con el proveedor de IA (Ollama)
            $ollamaProvider = new OllamaProvider();
            $result = $ollamaProvider->chat($messages);
            
            // Guardar la respuesta de la IA
            if ($result['success']) {
                $aiMessage = new Message();
                $aiMessage->conversation_id = $conversationId;
                $aiMessage->role = 'assistant';
                $aiMessage->content = $result['content'];
                $aiMessage->save();
                
                // Actualizar la fecha de última modificación de la conversación
                $conversation->touch();
                
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
}
