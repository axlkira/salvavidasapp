@extends('layouts.app')

@section('title', 'Tendencias de Riesgo - SalvaVidas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="page-title">
                    <i class="fas fa-chart-line text-primary me-2"></i> Tendencias de Riesgo
                </h2>
                <div>
                    <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
            <p class="text-muted">Analiza cómo evolucionan los niveles de riesgo a lo largo del tiempo</p>
        </div>
    </div>

    <!-- Filtros de fecha y período -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('analytics.risk-trends') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="period" class="form-label">Período</label>
                            <select class="form-select" id="period" name="period">
                                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Diario</option>
                                <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Semanal</option>
                                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Mensual</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Fecha inicio</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Fecha fin</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Aplicar filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico principal de tendencias -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        Evolución de Niveles de Riesgo - 
                        @if($period == 'daily')
                            Vista Diaria
                        @elseif($period == 'weekly')
                            Vista Semanal
                        @else
                            Vista Mensual
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="riskTrendsMainChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Gráfico de línea -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Tendencia de Casos de Alto Riesgo</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="highRiskTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de área -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Distribución de Niveles de Riesgo</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="riskDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Tabla de datos -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Datos Detallados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Período</th>
                                    <th class="text-center text-danger">Alto Riesgo</th>
                                    <th class="text-center text-warning">Riesgo Medio</th>
                                    <th class="text-center text-success">Riesgo Bajo</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">% Alto Riesgo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trends['labels'] as $index => $label)
                                    @php
                                        $highRisk = $trends['datasets'][0]['data'][$index] ?? 0;
                                        $mediumRisk = $trends['datasets'][1]['data'][$index] ?? 0;
                                        $lowRisk = $trends['datasets'][2]['data'][$index] ?? 0;
                                        $total = $highRisk + $mediumRisk + $lowRisk;
                                        $highRiskPercent = $total > 0 ? round(($highRisk / $total) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-center">{{ $highRisk }}</td>
                                        <td class="text-center">{{ $mediumRisk }}</td>
                                        <td class="text-center">{{ $lowRisk }}</td>
                                        <td class="text-center">{{ $total }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $highRiskPercent > 30 ? 'bg-danger' : 'bg-secondary' }}">
                                                {{ $highRiskPercent }}%
                                            </span>
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
    }
    
    .card-header {
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Datos de las tendencias
    const trendsLabels = {!! json_encode($trends['labels']) !!};
    const highRiskData = {!! json_encode($trends['datasets'][0]['data']) !!};
    const mediumRiskData = {!! json_encode($trends['datasets'][1]['data']) !!};
    const lowRiskData = {!! json_encode($trends['datasets'][2]['data']) !!};
    
    // Calcular totales para cada período
    const totalData = highRiskData.map((high, index) => {
        return high + mediumRiskData[index] + lowRiskData[index];
    });
    
    // Gráfico principal de tendencias
    const riskTrendsCtx = document.getElementById('riskTrendsMainChart').getContext('2d');
    const riskTrendsChart = new Chart(riskTrendsCtx, {
        type: 'bar',
        data: {
            labels: trendsLabels,
            datasets: [
                {
                    label: 'Alto Riesgo',
                    data: highRiskData,
                    backgroundColor: 'rgba(220, 53, 69, 0.6)',
                    borderColor: 'rgb(220, 53, 69)',
                    borderWidth: 1
                },
                {
                    label: 'Riesgo Medio',
                    data: mediumRiskData,
                    backgroundColor: 'rgba(255, 193, 7, 0.6)',
                    borderColor: 'rgb(255, 193, 7)',
                    borderWidth: 1
                },
                {
                    label: 'Riesgo Bajo',
                    data: lowRiskData,
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        footer: (tooltipItems) => {
                            let total = 0;
                            tooltipItems.forEach(item => {
                                total += item.parsed.y;
                            });
                            return 'Total: ' + total;
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                },
                y: {
                    stacked: true,
                    beginAtZero: true
                }
            }
        }
    });
    
    // Gráfico de línea de casos de alto riesgo
    const highRiskTrendCtx = document.getElementById('highRiskTrendChart').getContext('2d');
    const highRiskTrendChart = new Chart(highRiskTrendCtx, {
        type: 'line',
        data: {
            labels: trendsLabels,
            datasets: [{
                label: 'Casos de Alto Riesgo',
                data: highRiskData,
                fill: false,
                backgroundColor: 'rgb(220, 53, 69)',
                borderColor: 'rgb(220, 53, 69)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Gráfico de área de distribución
    const distributionCtx = document.getElementById('riskDistributionChart').getContext('2d');
    const distributionChart = new Chart(distributionCtx, {
        type: 'line',
        data: {
            labels: trendsLabels,
            datasets: [
                {
                    label: 'Alto Riesgo',
                    data: highRiskData.map((value, index) => (value / totalData[index] * 100) || 0),
                    backgroundColor: 'rgba(220, 53, 69, 0.4)',
                    borderColor: 'rgb(220, 53, 69)',
                    pointBackgroundColor: 'rgb(220, 53, 69)',
                    fill: true
                },
                {
                    label: 'Riesgo Medio',
                    data: mediumRiskData.map((value, index) => (value / totalData[index] * 100) || 0),
                    backgroundColor: 'rgba(255, 193, 7, 0.4)',
                    borderColor: 'rgb(255, 193, 7)',
                    pointBackgroundColor: 'rgb(255, 193, 7)',
                    fill: true
                },
                {
                    label: 'Riesgo Bajo',
                    data: lowRiskData.map((value, index) => (value / totalData[index] * 100) || 0),
                    backgroundColor: 'rgba(40, 167, 69, 0.4)',
                    borderColor: 'rgb(40, 167, 69)',
                    pointBackgroundColor: 'rgb(40, 167, 69)',
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    stacked: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
