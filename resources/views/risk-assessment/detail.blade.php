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
                        <div class="chat-container p-3">
                            @foreach($relevantMessages as $message)
                                <div class="chat-message {{ $message->role == 'user' ? 'user' : 'assistant' }}">
                                    <div class="chat-bubble">
                                        <div class="chat-content">{{ $message->content }}</div>
                                        <div class="chat-time">{{ $message->created_at->format('H:i') }}</div>
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
                                <a href="#" class="btn btn-success me-2">
                                    <i class="fas fa-file-medical"></i> Crear Guía de Intervención
                                </a>
                                <a href="#" class="btn btn-danger">
                                    <i class="fas fa-exclamation-circle"></i> Marcar como Crítico
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
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
    
    .chat-container {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    .chat-message {
        margin-bottom: 15px;
        display: flex;
    }
    
    .chat-message.user {
        justify-content: flex-end;
    }
    
    .chat-bubble {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 15px;
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
</style>
@endpush
