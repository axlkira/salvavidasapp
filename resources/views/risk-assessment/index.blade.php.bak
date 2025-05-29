@extends('layouts.app')

@section('title', 'Evaluaciones de Riesgo - SalvaVidas')

@section('page-title', 'Evaluaciones de Riesgo')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Evaluaciones de Riesgo</h2>
            </div>
        </div>
    </div>



    <!-- Tabla de evaluaciones de riesgo -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Listado de Evaluaciones</h5>
                </div>
                <div class="card-body">
                    @if(count($assessments) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="assessmentsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Paciente</th>
                                        <th>Nivel de Riesgo</th>
                                        <th>Puntaje</th>
                                        <th>Factores Clave</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assessmentsData as $index => $assessment)
                                        <tr>
                                            <td>{{ $assessment['id'] }}</td>
                                            <td>
                                                {{ $assessment['patient_name'] }}
                                                @if($assessment['patient_document'])
                                                <br><small class="text-muted">{{ $assessment['patient_document'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ getRiskBadgeClass($assessment['risk_level']) }}">
                                                    {{ ucfirst($assessment['risk_level']) }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($assessment['risk_score'], 1) }}</td>
                                            <td>
                                                @if(count($assessment['risk_factors']) > 0)
                                                    <ul class="mb-0 ps-3">
                                                        @foreach($assessment['risk_factors'] as $factor)
                                                            <li>{{ $factor }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">No especificados</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ getStatusBadgeClass($assessment['status']) }}">
                                                    {{ getStatusLabel($assessment['status']) }}
                                                </span>
                                            </td>
                                            <td>{{ $assessment['created_at']->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('risk-assessment.show', $assessment['id']) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($assessment['conversation_id'])
                                                <a href="{{ route('chat.show', $assessment['conversation_id']) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-comments"></i>
                                                </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación manejada por DataTables, podemos ocultar la de Laravel -->
                        <div class="d-none">
                            {{ $assessments->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <p class="mb-0 text-center">No se encontraron evaluaciones de riesgo con los filtros seleccionados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar DataTables
        $('#assessmentsTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
            },
            order: [[6, 'desc']], // Ordenar por fecha (columna 6) descendente
            columnDefs: [
                { orderable: false, targets: 7 } // No permitir ordenar por la columna de acciones
            ],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
        });
    });
</script>
@endpush

@php
function getRiskBadgeClass($riskLevel) {
    switch ($riskLevel) {
        case 'bajo':
            return 'bg-success';
        case 'medio':
            return 'bg-warning text-dark';
        case 'alto':
            return 'bg-danger';
        case 'crítico':
        case 'critico':
            return 'bg-dark';
        default:
            return 'bg-secondary';
    }
}

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-secondary';
        case 'reviewed':
            return 'bg-success';
        case 'archived':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'pending':
            return 'Pendiente';
        case 'reviewed':
            return 'Revisado';
        case 'archived':
            return 'Archivado';
        default:
            return 'Desconocido';
    }
}
@endphp
