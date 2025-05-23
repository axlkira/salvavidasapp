@extends('layouts.app')

@section('title', 'Alertas de Riesgo - SalvaVidas')

@section('page-title', 'Alertas de Riesgo')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Alertas de Riesgo Crítico</h2>
            </div>
        </div>
    </div>

    <!-- Banner de alertas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> ¡Atención! Casos de Alto Riesgo</h4>
                <p>Las siguientes evaluaciones han sido identificadas como de <strong>alto riesgo</strong> o <strong>riesgo crítico</strong> y requieren atención inmediata.</p>
                <hr>
                <p class="mb-0">Por favor, revise estos casos con prioridad y tome las medidas apropiadas según los protocolos de intervención en crisis.</p>
            </div>
        </div>
    </div>

    <!-- Tabla de evaluaciones de riesgo críticas -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-heartbeat"></i> Casos que requieren atención inmediata</h5>
                </div>
                <div class="card-body">
                    @if(count($assessments) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="alertsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Paciente</th>
                                        <th>Nivel de Riesgo</th>
                                        <th>Puntaje</th>
                                        <th>Factores Clave</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assessmentsData as $index => $assessment)
                                        <tr class="table-danger">
                                            <td>{{ $assessment['id'] }}</td>
                                            <td>
                                                <strong>{{ $assessment['patient_name'] }}</strong>
                                                @if($assessment['patient_document'])
                                                <br><small class="text-muted">{{ $assessment['patient_document'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $assessment['risk_level'] == 'crítico' || $assessment['risk_level'] == 'critico' ? 'bg-dark' : 'bg-danger' }}">
                                                    {{ ucfirst($assessment['risk_level']) }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($assessment['risk_score'], 1) }}</td>
                                            <td>
                                                @if(count($assessment['risk_factors']) > 0)
                                                    <ul class="mb-0 ps-3">
                                                        @foreach($assessment['risk_factors'] as $factor)
                                                            <li>{{ Str::limit($factor, 50) }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">No se han identificado factores específicos</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($assessment['created_at'])->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('risk-assessment.show', $assessment['id']) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                    @if($assessment['conversation_id'])
                                                        <a href="{{ route('chat.show', $assessment['conversation_id']) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-comments"></i> Chat
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $assessments->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay alertas de riesgo crítico en este momento.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recomendaciones para profesionales -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Protocolo de Acción</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li><strong>Evaluar inmediatamente:</strong> Contacte al paciente lo antes posible para realizar una evaluación directa del riesgo.</li>
                        <li><strong>Documentar la intervención:</strong> Registre todas las acciones tomadas en el sistema.</li>
                        <li><strong>Seguir el protocolo institucional:</strong> Active los protocolos de intervención en crisis según corresponda.</li>
                        <li><strong>Coordinar la atención:</strong> Considere la necesidad de remisión a servicios de emergencia o especialistas en salud mental.</li>
                        <li><strong>Realizar seguimiento:</strong> Establezca un plan de seguimiento estructurado para monitorear la evolución del caso.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar DataTable con opciones básicas
        $('#alertsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "pageLength": 10,
            "order": [[5, 'desc']], // Ordenar por fecha (descendente)
            "columnDefs": [
                { "orderable": false, "targets": 6 } // La columna de acciones no es ordenable
            ]
        });
    });
</script>
@endpush
