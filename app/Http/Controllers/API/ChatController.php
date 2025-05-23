<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Individual;
use App\Models\PrincipalIntegrante;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
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
     * Obtener todas las conversaciones del usuario actual
     */
    public function getConversations(Request $request)
    {
        $conversations = Conversation::where('professional_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->with('patient')
            ->get();
            
        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }
    
    /**
     * Obtener una conversación específica con sus mensajes
     */
    public function getConversation(Request $request, $id)
    {
        $conversation = Conversation::where('id', $id)
            ->where('professional_id', Auth::id())
            ->with(['messages', 'patient'])
            ->first();
            
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversación no encontrada'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'conversation' => $conversation
        ]);
    }
    
    /**
     * Crear una nueva conversación
     */
    public function createConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'patient_document' => 'nullable|string|max:30',
            'provider' => 'nullable|string|max:30',
            'model' => 'nullable|string|max:50',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de conversación inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar que el paciente existe si se proporciona documento
        if ($request->patient_document) {
            $patient = PrincipalIntegrante::where('documento', $request->patient_document)->first();
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paciente no encontrado con el documento proporcionado'
                ], 404);
            }
        }
        
        $conversation = new Conversation;
        $conversation->professional_id = Auth::id();
        $conversation->patient_document = $request->patient_document;
        $conversation->title = $request->title ?? 'Nueva conversación';
        $conversation->provider = $request->provider ?? config('ai.default_provider');
        $conversation->model = $request->model ?? config('ai.providers.' . ($request->provider ?? config('ai.default_provider')) . '.model');
        $conversation->save();
        
        // Si se trata de una conversación con un paciente, añadir un mensaje del sistema
        if ($request->patient_document) {
            $patient = PrincipalIntegrante::where('documento', $request->patient_document)->first();
            $historias = Individual::where('Documento', $request->patient_document)->get();
            
            $systemMessage = "Esta es una conversación relacionada con el paciente {$patient->getNombreCompletoAttribute()}. ";
            
            if ($historias->count() > 0) {
                $systemMessage .= "El paciente tiene {$historias->count()} historias clínicas registradas. ";
            }
            
            $message = new Message;
            $message->conversation_id = $conversation->id;
            $message->role = 'system';
            $message->content = $systemMessage;
            $message->save();
        }
        
        return response()->json([
            'success' => true,
            'conversation' => $conversation->load('messages')
        ], 201);
    }
    
    /**
     * Enviar un mensaje y obtener respuesta
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string',
            'provider' => 'nullable|string|max:30',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de mensaje inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar que la conversación pertenece al usuario actual
        $conversation = Conversation::where('id', $request->conversation_id)
            ->where('professional_id', Auth::id())
            ->first();
            
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversación no encontrada o no autorizada'
            ], 404);
        }
        
        // Guardar el mensaje del usuario
        $userMessage = new Message;
        $userMessage->conversation_id = $conversation->id;
        $userMessage->role = 'user';
        $userMessage->content = $request->message;
        $userMessage->save();
        
        // Obtener todos los mensajes previos para contexto
        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'role' => $message->role,
                    'content' => $message->content
                ];
            })
            ->toArray();
            
        // Si hay un paciente asociado, añadir contexto adicional
        if ($conversation->patient_document) {
            // Buscar la historia clínica más reciente
            $latestHistory = Individual::where('Documento', $conversation->patient_document)
                ->orderBy('id', 'desc')
                ->first();
                
            if ($latestHistory) {
                // Añadir datos relevantes al contexto
                $patientContext = "Información del paciente:\n";
                $patientContext .= "- Problemática actual: " . ($latestHistory->ProblematicaActual ?? 'No disponible') . "\n";
                $patientContext .= "- Impresión diagnóstica: " . ($latestHistory->ImprecionDiagnostica ?? 'No disponible') . "\n";
                
                // Añadir contexto del paciente al primer mensaje del sistema o crear uno nuevo
                $systemMessage = null;
                foreach ($messages as $key => $message) {
                    if ($message['role'] === 'system') {
                        $systemMessage = $key;
                        break;
                    }
                }
                
                if ($systemMessage !== null) {
                    $messages[$systemMessage]['content'] .= "\n\n" . $patientContext;
                } else {
                    // Insertar al inicio
                    array_unshift($messages, [
                        'role' => 'system',
                        'content' => $patientContext
                    ]);
                }
            }
        }
        
        // Enviar a la IA para procesar
        $provider = $request->provider ?? $conversation->provider;
        $aiResponse = $this->aiService->chat($messages, $provider);
        
        if (!$aiResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar con IA: ' . $aiResponse['error']
            ], 500);
        }
        
        // Guardar la respuesta de la IA
        $assistantMessage = new Message;
        $assistantMessage->conversation_id = $conversation->id;
        $assistantMessage->role = 'assistant';
        $assistantMessage->content = $aiResponse['content'];
        $assistantMessage->save();
        
        // Actualizar la conversación
        $conversation->touch();
        
        return response()->json([
            'success' => true,
            'message' => $assistantMessage,
            'conversation' => $conversation->fresh(['messages'])
        ]);
    }
    
    /**
     * Eliminar una conversación
     */
    public function deleteConversation(Request $request, $id)
    {
        $conversation = Conversation::where('id', $id)
            ->where('professional_id', Auth::id())
            ->first();
            
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversación no encontrada o no autorizada'
            ], 404);
        }
        
        $conversation->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Conversación eliminada correctamente'
        ]);
    }
    
    /**
     * Obtener proveedores de IA disponibles
     */
    public function getProviders()
    {
        $providers = $this->aiService->getAvailableProviders();
        
        return response()->json([
            'success' => true,
            'providers' => $providers
        ]);
    }
}
