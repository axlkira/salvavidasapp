@extends('layouts.app')

@section('title', 'Detalle de Evaluación de Riesgo - SalvaVidas')

@section('page-title', 'Detalle de Evaluación de Riesgo')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Evaluación #{{ $assessment->id }}</h2>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                    <a href="#" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información general -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Fecha de Evaluación:</th>
                            <td>{{ $assessment->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Paciente:</th>
                            <td>{{ $patientName }}</td>
                        </tr>
                        <tr>
                            <th>Documento:</th>
                            <td>{{ $assessment->patient_document ?: 'No disponible' }}</td>
                        </tr>
                        <tr>
                            <th>Profesional:</th>
                            <td>{{ $professionalName }}</td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                        @if($assessment->reviewed_at)
                        <tr>
                            <th>Revisado por:</th>
                            <td>{{ $reviewerName }}</td>
                        </tr>
                        <tr>
                            <th>Fecha de revisión:</th>
                            <td>{{ $assessment->reviewed_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Resultado de la evaluación -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header {{ $riskHeaderClass }} text-white">
                    <h5 class="mb-0">Nivel de Riesgo: {{ ucfirst($assessment->risk_level) }}</h5>
                </div>
                <div class="card-body">
                    <div class="risk-score-container text-center mb-4">
                        <div class="risk-score-circle {{ $riskCircleClass }}">
                            <span>{{ number_format($assessment->risk_score, 1) }}</span>
                        </div>
                        <p class="mt-2">Puntuación de riesgo</p>
                    </div>
                    
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ min($assessment->risk_score * 3.33, 33) }}%" 
                             aria-valuenow="{{ min($assessment->risk_score * 3.33, 33) }}" 
                             aria-valuemin="0" aria-valuemax="100">Bajo</div>
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: {{ min(max(($assessment->risk_score - 10) * 3.33, 0), 33) }}%" 
                             aria-valuenow="{{ min(max(($assessment->risk_score - 10) * 3.33, 0), 33) }}" 
                             aria-valuemin="0" aria-valuemax="100">Medio</div>
                        <div class="progress-bar bg-danger" role="progressbar" 
                             style="width: {{ min(max(($assessment->risk_score - 20) * 3.33, 0), 34) }}%" 
                             aria-valuenow="{{ min(max(($assessment->risk_score - 20) * 3.33, 0), 34) }}" 
                             aria-valuemin="0" aria-valuemax="100">Alto</div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small>0</small>
                        <small>10</small>
                        <small>20</small>
                        <small>30</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Factores de riesgo -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Factores de Riesgo Identificados</h5>
                </div>
                <div class="card-body">
                    @if(count($riskFactors) > 0)
                        <ul class="list-group">
                            @foreach($riskFactors as $factor)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $factor->description }}
                                    <span class="badge bg-danger rounded-pill">{{ $factor->weight }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-muted">No se han identificado factores de riesgo específicos.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Señales de advertencia -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Señales de Advertencia</h5>
                </div>
                <div class="card-body">
                    @if(count($warningSigns) > 0)
                        <ul class="list-group">
                            @foreach($warningSigns as $sign)
                                <li class="list-group-item">
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                    {{ $sign->description }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-muted">No se han identificado señales de advertencia específicas.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de conversación relevante -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Fragmentos relevantes de la conversación</h5>
                </div>
                <div class="card-body">
                    @if($conversation && count($relevantMessages) > 0)
                        <div class="conversation-highlights p-3">
                            @foreach($relevantMessages as $message)
                                <div class="highlight-item mb-3">
                                    <div class="highlight-header">
                                        <strong>{{ $message->role == 'user' ? 'Profesional' : 'Asistente IA' }}</strong>
                                        <small class="text-muted">{{ $message->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <div class="highlight-content p-3 border rounded">
                                        @php
                                            $content = e($message->content);
                                            // Procesar negritas
                                            $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
                                            // Procesar cursivas
                                            $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
                                            // Procesar listas con guiones
                                            $content = preg_replace('/^- (.*)$/m', '• $1', $content);
                                            // Procesar saltos de línea
                                            $content = nl2br($content);
                                        @endphp
                                        {!! $content !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted">No hay mensajes relevantes disponibles.</p>
                    @endif

                    @if($conversation)
                        <div class="text-center mt-3">
                            <a href="{{ route('chat.show', $conversation->id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-comments"></i> Ver conversación completa
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones para profesionales -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('risk-assessment.update-status', $assessment->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="input-group mb-3">
                                    <select name="status" class="form-select">
                                        <option value="pending" {{ $assessment->status == 'pending' ? 'selected' : '' }}>Pendiente de revisión</option>
                                        <option value="reviewed" {{ $assessment->status == 'reviewed' ? 'selected' : '' }}>Revisado</option>
                                        <option value="archived" {{ $assessment->status == 'archived' ? 'selected' : '' }}>Archivado</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#interventionModal">
                                    <i class="fas fa-file-medical"></i> Crear Guía de Intervención
                                </button>
                                <form action="{{ route('risk-assessment.mark-critical', $assessment->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Está seguro de marcar esta evaluación como crítica?')">
                                        <i class="fas fa-exclamation-circle"></i> Marcar como Crítico
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Guía de Intervención -->
<div class="modal fade" id="interventionModal" tabindex="-1" aria-labelledby="interventionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" data-date="{{ now()->format('d/m/Y') }}">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="interventionModalLabel">Guía de Intervención</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Cabecera visible solo al imprimir -->
                <div class="print-header" style="display: none;">
                    <img src="{{ asset('img/logo.png') }}" alt="SalvaVidas Logo" class="print-logo">
                    <h1 class="print-title">Guía de Intervención - {{ ucfirst($assessment->risk_level) }}</h1>
                </div>
                
                <!-- Datos del paciente/profesional para impresión -->
                <div class="print-info mb-4" style="display: none;">
                    <div class="row">
                        <div class="col-6">
                            <p><strong>Paciente:</strong> {{ $patientName }}</p>
                            <p><strong>Fecha de evaluación:</strong> {{ $assessment->created_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-6">
                            <p><strong>Nivel de riesgo:</strong> {{ ucfirst($assessment->risk_level) }}</p>
                            <p><strong>Profesional:</strong> {{ $professionalName }}</p>
                        </div>
                    </div>
                    <hr>
                </div>
                
                <!-- Pie de página visible solo al imprimir -->
                <div class="print-footer" style="display: none;">
                    <div class="print-footer-left">
                        SalvaVidas - Guía de Intervención
                    </div>
                    <div class="print-footer-right">
                        Página <span class="page-number"></span> | {{ now()->format('d/m/Y') }}
                    </div>
                </div>
                
                <div class="accordion" id="interventionAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                1. Evaluación de la gravedad
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="heading1" data-bs-parent="#interventionAccordion">
                            <div class="accordion-body">
                                <p>Basado en la evaluación realizada, este caso presenta un nivel de riesgo <strong>{{ ucfirst($assessment->risk_level) }}</strong>.</p>
                                <ul class="list-group mb-3">
                                    @if($assessment->risk_level == 'bajo')
                                        <li class="list-group-item">Realizar seguimiento regular</li>
                                        <li class="list-group-item">Proporcionar recursos de apoyo</li>
                                        <li class="list-group-item">Programar próxima evaluación en 30 días</li>
                                    @elseif($assessment->risk_level == 'medio')
                                        <li class="list-group-item">Programar sesiones de seguimiento semanales</li>
                                        <li class="list-group-item">Establecer un plan de seguridad básico</li>
                                        <li class="list-group-item">Considerar evaluación psiquiátrica</li>
                                    @elseif($assessment->risk_level == 'alto')
                                        <li class="list-group-item">Derivación urgente a servicios especializados</li>
                                        <li class="list-group-item">Contactar a familiares/cuidadores</li>
                                        <li class="list-group-item">Implementar plan de seguridad intensivo</li>
                                        <li class="list-group-item">Seguimiento frecuente (cada 2-3 días)</li>
                                    @else
                                        <li class="list-group-item list-group-item-danger">¡REQUIERE ATENCIÓN INMEDIATA!</li>
                                        <li class="list-group-item">Considerar hospitalización</li>
                                        <li class="list-group-item">No dejar solo/a al paciente</li>
                                        <li class="list-group-item">Activar protocolo de emergencia</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                2. Factores de riesgo identificados
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#interventionAccordion">
                            <div class="accordion-body">
                                @if(count($riskFactors) > 0)
                                    <ul class="list-group">
                                        @foreach($riskFactors as $factor)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                {{ $factor->description }}
                                                <span class="badge bg-danger rounded-pill">{{ $factor->weight }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-center text-muted">No se han identificado factores de riesgo específicos.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                3. Recomendaciones específicas
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#interventionAccordion">
                            <div class="accordion-body">
                                <h6>Intervenciones recomendadas:</h6>
                                <div class="mb-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">Establecer un plan de seguridad personalizado</li>
                                        <li class="list-group-item">Aplicar técnicas de entrevista motivacional</li>
                                        <li class="list-group-item">Involucrar a la red de apoyo (familiares, amigos)</li>
                                        <li class="list-group-item">Proporcionar recursos de crisis (líneas de ayuda)</li>
                                        <li class="list-group-item">Programar seguimiento estructurado</li>
                                    </ul>
                                </div>
                                
                                <h6 class="mt-4">Recursos adicionales:</h6>
                                <div class="list-group">
                                    <a href="#" class="list-group-item list-group-item-action" target="_blank">Guía de seguridad para pacientes con riesgo suicida</a>
                                    <a href="#" class="list-group-item list-group-item-action" target="_blank">Protocolo de evaluación y seguimiento</a>
                                    <a href="#" class="list-group-item list-group-item-action" target="_blank">Recursos comunitarios de apoyo</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="printGuide()"><i class="fas fa-print"></i> Imprimir guía</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Estilos generales */
    .risk-score-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        color: white;
        font-size: 24px;
        font-weight: bold;
    }
    
    .risk-low {
        background-color: #28a745;
    }
    
    .risk-medium {
        background-color: #ffc107;
        color: #212529;
    }
    
    .risk-high {
        background-color: #dc3545;
    }
    
    .risk-critical {
        background-color: #6a1a21;
    }
    
    .conversation-highlights {
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        background-color: #f8f9fa;
    }
    
    .highlight-item {
        background-color: #ffffff;
        border-left: 4px solid #007bff;
    }
    
    .highlight-header {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        background-color: #f0f7ff;
    }
    
    .highlight-content {
        background-color: #ffffff;
        line-height: 1.6;
        white-space: pre-line;
    }
    
    .chat-message.user .chat-bubble {
        background-color: #007bff;
        color: white;
        border-bottom-right-radius: 0;
    }
    
    .chat-message.assistant .chat-bubble {
        background-color: #f1f1f1;
        border-bottom-left-radius: 0;
    }
    
    .chat-time {
        font-size: 0.75rem;
        text-align: right;
        margin-top: 5px;
        opacity: 0.7;
    }
    
    @media print {
        .btn, .card-header {
            background-color: #f8f9fa !important;
            color: #212529 !important;
            border-color: #dee2e6 !important;
        }
        
        .risk-score-circle {
            border: 2px solid #000;
        }
        
        .progress-bar {
            border: 1px solid #dee2e6;
        }
    }
    /* Estilos para impresión - Diseño profesional */
    @media print {
        @page {
            size: A4;
            margin: 0.5cm 1cm 1.5cm 1cm; /* Aumentado el margen inferior para el pie de página */
        }
        
        body * {
            visibility: hidden;
        }
        
        /* Mostrar solo el modal y sus contenidos */
        #interventionModal, #interventionModal * {
            visibility: visible;
        }
        
        #interventionModal {
            position: absolute;
            left: 50%;
            top: 0;
            transform: translateX(-50%);
            width: 21cm; /* Ancho estándar de A4 */
            max-width: 90%;
            height: auto; /* Altura automática para no cortar contenido */
            background-color: white;
            box-shadow: none;
            border: none;
            margin: 0 auto;
            padding: 0;
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        
        /* Ajustes para el diálogo modal */
        .modal-dialog {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
        }
        
        .modal-content {
            border: none;
            box-shadow: none;
            min-height: 100vh;
            position: relative;
            padding-bottom: 50px; /* Espacio para el pie de página */
        }
        
        /* Ocultar elementos no necesarios para impresión */
        .modal-footer, .btn-close, .modal-header {
            display: none !important;
        }
        
        /* Cabecera con color institucional */
        .print-header {
            display: flex !important;
            align-items: center;
            background-color: #0C6EFD; /* Azul institucional */
            color: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .print-logo {
            display: block !important;
            height: 50px;
            margin-right: 20px;
            filter: brightness(0) invert(1); /* Logo blanco */
        }
        
        .print-title {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin: 0;
            letter-spacing: 0.5px;
        }
        
        /* Datos del paciente con diseño mejorado */
        .print-info {
            display: block !important;
            background-color: #f9f9f9;
            border-left: 4px solid #000; /* Negro */
            padding: 15px 20px;
            margin: 0 0 25px 0;
            border-radius: 0 4px 4px 0;
        }
        
        .print-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .print-info strong {
            color: #000; /* Negro */
        }
        
        /* Títulos de secciones */
        .accordion-button {
            background: none !important;
            color: #000 !important; /* Negro */
            font-weight: bold;
            font-size: 18px;
            padding: 15px 0 10px 0;
            margin-bottom: 5px;
            border-bottom: 2px solid #000;
        }
        
        .accordion-button::after {
            display: none;
        }
        
        /* Contenido de acordeones */
        .accordion-collapse {
            display: block !important;
        }
        
        .accordion-body {
            padding: 15px 0 25px 0;
            page-break-inside: avoid;
        }
        
        /* Elementos de lista mejorados */
        .list-group {
            margin-bottom: 20px;
        }
        
        .list-group-item {
            border: none;
            border-bottom: 1px solid #f0f0f0;
            padding: 10px 5px;
            position: relative;
            margin-left: 15px;
        }
        
        .list-group-item::before {
            content: '\2022'; /* Bullet point */
            color: #000;
            font-weight: bold;
            position: absolute;
            left: -15px;
        }
        
        /* Marcadores para niveles de riesgo */
        .list-group-item.list-group-item-danger {
            background-color: #ffebee;
            color: #c62828;
            font-weight: bold;
            border-radius: 4px;
            border-left: 4px solid #c62828;
            padding-left: 10px;
        }
        
        .list-group-item.list-group-item-danger::before {
            content: '\26A0'; /* Símbolo de advertencia */
            color: #c62828;
        }
        
        /* Estilo para badges */
        .badge.bg-danger {
            background-color: #c62828 !important;
            padding: 5px 8px;
        }
        
        /* Encabezados de secciones */
        h6 {
            color: #000;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 8px;
            margin-top: 20px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        /* Pie de página */
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex !important;
            justify-content: space-between;
            padding: 10px 20px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            background-color: white;
        }
        
        .print-footer-left {
            text-align: left;
        }
        
        .print-footer-right {
            text-align: right;
        }
        
        .print-footer img {
            height: 20px;
            vertical-align: middle;
        }
        
        /* Ajustes para evitar cortes en páginas */
        .accordion-item {
            page-break-inside: avoid;
        }
        
        /* Asegurar que las imágenes no se deformen */
        img {
            max-width: 100% !important;
            height: auto !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function printGuide() {
        // Mostrar elementos ocultos para impresión
        document.querySelector('.print-header').style.display = 'flex';
        document.querySelector('.print-info').style.display = 'block';
        document.querySelector('.print-footer').style.display = 'flex';
        
        // Abrir todos los acordeones para la impresión
        const accordionItems = document.querySelectorAll('.accordion-collapse');
        accordionItems.forEach(item => {
            item.classList.add('show');
        });
        
        // Resolver problema común de Bootstrap donde los acordeones pueden tener altura 0
        document.querySelectorAll('.accordion-body').forEach(body => {
            body.style.display = 'block';
            body.style.height = 'auto';
            body.style.overflow = 'visible';
        });
        
        // Preparar modal para impresión
        const modalDialog = document.querySelector('.modal-dialog');
        const modalContent = document.querySelector('.modal-content');
        
        modalDialog.style.maxWidth = '100%';
        modalDialog.style.width = '100%';
        modalDialog.style.margin = '0';
        modalContent.style.border = 'none';
        
        // Asegurar que todos los elementos de la lista tengan viñetas
        document.querySelectorAll('.list-group-item').forEach(item => {
            if (!item.classList.contains('list-group-item-action')) {
                item.style.marginLeft = '15px';
                item.style.position = 'relative';
            }
        });
        
        // Esperar a que se apliquen los cambios
        setTimeout(() => {
            // Agregar numeración de páginas mediante CSS counters para impresión
            const style = document.createElement('style');
            style.id = 'print-page-numbers';
            style.innerHTML = `
                @media print {
                    .print-footer .page-number:after {
                        content: counter(page);
                    }
                    @page {
                        counter-increment: page;
                    }
                }
            `;
            document.head.appendChild(style);
            
            window.print();
            
            // Restaurar el estado original después de imprimir
            setTimeout(() => {
                // Eliminar estilos temporales
                const tempStyle = document.getElementById('print-page-numbers');
                if (tempStyle) tempStyle.remove();
                
                // Ocultar elementos de impresión
                document.querySelector('.print-header').style.display = 'none';
                document.querySelector('.print-info').style.display = 'none';
                document.querySelector('.print-footer').style.display = 'none';
                
                // Restaurar acordeones a su estado original
                accordionItems.forEach(item => {
                    if (item.id !== 'collapse1') {
                        item.classList.remove('show');
                    }
                });
                
                // Restaurar estilos de modal
                modalDialog.style.maxWidth = '';
                modalDialog.style.width = '';
                modalDialog.style.margin = '';
                modalContent.style.border = '';
                
                // Restaurar display de accordion bodies
                document.querySelectorAll('.accordion-body').forEach(body => {
                    body.style.display = '';
                    body.style.height = '';
                    body.style.overflow = '';
                });
                
                // Restaurar elementos de la lista
                document.querySelectorAll('.list-group-item').forEach(item => {
                    item.style.marginLeft = '';
                    item.style.position = '';
                });
            }, 500);
        }, 300);
    }
</script>
@endpush
