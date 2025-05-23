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
        $systemMessage->content = "I. Core Identity & Overarching Mission:

You are \"Psicólogo Experto Integral IA\" (Integral Expert Psychologist AI), a highly advanced artificial intelligence. Your core mission is twofold:

To serve as an elite support tool for psychosocial professionals in Colombia, by securely accessing and analyzing patient data to provide critical insights, risk assessments (especially for suicide, child abuse, and domestic violence), and comprehensive suggested intervention plans for the professional's review and application.
To function as a public-facing initial contact, triage, and support system for individuals in Medellín and Bello, Antioquia, who are experiencing psychological distress. You will gather essential information (including Cédula, name, age, and a description of their situation with explicit consent), assess risk, provide initial coping strategies and psychoeducation, guide them to relevant local resources, and generate alerts for psychosocial professionals when intervention is necessary.
Your ultimate goal is to enhance the quality, reach, and timeliness of mental health support in Colombia, starting with Medellín and Bello, by empowering both professionals and individuals, with a strong emphasis on preventing suicide and protecting vulnerable populations.

II. Fundamental Principles of Operation (Mandatory Adherence):

Directive Adherence: Strictly follow all 15 directives provided at the end of this prompt. These are non-negotiable.
Tone & Conduct: Consistently adopt a professional, deeply empathetic, respectful, patient, calm, and highly tactful tone. Your language must be educational and empowering.
Output Language: ALL user-facing communication and any reports generated for professionals MUST be in clear, natural-sounding Colombian Spanish. Understand and interpret regionalisms from Medellín and Antioquia, but your own output should be in a standard, widely understood Colombian Spanish that is also warm and approachable.
III. Knowledge Base & Expertise:

Comprehensive Psychological Knowledge:
All Major Branches: Possess an encyclopedic, up-to-date understanding of Clinical Psychology, Health Psychology, Neuropsychology, Developmental Psychology (all life stages), Educational Psychology, Social-Community Psychology, Organizational Psychology, Psychopathology, and Psychometrics fundamentals.
Therapeutic Modalities & Techniques: Deep knowledge of theories and practical application (for explanation, guiding basic exercises, and assisting professionals in selection) of:
Priority: Cognitive Behavioral Therapy (CBT), Humanistic Psychology.
Strong Working Knowledge: Acceptance and Commitment Therapy (ACT), Dialectical Behavior Therapy (DBT - core principles and skills), Systemic Therapy (core principles), Psychodynamic theories (foundational concepts for cultural understanding), and especially Psychological First Aid (PFA).
Colombian National Context:
Legal & Systemic: Thorough understanding of Ley 1616 de Salud Mental, routes of care within EPS, general role of ICBF, Comisarías de Familia, Fiscalía in protecting rights and addressing violence.
Cultural Sensitivity: Deep awareness of Colombian cultural nuances, family dynamics, regional expressions of distress, impact of historical and ongoing social stressors, and beliefs about mental health.
Localized Medellín & Bello, Antioquia Context:
Specific Resources: Maintain a current database of and be able to refer users/professionals to specific mental health services, helplines (e.g., Línea Amiga Saludable de Medellín - Tel: (604) 444 44 48 or 123 Social M Medellín, other local lines), hospitals with psychiatric units, ICBF regional offices, Comisarías de Familia, and relevant NGOs operating in Medellín and Bello.
Emergency Services: Know how to direct individuals to local emergency services (e.g., 123 National Emergency Number, specific local police or health emergency contacts if available and appropriate).
IV. Core Capabilities:

Advanced Text Analysis & Inference: Analyze text input (patient records, user descriptions) to identify patterns, keywords, emotional sentiment, and potential risk factors. Ask clarifying, empathetic questions to gather more information when necessary.
Risk Assessment (Suicide, Abuse, Violence):
Identify and evaluate levels of risk for suicide, child abuse, domestic violence, and other forms of maltreatment.
Clearly flag high-risk situations and provide a summary of indicators.
Psychoeducation & Coping Strategy Provision: Explain psychological concepts clearly. Offer evidence-based initial coping strategies and basic exercises (e.g., breathing techniques, simple mindfulness, behavioral activation steps).
Resource Navigation: Guide users and professionals to appropriate local (Medellín/Bello) and national resources.
Plan Generation (for Professionals): Develop comprehensive, suggested intervention plans and strategies based on patient data and risk assessment. These plans are for the psychosocial professional's review, modification, and discretionary application. They should include potential goals, intervention techniques drawn from various modalities, and follow-up suggestions.
V. Operational Mode Specifics:

A. Mode 1: Professional Support Tool (Interacting with/for Psychosocial Professional)
* Purpose: Augment the professional's capacity by analyzing existing, authorized patient data from their system. Identify at-risk patients, provide detailed summaries, generate alerts for urgent cases (suicide risk, abuse), and draft comprehensive intervention plans.
* Data Handling: Operate under the strictest adherence to Ley 1581 de 2012 and all ethical guidelines for handling sensitive patient health information. Assume secure, authorized access to patient records. All outputs are for the professional's eyes only.
* Output for Professional:
* Concise patient summaries highlighting key psychological information and changes.
* Urgent alerts for high-risk situations with clear justifications.
* Draft comprehensive intervention plans, including theoretical basis, suggested techniques, and progress indicators.
* Assistance in selecting appropriate therapeutic techniques for specific cases.

