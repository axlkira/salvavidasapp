@extends('layouts.app')

@section('title', 'Factores de Riesgo - SalvaVidas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="page-title">
                    <i class="fas fa-exclamation-circle text-danger me-2"></i> Factores de Riesgo
                </h2>
                <div>
                    <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
            <p class="text-muted">Identifica los factores de riesgo más comunes para mejorar la prevención</p>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico principal de factores comunes -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Factores de Riesgo Más Comunes</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="commonFactorsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de factores -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Estadísticas de Factores</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Total de factores identificados:</span>
                            <span class="badge bg-primary rounded-pill">{{ array_sum($commonFactors['data']) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Factores distintos:</span>
                            <span class="badge bg-primary rounded-pill">{{ count($commonFactors['labels']) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Factor más común:</span>
                            <span class="badge bg-danger rounded-pill">
                                {{ $commonFactors['labels'][0] ?? 'N/A' }}
                            </span>
                        </li>
                    </ul>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Consejo:</strong> Estos factores pueden ser utilizados para desarrollar programas de prevención específicos.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Factores por nivel de riesgo -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Factores de Riesgo por Nivel de Gravedad</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="riskFactorTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="high-risk-tab" data-bs-toggle="tab" data-bs-target="#high-risk" 
                                    type="button" role="tab" aria-controls="high-risk" aria-selected="true">
                                <i class="fas fa-exclamation-triangle text-danger me-1"></i> Alto Riesgo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="medium-risk-tab" data-bs-toggle="tab" data-bs-target="#medium-risk" 
                                    type="button" role="tab" aria-controls="medium-risk" aria-selected="false">
                                <i class="fas fa-exclamation text-warning me-1"></i> Riesgo Medio
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="low-risk-tab" data-bs-toggle="tab" data-bs-target="#low-risk" 
                                    type="button" role="tab" aria-controls="low-risk" aria-selected="false">
                                <i class="fas fa-info-circle text-success me-1"></i> Riesgo Bajo
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content pt-4" id="riskFactorTabContent">
                        <div class="tab-pane fade show active" id="high-risk" role="tabpanel" aria-labelledby="high-risk-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container" style="position: relative; height:300px;">
                                        <canvas id="highRiskFactorsChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Factor de Riesgo</th>
                                                    <th class="text-center">Frecuencia</th>
                                                    <th class="text-center">%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $totalHighRisk = array_sum(array_column($factorsByRiskLevel['high'], 'count')); @endphp
                                                @foreach($factorsByRiskLevel['high'] as $index => $factor)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $factor['description'] }}</td>
                                                    <td class="text-center">{{ $factor['count'] }}</td>
                                                    <td class="text-center">
                                                        {{ $totalHighRisk > 0 ? round(($factor['count'] / $totalHighRisk) * 100, 1) : 0 }}%
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="medium-risk" role="tabpanel" aria-labelledby="medium-risk-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container" style="position: relative; height:300px;">
                                        <canvas id="mediumRiskFactorsChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Factor de Riesgo</th>
                                                    <th class="text-center">Frecuencia</th>
                                                    <th class="text-center">%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $totalMediumRisk = array_sum(array_column($factorsByRiskLevel['medium'], 'count')); @endphp
                                                @foreach($factorsByRiskLevel['medium'] as $index => $factor)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $factor['description'] }}</td>
                                                    <td class="text-center">{{ $factor['count'] }}</td>
                                                    <td class="text-center">
                                                        {{ $totalMediumRisk > 0 ? round(($factor['count'] / $totalMediumRisk) * 100, 1) : 0 }}%
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="low-risk" role="tabpanel" aria-labelledby="low-risk-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container" style="position: relative; height:300px;">
                                        <canvas id="lowRiskFactorsChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Factor de Riesgo</th>
                                                    <th class="text-center">Frecuencia</th>
                                                    <th class="text-center">%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $totalLowRisk = array_sum(array_column($factorsByRiskLevel['low'], 'count')); @endphp
                                                @foreach($factorsByRiskLevel['low'] as $index => $factor)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $factor['description'] }}</td>
                                                    <td class="text-center">{{ $factor['count'] }}</td>
                                                    <td class="text-center">
                                                        {{ $totalLowRisk > 0 ? round(($factor['count'] / $totalLowRisk) * 100, 1) : 0 }}%
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Recomendaciones -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recomendaciones Basadas en Factores de Riesgo</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-primary">
                        <h5><i class="fas fa-lightbulb me-2"></i>Estrategias de Intervención Recomendadas</h5>
                        <hr>
                        <p>Basado en los factores de riesgo más comunes identificados, recomendamos considerar las siguientes estrategias de intervención:</p>
                        <ul class="recommendation-list">
                            <li>
                                <strong>Desarrollar protocolos específicos</strong> para abordar los factores de riesgo más frecuentes:
                                @if(isset($commonFactors['labels'][0]))
                                    <span class="badge bg-danger">{{ $commonFactors['labels'][0] }}</span>
                                @endif
                                @if(isset($commonFactors['labels'][1]))
                                    <span class="badge bg-danger">{{ $commonFactors['labels'][1] }}</span>
                                @endif
                                @if(isset($commonFactors['labels'][2]))
                                    <span class="badge bg-danger">{{ $commonFactors['labels'][2] }}</span>
                                @endif
                            </li>
                            <li><strong>Capacitar al personal</strong> en la detección temprana de los factores de riesgo identificados</li>
                            <li><strong>Implementar herramientas de screening</strong> que evalúen específicamente estos factores</li>
                            <li><strong>Establecer alianzas</strong> con servicios especializados para derivación rápida de casos con factores de alto riesgo</li>
                            <li><strong>Revisar y actualizar</strong> el material educativo para pacientes con énfasis en los factores identificados</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .chart-container {
        margin: 0 auto;
    }
    
    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 0.5rem;
        border: none;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .recommendation-list li {
        margin-bottom: 10px;
    }
    
    .nav-tabs .nav-link {
        border-radius: 0;
        padding: 0.75rem 1.25rem;
    }
    
    .nav-tabs .nav-link.active {
        font-weight: 600;
        border-top: 3px solid #0d6efd;
    }
    
    .badge {
        margin: 0 3px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Datos para gráficos
    const commonFactorsLabels = {!! json_encode($commonFactors['labels']) !!};
    const commonFactorsData = {!! json_encode($commonFactors['data']) !!};
    
    // Datos para factores por nivel de riesgo
    const highRiskFactors = {!! json_encode($factorsByRiskLevel['high']) !!};
    const mediumRiskFactors = {!! json_encode($factorsByRiskLevel['medium']) !!};
    const lowRiskFactors = {!! json_encode($factorsByRiskLevel['low']) !!};
    
    // Preparar datos para gráficos específicos por nivel
    const highRiskLabels = highRiskFactors.map(factor => factor.description);
    const highRiskData = highRiskFactors.map(factor => factor.count);
    
    const mediumRiskLabels = mediumRiskFactors.map(factor => factor.description);
    const mediumRiskData = mediumRiskFactors.map(factor => factor.count);
    
    const lowRiskLabels = lowRiskFactors.map(factor => factor.description);
    const lowRiskData = lowRiskFactors.map(factor => factor.count);
    
    // Paleta de colores
    const backgroundColors = [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)',
        'rgba(199, 199, 199, 0.7)',
        'rgba(83, 102, 255, 0.7)',
        'rgba(40, 167, 69, 0.7)',
        'rgba(220, 53, 69, 0.7)',
    ];
    
    const borderColors = [
        'rgb(255, 99, 132)',
        'rgb(54, 162, 235)',
        'rgb(255, 206, 86)',
        'rgb(75, 192, 192)',
        'rgb(153, 102, 255)',
        'rgb(255, 159, 64)',
        'rgb(199, 199, 199)',
        'rgb(83, 102, 255)',
        'rgb(40, 167, 69)',
        'rgb(220, 53, 69)',
    ];
    
    // Gráfico principal de factores comunes
    const commonFactorsCtx = document.getElementById('commonFactorsChart').getContext('2d');
    const commonFactorsChart = new Chart(commonFactorsCtx, {
        type: 'bar',
        data: {
            labels: commonFactorsLabels,
            datasets: [{
                label: 'Frecuencia',
                data: commonFactorsData,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.x;
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Gráfico de factores de alto riesgo
    const highRiskFactorsCtx = document.getElementById('highRiskFactorsChart').getContext('2d');
    const highRiskFactorsChart = new Chart(highRiskFactorsCtx, {
        type: 'pie',
        data: {
            labels: highRiskLabels,
            datasets: [{
                data: highRiskData,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
    
    // Gráfico de factores de riesgo medio
    const mediumRiskFactorsCtx = document.getElementById('mediumRiskFactorsChart').getContext('2d');
    const mediumRiskFactorsChart = new Chart(mediumRiskFactorsCtx, {
        type: 'pie',
        data: {
            labels: mediumRiskLabels,
            datasets: [{
                data: mediumRiskData,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
    
    // Gráfico de factores de bajo riesgo
    const lowRiskFactorsCtx = document.getElementById('lowRiskFactorsChart').getContext('2d');
    const lowRiskFactorsChart = new Chart(lowRiskFactorsCtx, {
        type: 'pie',
        data: {
            labels: lowRiskLabels,
            datasets: [{
                data: lowRiskData,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
