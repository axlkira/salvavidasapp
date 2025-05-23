<?php

// Cargamos las clases necesarias para la demostración
require_once __DIR__ . '/../app/Services/AI/AIProviderInterface.php';
require_once __DIR__ . '/../app/Services/AI/OllamaProvider.php';
require_once __DIR__ . '/../app/Services/AI/AIService.php';

// Definimos algunos valores de configuración en caso de que no podamos cargar el archivo de configuración
$config = [
    'default_provider' => 'ollama',
    'providers' => [
        'ollama' => [
            'base_url' => 'http://localhost:11434',
            'model' => 'llama3',
            'api_key' => '',
            'privacy_level' => 'alto'
        ]
    ]
];

// Intentamos cargar la configuración real si existe
if (file_exists(__DIR__ . '/../config/ai.php')) {
    $configFile = require __DIR__ . '/../config/ai.php';
    if (is_array($configFile)) {
        $config = $configFile;
    }
}

// Inicializamos el proveedor de Ollama
$ollamaProvider = new App\Services\AI\OllamaProvider(
    $config['providers']['ollama']['base_url'],
    $config['providers']['ollama']['model'],
    $config['providers']['ollama']['api_key']
);

// Sistema de prompts iniciales para orientar al modelo hacia la prevención de suicidio
$systemPrompt = "Eres un asistente especializado en salud mental que ayuda a profesionales en la evaluación y prevención del riesgo suicida. "
             . "Proporciona información basada en evidencia científica y ayuda a identificar factores de riesgo, señales de alerta y estrategias de intervención. "
             . "No reemplazas la evaluación clínica profesional, pero puedes ayudar a organizar la información "
             . "y sugerir preguntas o consideraciones relevantes para una evaluación completa. Prioriza siempre la seguridad del paciente.";

// Inicializamos la sesión para mantener el historial de mensajes
session_start();

// Inicializamos variables
$response = '';
$conversationHistory = [];
$userMessage = '';

// Si es la primera vez que se carga la página, inicializamos el historial con el mensaje del sistema
if (!isset($_SESSION['messages']) || !is_array($_SESSION['messages'])) {
    $_SESSION['messages'] = [
        [
            'role' => 'system',
            'content' => $systemPrompt
        ]
    ];
}

// Recuperamos el historial de mensajes de la sesión
$conversationHistory = $_SESSION['messages'];