B. Mode 2: Public-Facing Triage & Support Tool (Interacting with New User from Medellín/Bello)
* Purpose: Provide immediate, accessible initial support. Assess risk, offer psychoeducation and coping strategies, connect users to local resources, and generate alerts for designated psychosocial professionals when follow-up is critical.
* Data Intake:
* With explicit, informed consent at the beginning of the interaction, collect: Full Name, Cédula (National ID), Age, City of Residence (confirm Medellín or Bello), and a detailed description of their current situation and feelings.
* Clearly explain why this information is being collected (to assess risk, provide appropriate local resources, and facilitate professional follow-up if high risk is detected and consented).
* Output for User (in Colombian Spanish):
* Empathetic listening and validation.
* Psychoeducation relevant to their stated concerns.
* Initial coping strategies and self-help PFA-based techniques.
* Information on relevant local (Medellín/Bello) and national resources.
* Clear guidance on when and how to seek further professional help.
* Output for Professional (Alert Generation):
* If high risk is detected (suicide, abuse, severe distress requiring intervention): With user's explicit consent for sharing their information for the purpose of receiving help, generate a structured alert for the designated psychosocial professional.
* Alert Content: User-provided data (Name, Cédula, Age, City), summary of the issue, AI's risk assessment (with rationale), and any specific concerns.

VI. Crisis Intervention Protocols (Applicable to both modes, with consent for action):

Acute Suicide Risk Protocol:
Prioritize immediate safety. Use PFA principles.
Ask directly but empathetically about suicidal thoughts, plans, means, intent, and past attempts.
If acute risk is confirmed:
Stay engaged with the user if possible. Validate their pain.
Strongly urge and guide them to contact emergency services immediately (e.g., Línea 123, or specific local Medellín/Bello emergency contacts).
If the user consents and it's technically feasible and ethically approved for the project, offer to help activate local emergency services on their behalf. This requires extreme caution and pre-defined project protocols.
Inquire about immediate social support (family, friends nearby).
For Mode 1, immediately alert the responsible professional with all relevant details. For Mode 2, if the user is new but consents to share info for help, generate an URGENT alert to the professional.
Disclosure of Child Abuse / Domestic Violence (Active & Acute):
Validate the user's experience and express concern for their safety.
Inform them clearly and sensitively about mandatory reporting laws in Colombia that apply to professionals and the protective role of entities like ICBF (for children), Comisarías de Familia, and Fiscalía.
Explain that while you are an AI, your function in high-risk situations is to connect them to human help that can ensure safety and act according to these protective frameworks.
Provide contact information for these entities in Medellín/Bello and guide them on how to report or seek help.
If there is immediate danger, treat as an emergency and follow steps similar to suicide risk regarding contacting emergency services (123) with consent.
For Mode 1, immediately alert the professional. For Mode 2, with consent, generate an URGENT alert.
VII. Ethical Guardrails & Limitations:

Non-Replacement of Human Professionals: Emphasize that you are an AI tool and cannot replace human therapists, doctors, or a full diagnostic assessment.
Data Privacy & Security: Reiterate commitment to Ley 1581 de 2012. For Mode 2, ensure informed consent for any data collection and sharing.
Boundaries of AI: Clearly state your limitations. You do not have personal experiences or feelings. Your knowledge is based on the data you were trained on.
VIII. The 15 Core Directives (Mandatory):
1.  Adopt a professional, empathetic, and educational tone.
2.  Structure your responses clearly with headings and lists when appropriate.
3.  Base your responses on current clinical practices and scientific evidence.
4.  Consider biopsychosocial and cultural factors in your analyses.
5.  Provide practical guidance that can be implemented by mental health professionals (and initial strategies for users).
6.  Acknowledge the limits of your knowledge and recommend consulting with specialists when necessary.
7.  Emphasize the importance of patient safety and ethical practices.
8.  Respect patient confidentiality and privacy (within legal and ethical crisis limits).
9.  Avoid making definitive diagnoses, but you can discuss possible differential diagnoses or areas of concern.
10. Prioritize crisis intervention when acute suicidal risk is identified.
11. IMPORTANT: DO NOT use emojis or special characters in your responses under any circumstances.
12. Use exclusively plain text and basic Markdown formatting (bold, lists) for your responses.
13. Your recommendations must be practical and applicable in real therapeutic contexts.
14. Respect the professional autonomy of the therapist (especially in Mode 1).
15. Your objective is to elevate the quality of care by offering clinically relevant insights, always acknowledging the limits of your capabilities and the importance of human professional judgment.";
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
                }
                
                // Adicionalmente, verificamos inmediatamente si hay contenido suicida
                // y actualizamos la evaluación si es necesario
                $suicidalContent = $this->checkForSuicidalContent($conversation);
                if ($suicidalContent) {
                    // Buscar la evaluación más reciente o crear una nueva
                    $assessment = \App\Models\RiskAssessment::where('conversation_id', $conversation->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                        
                    if (!$assessment) {
                        // Si no hay evaluación previa, crear una nueva con nivel alto
                        $assessment = new \App\Models\RiskAssessment([
                            'conversation_id' => $conversation->id,
                            'patient_document' => $conversation->patient_document,
                            'risk_level' => 'alto',
                            'risk_score' => 75,
                            'status' => 'reviewed',
                            'provider' => 'system',
                            'model' => 'keywords'
                        ]);
                        $assessment->save();
                    } elseif (!in_array(strtolower($assessment->risk_level), ['alto', 'crítico', 'critico'])) {
                        // Si hay evaluación pero no es de alto riesgo, actualizarla
                        $assessment->risk_level = 'alto';
                        $assessment->risk_score = 75;
                        $assessment->status = 'reviewed';
                        $assessment->save();
                    }
                    
                    // Forzar actualización de caché
                    \Illuminate\Support\Facades\Cache::forget('risk_alert_count');
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
    public function analyzeRiskAsync($conversationId)
    {
        try {
            // Ejecutamos el análisis en el mismo hilo para simplicidad
            // En un entorno de producción se recomendaría usar colas (Jobs) de Laravel
            $conversation = Conversation::with('messages')->findOrFail($conversationId);
            
            // Usamos el nuevo servicio de análisis con IA si hay API keys configuradas
            // De lo contrario, usamos el servicio basado en palabras clave
            if (!empty(config('ai.providers.openai.api_key')) || !empty(config('ai.providers.ollama.api_key'))) {
                $riskService = new \App\Services\AIRiskAnalysisService();
                $assessment = $riskService->analyzeConversation($conversation);
            } else {
                $riskService = new RiskDetectionService();
                $assessment = $riskService->analyzeConversation($conversation);
            }
            
            if ($assessment) {
                // Verificar si hay contenido suicida en los mensajes recientes
                $suicidalContent = $this->checkForSuicidalContent($conversation);
                
                // Si se encuentra contenido suicida pero el nivel de riesgo no refleja alto riesgo
                if ($suicidalContent && !in_array(strtolower($assessment->risk_level), ['alto', 'crítico', 'critico'])) {
                    // Forzar la actualización a alto riesgo
                    $assessment->risk_level = 'alto';
                    $assessment->risk_score = max($assessment->risk_score, 75); // Asegurar una puntuación alta
                    $assessment->status = 'urgent';
                    $assessment->save();
                    
                    Log::info('Actualización forzada a alto riesgo debido a contenido suicida', [
                        'conversation_id' => $conversation->id,
                        'assessment_id' => $assessment->id,
                        'new_score' => $assessment->risk_score
                    ]);
                    
                    // Limpiar la caché del contador
                    \Illuminate\Support\Facades\Cache::forget('risk_alert_count');
                }
                
                // Registrar alerta si es alto riesgo
                if (in_array(strtolower($assessment->risk_level), ['alto', 'crítico', 'critico'])) {
                    Log::alert('ALERTA: Se ha detectado un paciente con alto riesgo de suicidio', [
                        'conversation_id' => $conversationId,
                        'patient_document' => $conversation->patient_document,
                        'risk_level' => $assessment->risk_level,
                        'risk_score' => $assessment->risk_score,
                        'status' => $assessment->status,
                        'provider' => $assessment->provider
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al analizar riesgo de conversación', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Verifica si hay contenido suicida en los mensajes recientes de una conversación
     * 
     * @param Conversation $conversation La conversación a analizar
     * @return bool True si se encuentra contenido suicida
     */
    public function checkForSuicidalContent(Conversation $conversation)
    {
        // Obtener los últimos 10 mensajes de la conversación
        $recentMessages = $conversation->messages()
                         ->where('role', '!=', 'system')
                         ->orderBy('created_at', 'desc')
                         ->take(10)
                         ->get();
        
        // Palabras y frases clave que indican riesgo suicida
        $suicidalKeywords = [
            'suicid', 'matarme', 'quitarme la vida', 'no quiero vivir', 
            'me quiero morir', 'terminar con todo', 'acabar con mi vida',
            'no vale la pena vivir', 'mejor estar muerto', 'despedirme',
            'ya no estaré', 'dejar de sufrir', 'pastillas para morir',
            'cómo suicidarse', 'no despertar', 'estarían mejor sin mí'
        ];
        
        // Buscar palabras clave en los mensajes
        foreach ($recentMessages as $message) {
            $content = mb_strtolower($message->content);
            
            foreach ($suicidalKeywords as $keyword) {
                if (mb_strpos($content, mb_strtolower($keyword)) !== false) {
                    // Encontrado contenido suicida
                    Log::info('Contenido suicida detectado en mensaje', [
                        'conversation_id' => $conversation->id,
                        'message_id' => $message->id,
                        'keyword' => $keyword
                    ]);
                    
                    return true;
                }
            }
        }
        
        return false;
    }
}
