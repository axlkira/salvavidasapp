<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PrincipalIntegrante;
use App\Models\Conversation;
use App\Models\Message;
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
        
        // Añadir mensaje inicial del sistema
        $systemMessage = new Message();
        $systemMessage->conversation_id = $conversation->id;
        $systemMessage->role = 'system';
        $systemMessage->content = "Eres un asistente especializado en salud mental que ayuda a profesionales en la evaluación y prevención del riesgo suicida. " .
                              "Proporciona información basada en evidencia científica y ayuda a identificar factores de riesgo, señales de alerta y estrategias de intervención. " .
                              "No reemplazas la evaluación clínica profesional, pero puedes ayudar a organizar la información " .
                              "y sugerir preguntas o consideraciones relevantes para una evaluación completa. Prioriza siempre la seguridad del paciente.";
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
