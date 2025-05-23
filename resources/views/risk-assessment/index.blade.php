@extends('layouts.app')

@section('title', 'Evaluaciones de Riesgo - SalvaVidas')

@section('page-title', 'Evaluaciones de Riesgo')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Evaluaciones de Riesgo</h2>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Filtros</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('risk-assessment.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="risk_level" class="form-label">Nivel de Riesgo</label>
                            <select name="risk_level" id="risk_level" class="form-select">
                                <option value="all" {{ $filters['risk_level'] == 'all' ? 'selected' : '' }}>Todos</option>
                                <option value="bajo" {{ $filters['risk_level'] == 'bajo' ? 'selected' : '' }}>Bajo</option>
                                <option value="medio" {{ $filters['risk_level'] == 'medio' ? 'selected' : '' }}>Medio</option>
                                <option value="alto" {{ $filters['risk_level'] == 'alto' ? 'selected' : '' }}>Alto</option>
                                <option value="crítico" {{ $filters['risk_level'] == 'crítico' ? 'selected' : '' }}>Crítico</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="status" class="form-label">Estado</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" {{ $filters['status'] == 'all' ? 'selected' : '' }}>Todos</option>
                                <option value="pending" {{ $filters['status'] == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="reviewed" {{ $filters['status'] == 'reviewed' ? 'selected' : '' }}>Revisado</option>
                                <option value="archived" {{ $filters['status'] == 'archived' ? 'selected' : '' }}>Archivado</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Fecha Desde</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Fecha Hasta</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                        </div>
                        
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('risk-assessment.index') }}" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Limpiar Filtros
                            </a>
                        </div>
                    </form>
                </div>
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
                            <table class="table table-striped table-hover">
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
                        
                        <!-- Paginación -->
                        <div class="d-flex justify-content-center mt-4">
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
        // Inicializar datepickers si es necesario
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