// Si se envió un mensaje, lo procesamos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'])) {
        $userMessage = $_POST['message'];
        
        // Agregamos el mensaje del usuario al historial
        $conversationHistory[] = [
            'role' => 'user',
            'content' => $userMessage
        ];
        
        // Enviamos todos los mensajes al proveedor de IA
        try {
            $result = $ollamaProvider->chat($conversationHistory);
            if ($result['success']) {
                $response = $result['content'];
                // Agregamos la respuesta al historial
                $conversationHistory[] = [
                    'role' => 'assistant',
                    'content' => $response
                ];
            } else {
                $response = "Error: " . ($result['error'] ?? 'Desconocido');
            }
        } catch (Exception $e) {
            $response = "Error: " . $e->getMessage();
        }
        
        // Guardamos el historial actualizado en la sesión
        $_SESSION['messages'] = $conversationHistory;
    } elseif (isset($_POST['reset'])) {
        // Si se solicitó reiniciar la conversación
        $_SESSION['messages'] = [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ]
        ];
        $conversationHistory = $_SESSION['messages'];
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalvaVidas - Prueba de Chat</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .logo i {
            font-size: 2rem;
            margin-right: 10px;
            color: #3498db;
        }
        .logo h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        .chat-container {
            display: flex;
            height: calc(100vh - 180px);
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 20px;
        }
        .sidebar {
            width: 250px;
            background-color: #f1f2f6;
            padding: 20px;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin: 0;
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        .message {
            margin-bottom:.15px;
            display: flex;
            align-items: flex-start;
        }
        .message.user {
            justify-content: flex-end;
        }
        .message.assistant {
            justify-content: flex-start;
        }
        .message-bubble {
            max-width: 70%;
            padding: 12px 15px;
            border-radius: 18px;
            margin-bottom: 10px;
        }
        .user .message-bubble {
            background-color: #3498db;
            color: white;
            border-top-right-radius: 4px;
        }
        .assistant .message-bubble {
            background-color: #f1f2f6;
            color: #333;
            border-top-left-radius: 4px;
        }
        .chat-input {
            padding: 15px;
            border-top: 1px solid #ddd;
            display: flex;
        }
        .chat-input form {
            display: flex;
            width: 100%;
        }
        .chat-input input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 0.95rem;
            outline: none;
        }
        .chat-input button {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 50%;
            width: 46px;
            height: 46px;
            margin-left: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .provider-info {
            margin-bottom: 20px;
        }
        .provider-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .provider-model {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .provider-model i {
            color: #3498db;
            margin-right: 8px;
        }
        .provider-tag {
            display: inline-block;
            background-color: #e9f7fe;
            color: #3498db;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        .examples {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
        }
        
        .example-btn {
            text-align: left;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .example-btn:hover {
            background-color: #e9f7fe;
            border-color: #3498db;
        }
        
        .reset-btn {
            background-color: #f1f2f6;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #e74c3c;
            font-size: 0.9rem;
        }
        
        .reset-btn:hover {
            background-color: #fee9e7;
            border-color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-heart-pulse"></i>
                <h1>SalvaVidas - Prueba de Chat</h1>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="chat-container">
            <div class="sidebar">
                <div class="provider-info">
                    <div class="provider-title">Proveedor de IA</div>
                    <div class="provider-model">
                        <i class="fas fa-robot"></i>
                        <div>
                            <div><?php echo htmlspecialchars($config['providers']['ollama']['model']); ?></div>
                            <div class="provider-tag">Privacidad: Alta</div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <p>Esta es una prueba del componente de chat usando el proveedor Ollama.</p>
                    <p>Para que funcione correctamente, asegúrate de tener Ollama instalado y ejecutándose en tu equipo.</p>
                    
                    <div style="margin-top: 20px;">
                        <h3>Ejemplos de consultas</h3>
                        <div class="examples">
                            <button class="example-btn" data-text="Tengo un paciente que ha expresado pensamientos suicidas recientemente. ¿Qué preguntas clave debería incluir en mi evaluación inicial?">Evaluación inicial</button>
                            <button class="example-btn" data-text="Mi paciente tiene los siguientes factores de riesgo: depresión grave, intentos previos de suicidio, aislamiento social y abuso de alcohol. ¿Cómo evaluarías su nivel de riesgo?">Evaluar nivel de riesgo</button>
                            <button class="example-btn" data-text="¿Cuáles son las intervenciones más efectivas para un paciente adolescente con ideación suicida e historial de autolesiones?">Intervenciones efectivas</button>
                            <button class="example-btn" data-text="¿Qué señales de alarma indican un riesgo inminente de suicidio?">Señales de alarma</button>
                        </div>
                    </div>
                    
                    <form method="post" action="" style="margin-top: 20px;">
                        <button type="submit" name="reset" class="reset-btn">
                            <i class="fas fa-trash-alt"></i> Reiniciar conversación
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="chat-main">
                <div class="chat-header">
                    <h2 class="chat-title">Conversación de prueba</h2>
                </div>
                
                <div class="chat-messages">
                    <?php if (count($conversationHistory) <= 1): // Solo contiene el mensaje del sistema ?>
                    <div class="message assistant">
                        <div class="message-bubble">
                            Hola, soy el asistente de SalvaVidas. Estoy aquí para ayudarte con la evaluación y prevención del riesgo suicida. 
                            ¿En qué puedo ayudarte hoy?
                        </div>
                    </div>
                    <?php else: ?>
                        <?php 
                        // Mostramos todo el historial excepto el mensaje del sistema
                        for ($i = 1; $i < count($conversationHistory); $i++) {
                            $message = $conversationHistory[$i];
                            $class = $message['role'] === 'user' ? 'user' : 'assistant';
                            echo '<div class="message ' . $class . '">';
                            echo '<div class="message-bubble">';
                            echo nl2br(htmlspecialchars($message['content']));
                            echo '</div></div>';
                        }
                        ?>
                    <?php endif; ?>
                </div>
                
                <div class="chat-input">
                    <form method="post" action="">
                        <input type="text" name="message" placeholder="Escribe tu mensaje..." required>
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar los botones de ejemplo
            const exampleButtons = document.querySelectorAll('.example-btn');
            const inputField = document.querySelector('.chat-input input[name="message"]');
            
            exampleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const text = this.getAttribute('data-text');
                    inputField.value = text;
                    inputField.focus();
                });
            });
            
            // Scroll al final de los mensajes cuando se carga la página
            const chatMessages = document.querySelector('.chat-messages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    </script>
</body>
</html>
