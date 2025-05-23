@extends('layouts.app')

@section('content')
<div class="container-fluid chat-container">
    <div class="row h-100">
        <!-- Panel lateral con información del paciente (si existe) -->
        <div class="col-md-3 sidebar">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Información
                    </h5>
                    <a href="{{ route('chat.index') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                <div class="card-body">
                    <h6 class="border-bottom pb-2 mb-3">Detalles de la conversación</h6>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-primary rounded-circle p-2 mr-2 text-white">
                                <i class="fas fa-comment"></i>
                            </div>
                            <strong>Título</strong>
                        </div>
                        <p>{{ $conversation->title }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-info rounded-circle p-2 mr-2 text-white">
                                <i class="fas fa-robot"></i>
                            </div>
                            <strong>Modelo IA</strong>
                        </div>
                        <p>{{ $conversation->model }} <span class="badge badge-light">{{ $conversation->provider }}</span></p>
                    </div>
                    
                    @if($patient)
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Paciente</h6>
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-success rounded-circle p-2 mr-2 text-white">
                                <i class="fas fa-user"></i>
                            </div>
                            <strong>Nombre</strong>
                        </div>
                        <p>{{ $patient->nombre1 ?? '' }} {{ $patient->nombre2 ?? '' }} {{ $patient->apellido1 ?? '' }} {{ $patient->apellido2 ?? '' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-secondary rounded-circle p-2 mr-2 text-white">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <strong>Documento</strong>
                        </div>
                        <p>{{ $patient->documento }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-info rounded-circle p-2 mr-2 text-white">
                                <i class="fas fa-birthday-cake"></i>
                            </div>
                            <strong>Edad</strong>
                        </div>
                        <p>{{ $patient->edad ?? 'No disponible' }}</p>
                    </div>
                    
                    @if(isset($historiasClinicas) && $historiasClinicas && $historiasClinicas->count() > 0)
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Histórico Clínico</h6>
                    <div class="accordion" id="historiaAccordion">
                        @foreach($historiasClinicas as $index => $historia)
                        <div class="accordion-item mb-2 border rounded">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                                    <small>{{ $historia->FechaInicio ?? 'Fecha no disponible' }}</small>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $index }}" data-bs-parent="#historiaAccordion">
                                <div class="accordion-body p-2">
                                    <div class="small-text">
                                        @if($historia->ProblematicaActual)
                                        <div class="mb-2">
                                            <strong>Problemática:</strong>
                                            <p class="text-muted small mb-1">{{ Str::limit($historia->ProblematicaActual, 150) }}</p>
                                        </div>
                                        @endif
                                        
                                        @if($historia->ImprecionDiagnostica)
                                        <div class="mb-2">
                                            <strong>Impresión Diagnóstica:</strong>
                                            <p class="text-muted small mb-1">{{ Str::limit($historia->ImprecionDiagnostica, 150) }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    @else
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle"></i> Esta conversación no está asociada a un paciente específico.
                    </div>
                    @endif
                    
                    <div class="mt-4 pt-2 border-top">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Creada: {{ $conversation->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Área principal de chat -->
        <div class="col-md-9 main-chat">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h4 class="mb-0">{{ $conversation->title }}</h4>
                </div>
                <div class="card-body messages-container" id="messagesContainer">
                    @foreach($conversation->messages as $message)
                        @if($message->role != 'system')
                            <div class="message-wrapper {{ $message->role == 'user' ? 'user-message' : 'assistant-message' }}">
                                <div class="message-bubble">
                                    <div class="message-info">
                                        <span class="message-sender">
                                            @if($message->role == 'user')
                                                <i class="fas fa-user-md"></i> Profesional
                                            @else
                                                <i class="fas fa-robot"></i> SalvaVidas IA
                                            @endif
                                        </span>
                                        <span class="message-time">
                                            {{ $message->created_at->format('H:i') }}
                                        </span>
                                    </div>
                                    <div class="message-content">
                                        @if($message->role == 'assistant')
                                            {!! preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', nl2br(e($message->content))) !!}
                                        @else
                                            {!! nl2br(e($message->content)) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                
                <div class="card-footer bg-white">
                    <form id="messageForm" class="message-form">
                        @csrf
                        <div class="input-group">
                            <textarea 
                                class="form-control" 
                                id="messageInput" 
                                rows="2" 
                                placeholder="Escribe tu mensaje aquí..."
                                required
                            ></textarea>
                            <div class="input-group-append">
                                <button 
                                    type="submit" 
                                    class="btn btn-primary" 
                                    id="sendMessageBtn"
                                >
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="typing-indicator d-none" id="typingIndicator">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .chat-container {
        height: calc(100vh - 80px);
        padding-top: 15px;
        padding-bottom: 15px;
    }
    
    .sidebar .card-body {
        overflow-y: auto;
    }
    
    .messages-container {
        display: flex;
        flex-direction: column;
        gap: 10px;
        overflow-y: auto;
        padding: 20px;
        height: calc(100% - 140px);
    }
    
    .message-wrapper {
        display: flex;
        margin-bottom: 15px;
    }
    
    .user-message {
        justify-content: flex-end;
    }
    
    .assistant-message {
        justify-content: flex-start;
    }
    
    .message-bubble {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 18px;
    }
    
    .user-message .message-bubble {
        background-color: #007bff;
        color: white;
        border-top-right-radius: 5px;
    }
    
    .assistant-message .message-bubble {
        background-color: #f1f1f1;
        color: #333;
        border-top-left-radius: 5px;
    }
    
    .message-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        margin-bottom: 5px;
    }
    
    .user-message .message-info {
        color: rgba(255, 255, 255, 0.8);
    }
    
    .assistant-message .message-info {
        color: #666;
    }
    
    .message-content {
        word-break: break-word;
        line-height: 1.4;
    }
    
    .message-form {
        position: relative;
    }
    
    .card-footer {
        padding: 15px;
        border-top: 1px solid rgba(0,0,0,.125);
    }
    
    #messageInput {
        resize: none;
        padding-right: 50px;
        border-radius: 20px;
    }
    
    #sendMessageBtn {
        border-radius: 50%;
        width: 40px;
        height: 40px;
        padding: 0;
        margin-left: 10px;
    }
    
    .typing-indicator {
        display: flex;
        align-items: center;
        margin-top: 10px;
    }
    
    .typing-indicator .dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 3px;
        background: #007bff;
        animation: typing 1.5s infinite ease-in-out;
    }
    
    .typing-indicator .dot:nth-child(1) {
        animation-delay: 0s;
    }
    
    .typing-indicator .dot:nth-child(2) {
        animation-delay: 0.2s;
    }
    
    .typing-indicator .dot:nth-child(3) {
        animation-delay: 0.4s;
    }
    
    @keyframes typing {
        0% {
            transform: translateY(0px);
            background-color: rgba(0, 123, 255, 0.7);
        }
        50% {
            transform: translateY(-5px);
            background-color: rgba(0, 123, 255, 1);
        }
        100% {
            transform: translateY(0px);
            background-color: rgba(0, 123, 255, 0.7);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Scroll al final de los mensajes
        const messagesContainer = document.getElementById('messagesContainer');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Manejar el envío de mensajes
        $('#messageForm').on('submit', function(e) {
            e.preventDefault();
            
            const messageText = $('#messageInput').val().trim();
            if (!messageText) return;
            
            // Desactivar el botón de envío y mostrar indicador de carga
            $('#sendMessageBtn').prop('disabled', true);
            $('#messageInput').val('').prop('disabled', true);
            
            // Agregar mensaje del usuario a la UI
            appendMessage('user', messageText);
            
            // Mostrar indicador de "escribiendo..."
            $('#typingIndicator').removeClass('d-none');
            
            // Enviar mensaje a la IA
            $.ajax({
                url: "{{ route('chat.send-message', ['id' => $conversation->id]) }}",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    message: messageText
                },
                success: function(response) {
                    // Ocultar indicador de "escribiendo..."
                    $('#typingIndicator').addClass('d-none');
                    
                    // Agregar respuesta de la IA a la UI
                    if (response.success) {
                        appendMessage('assistant', response.message);
                    } else {
                        showError(response.error || 'Error al procesar tu mensaje.');
                    }
                    
                    // Reactivar el formulario
                    $('#sendMessageBtn').prop('disabled', false);
                    $('#messageInput').prop('disabled', false).focus();
                },
                error: function(xhr) {
                    // Ocultar indicador de "escribiendo..."
                    $('#typingIndicator').addClass('d-none');
                    
                    // Mostrar error
                    let errorMessage = 'Error de conexión al procesar tu mensaje.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    showError(errorMessage);
                    
                    // Reactivar el formulario
                    $('#sendMessageBtn').prop('disabled', false);
                    $('#messageInput').prop('disabled', false).focus();
                }
            });
        });
        
        // Función para agregar un nuevo mensaje a la UI
        function appendMessage(role, content) {
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                              now.getMinutes().toString().padStart(2, '0');
            
            const senderName = role === 'user' ? 
                '<i class="fas fa-user-md"></i> Profesional' : 
                '<i class="fas fa-robot"></i> SalvaVidas IA';
            
            const messageHTML = `
                <div class="message-wrapper ${role}-message">
                    <div class="message-bubble">
                        <div class="message-info">
                            <span class="message-sender">${senderName}</span>
                            <span class="message-time">${timeString}</span>
                        </div>
                        <div class="message-content">${role === 'assistant' ? content.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>') : content.replace(/\n/g, '<br>')}</div>
                    </div>
                </div>
            `;
            
            $('#messagesContainer').append(messageHTML);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Función para mostrar un error
        function showError(message) {
            const errorHTML = `
                <div class="alert alert-danger mx-auto my-2" style="max-width: 80%;">
                    <i class="fas fa-exclamation-triangle"></i> ${message}
                </div>
            `;
            
            $('#messagesContainer').append(errorHTML);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Ajustar altura del textarea al escribir
        $('#messageInput').on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Enviar con Ctrl+Enter
        $('#messageInput').on('keydown', function(e) {
            if (e.ctrlKey && e.keyCode === 13) {
                $('#messageForm').submit();
                e.preventDefault();
            }
        });
    });
</script>
@endpush
