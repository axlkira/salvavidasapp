@extends('layouts.app')

@section('title', 'Analítica de Riesgo - SalvaVidas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="page-title">
                    <i class="fas fa-chart-line text-primary me-2"></i> Analítica de Riesgo
                </h2>
                <div class="date-range-selector">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" data-range="week">Semana</button>
                        <button type="button" class="btn btn-outline-primary active" data-range="month">Mes</button>
                        <button type="button" class="btn btn-outline-primary" data-range="quarter">Trimestre</button>
                        <button type="button" class="btn btn-outline-primary" data-range="year">Año</button>
                    </div>
                </div>
            </div>
            <p class="text-muted">Visualiza tendencias y patrones de riesgo para mejorar tus intervenciones</p>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-body">
                    <div class="stats-card-icon bg-primary">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stats-card-content">
                        <h3 class="stats-card-title">{{ $stats['total'] }}</h3>
                        <p class="stats-card-text">Evaluaciones Totales</p>
                        @if($stats['percent_change'] > 0)
                            <span class="trend-badge positive">
                                <i class="fas fa-arrow-up"></i> {{ $stats['percent_change'] }}% vs mes anterior
                            </span>
                        @elseif($stats['percent_change'] < 0)
                            <span class="trend-badge negative">
                                <i class="fas fa-arrow-down"></i> {{ abs($stats['percent_change']) }}% vs mes anterior
                            </span>
                        @else
                            <span class="trend-badge neutral">
                                <i class="fas fa-equals"></i> Sin cambios vs mes anterior
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-body">
                    <div class="stats-card-icon bg-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stats-card-content">
                        <h3 class="stats-card-title">{{ $stats['high_risk'] }}</h3>
                        <p class="stats-card-text">Casos Alto Riesgo</p>
                        <span class="stats-card-percent">{{ $stats['risk_percentage']['high'] }}% del total</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-body">
                    <div class="stats-card-icon bg-warning">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="stats-card-content">
                        <h3 class="stats-card-title">{{ $stats['medium_risk'] }}</h3>
                        <p class="stats-card-text">Riesgo Medio</p>
                        <span class="stats-card-percent">{{ $stats['risk_percentage']['medium'] }}% del total</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-body">
                    <div class="stats-card-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-card-content">
                        <h3 class="stats-card-title">{{ $stats['low_risk'] }}</h3>
                        <p class="stats-card-text">Riesgo Bajo</p>
                        <span class="stats-card-percent">{{ $stats['risk_percentage']['low'] }}% del total</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Gráfico de Tendencias de Riesgo -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Tendencias de Riesgo</h5>
                    <div class="card-tools">
                        <a href="{{ route('analytics.risk-trends') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i> Ver detalle
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="riskTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Factores de Riesgo Comunes -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Factores de Riesgo Comunes</h5>
                    <div class="card-tools">
                        <a href="{{ route('analytics.risk-factors') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i> Ver detalle
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="riskFactorsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Estado de las Evaluaciones -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Estado de Evaluaciones</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="260"></canvas>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Acciones Analíticas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('analytics.risk-trends') }}" class="analytics-action-card">
                                <div class="analytics-action-icon bg-primary-light text-primary">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="analytics-action-content">
                                    <h5>Tendencias de Riesgo</h5>
                                    <p>Analiza por período: diario, semanal, mensual</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('analytics.risk-factors') }}" class="analytics-action-card">
                                <div class="analytics-action-icon bg-danger-light text-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div class="analytics-action-content">
                                    <h5>Factores de Riesgo</h5>
                                    <p>Identifica los patrones más comunes</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('analytics.intervention-effectiveness') }}" class="analytics-action-card">
                                <div class="analytics-action-icon bg-success-light text-success">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div class="analytics-action-content">
                                    <h5>Eficacia de Intervenciones</h5>
                                    <p>Mide el impacto de las intervenciones realizadas</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('analytics.conversation-patterns') }}" class="analytics-action-card">
                                <div class="analytics-action-icon bg-info-light text-info">
                                    <i class="fas fa-comment-dots"></i>
                                </div>
                                <div class="analytics-action-content">
                                    <h5>Patrones de Conversación</h5>
                                    <p>Analiza conversaciones de alto riesgo</p>
                                </div>
                            </a>
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
    .stats-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
        transition: transform 0.3s;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .stats-card-body {
        display: flex;
        padding: 20px;
        align-items: center;
    }
    
    .stats-card-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        font-size: 24px;
        color: white;
    }
    
    .stats-card-content {
        flex: 1;
    }
    
    .stats-card-title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stats-card-text {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .stats-card-percent {
        font-size: 13px;
        color: #495057;
        font-weight: 500;
    }
    
    .trend-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .trend-badge.positive {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }
    
    .trend-badge.negative {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .trend-badge.neutral {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }
    
    .date-range-selector {
        margin-left: auto;
    }
    
    .card-tools {
        position: absolute;
        right: 1rem;
        top: 0.75rem;
    }
    
    .card-header {
        position: relative;
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 0.75rem 1.25rem;
    }
    
    .card-title {
        margin-bottom: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .analytics-action-card {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 10px;
        background-color: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s;
        text-decoration: none;
        color: #212529;
        height: 100%;
    }
    
    .analytics-action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        text-decoration: none;
    }
    
    .analytics-action-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 18px;
    }
    
    .analytics-action-content {
        flex: 1;
    }
    
    .analytics-action-content h5 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .analytics-action-content p {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 0;
    }
    
    .bg-primary-light {
        background-color: rgba(0, 123, 255, 0.1);
    }
    
    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.1);
    }
    
    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.1);
    }
    
    .bg-info-light {
        background-color: rgba(23, 162, 184, 0.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Tendencias de Riesgo
    const riskTrendsCtx = document.getElementById('riskTrendsChart').getContext('2d');
    const riskTrendsChart = new Chart(riskTrendsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($riskTrends['labels']) !!},
            datasets: [
                {
                    label: 'Alto Riesgo',
                    data: {!! json_encode($riskTrends['datasets'][0]['data']) !!},
                    backgroundColor: 'rgba(220, 53, 69, 0.6)',
                    borderColor: 'rgb(220, 53, 69)',
                    borderWidth: 1
                },
                {
                    label: 'Riesgo Medio',
                    data: {!! json_encode($riskTrends['datasets'][1]['data']) !!},
                    backgroundColor: 'rgba(255, 193, 7, 0.6)',
                    borderColor: 'rgb(255, 193, 7)',
                    borderWidth: 1
                },
                {
                    label: 'Bajo Riesgo',
                    data: {!! json_encode($riskTrends['datasets'][2]['data']) !!},
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Evaluaciones de Riesgo por Período'
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
    
    // Gráfico de Factores de Riesgo Comunes
    const factorsCtx = document.getElementById('riskFactorsChart').getContext('2d');
    const factorsChart = new Chart(factorsCtx, {
        type: 'horizontalBar',
        data: {
            labels: {!! json_encode($commonFactors['labels']) !!},
            datasets: [{
                label: 'Frecuencia',
                data: {!! json_encode($commonFactors['data']) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 206, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: 'Factores de Riesgo Más Comunes'
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Gráfico de Estado de Evaluaciones
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = {
        labels: ['Pendientes', 'Revisadas', 'Urgentes'],
        datasets: [{
            data: [
                {{ $stats['status']['pending'] }}, 
                {{ $stats['status']['reviewed'] }}, 
                {{ $stats['status']['urgent'] }}
            ],
            backgroundColor: [
                'rgba(255, 193, 7, 0.7)',
                'rgba(40, 167, 69, 0.7)',
                'rgba(220, 53, 69, 0.7)'
            ],
            borderColor: [
                'rgb(255, 193, 7)',
                'rgb(40, 167, 69)',
                'rgb(220, 53, 69)'
            ],
            borderWidth: 1
        }]
    };
    
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: statusData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Estado de Evaluaciones'
                }
            }
        }
    });
    
    // Manejo de selector de rango de fechas
    document.querySelectorAll('.date-range-selector .btn').forEach(button => {
        button.addEventListener('click', function() {
            // Remover active de todos los botones
            document.querySelectorAll('.date-range-selector .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Agregar active al botón clickeado
            this.classList.add('active');
            
            // Aquí se podría hacer una petición AJAX para actualizar los datos según el rango seleccionado
            // Por ahora solo mostramos un mensaje
            console.log('Rango seleccionado:', this.dataset.range);
        });
    });
</script>
@endpush
