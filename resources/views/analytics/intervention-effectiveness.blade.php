@extends('layouts.app')

@section('title', 'Efectividad de Intervenciones - SalvaVidas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="page-title">
                    <i class="fas fa-clipboard-check text-success me-2"></i> Efectividad de Intervenciones
                </h2>
                <div>
                    <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
            <p class="text-muted">Analiza el impacto de tus intervenciones y seguimientos en pacientes de alto riesgo</p>
        </div>
    </div>

    <div class="row">
        <!-- Tarjetas de estadísticas -->
        <div class="col-md-4">
            <div class="stats-card bg-success-light">
                <div class="stats-card-body">
                    <div class="stats-card-icon bg-success text-white">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="stats-card-content">
                        <h3 class="stats-card-title">{{ $effectiveness['improved'] }}</h3>
                        <p class="stats-card-text">Pacientes Mejorados</p>
                        <span class="stats-card-percent">{{ $effectiveness['percentage']['improved'] }}% del total</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card bg-warning-light">
                <div class="stats-card-body">
                    <div class="stats-card-icon bg-warning text-white">
                        <i class="fas fa-minus"></i>
                    </div>
                    <div class="stats-card-content">
                        <h3 class="stats-card-title">{{ $effectiveness['stable'] }}</h3>
                        <p class="stats-card-text">Pacientes Estables</p>
                        <span class="stats-card-percent">{{ $effectiveness['percentage']['stable'] }}% del total</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card bg-danger-light">
                <div class="stats-card-body">
                    <div class="stats-card-icon bg-danger text-white">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="stats-card-content">
                        <h3 class="stats-card-title">{{ $effectiveness['worsened'] }}</h3>
                        <p class="stats-card-text">Pacientes Empeorados</p>
                        <span class="stats-card-percent">{{ $effectiveness['percentage']['worsened'] }}% del total</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Gráfico principal de efectividad -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Resultados de Intervenciones</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:350px;">
                        <canvas id="effectivenessChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de seguimiento -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Seguimiento de Casos de Alto Riesgo</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height:200px;">
                                <canvas id="followUpChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="follow-up-stats">
                                <div class="follow-up-item">
                                    <div class="follow-up-label">Casos de alto riesgo con seguimiento</div>
                                    <div class="follow-up-value">
                                        <span class="badge bg-success">{{ $followUpStats['followed_up'] }}</span>
                                        <span class="text-muted small">
                                            {{ $followUpStats['percentage'] }}% del total
                                        </span>
                                    </div>
                                </div>
                                <div class="follow-up-item">
                                    <div class="follow-up-label">Casos de alto riesgo sin seguimiento</div>
                                    <div class="follow-up-value">
                                        <span class="badge bg-danger">{{ $followUpStats['not_followed'] }}</span>
                                        <span class="text-muted small">
                                            {{ 100 - $followUpStats['percentage'] }}% del total
                                        </span>
                                    </div>
                                </div>
                                <div class="follow-up-item">
                                    <div class="follow-up-label">Total de casos de alto riesgo</div>
                                    <div class="follow-up-value">
                                        <span class="badge bg-primary">{{ $followUpStats['high_risk_total'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Indicador de calidad:</strong> 
                        @if($followUpStats['percentage'] >= 90)
                            <span class="text-success">Excelente seguimiento (≥90%)</span>
                        @elseif($followUpStats['percentage'] >= 75)
                            <span class="text-primary">Buen seguimiento (≥75%)</span>
                        @elseif($followUpStats['percentage'] >= 50)
                            <span class="text-warning">Seguimiento regular (≥50%)</span>
                        @else
                            <span class="text-danger">Seguimiento deficiente (<50%)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Análisis de tendencias -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Análisis de Efectividad y Oportunidades de Mejora</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="analysis-section">
                                <h5 class="analysis-title">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Fortalezas
                                </h5>
                                <ul class="analysis-list">
                                    @if($effectiveness['percentage']['improved'] > 50)
                                        <li>Alta tasa de mejora ({{ $effectiveness['percentage']['improved'] }}%) en pacientes intervenidos</li>
                                    @endif
                                    @if($followUpStats['percentage'] > 70)
                                        <li>Buen seguimiento ({{ $followUpStats['percentage'] }}%) de casos de alto riesgo</li>
                                    @endif
                                    <li>Sistema estructurado de seguimiento a pacientes de alto riesgo</li>
                                    <li>Protocolos claros de intervención para diferentes niveles de riesgo</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="analysis-section">
                                <h5 class="analysis-title">
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                    Oportunidades de Mejora
                                </h5>
                                <ul class="analysis-list">
                                    @if($effectiveness['percentage']['worsened'] > 20)
                                        <li>Revisar protocolos para reducir la tasa de deterioro ({{ $effectiveness['percentage']['worsened'] }}%)</li>
                                    @endif
                                    @if($followUpStats['percentage'] < 70)
                                        <li>Aumentar la cobertura de seguimiento de casos de alto riesgo (actualmente {{ $followUpStats['percentage'] }}%)</li>
                                    @endif
                                    <li>Implementar recordatorios automáticos para seguimiento de casos</li>
                                    <li>Capacitar al personal en técnicas de intervención más efectivas</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="recommendations-section mt-4">
                        <h5 class="recommendations-title">
                            <i class="fas fa-lightbulb text-primary me-2"></i>
                            Recomendaciones
                        </h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="recommendation-card">
                                    <div class="recommendation-icon bg-primary">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <h6>Capacitación del Personal</h6>
                                    <p>Implementar sesiones de capacitación enfocadas en intervenciones para los factores de riesgo más comunes.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="recommendation-card">
                                    <div class="recommendation-icon bg-warning">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <h6>Optimización de Protocolos</h6>
                                    <p>Revisar y ajustar los protocolos de seguimiento para garantizar contacto con todos los casos de alto riesgo.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="recommendation-card">
                                    <div class="recommendation-icon bg-info">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                    <h6>Medición de Resultados</h6>
                                    <p>Implementar métricas adicionales para evaluar la efectividad de intervenciones específicas.</p>
                                </div>
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
    .stats-card {
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
    
    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.1);
    }
    
    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1);
    }
    
    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.1);
    }
    
    .follow-up-stats {
        padding: 0 15px;
    }
    
    .follow-up-item {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .follow-up-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .follow-up-label {
        font-size: 14px;
        color: #495057;
        margin-bottom: 5px;
    }
    
    .follow-up-value {
        display: flex;
        align-items: center;
    }
    
    .follow-up-value .badge {
        font-size: 15px;
        padding: 7px 12px;
        margin-right: 10px;
    }
    
    .analysis-section {
        margin-bottom: 20px;
    }
    
    .analysis-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .analysis-list {
        padding-left: 20px;
    }
    
    .analysis-list li {
        margin-bottom: 10px;
    }
    
    .recommendations-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .recommendation-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-bottom: 20px;
        height: 100%;
        transition: transform 0.3s;
    }
    
    .recommendation-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    .recommendation-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 20px;
        color: white;
    }
    
    .recommendation-card h6 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .recommendation-card p {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 0;
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Datos para el gráfico de efectividad
    const effectivenessData = {
        labels: ['Mejorados', 'Estables', 'Empeorados'],
        datasets: [{
            data: [
                {{ $effectiveness['improved'] }}, 
                {{ $effectiveness['stable'] }}, 
                {{ $effectiveness['worsened'] }}
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.7)',
                'rgba(255, 193, 7, 0.7)',
                'rgba(220, 53, 69, 0.7)'
            ],
            borderColor: [
                'rgb(40, 167, 69)',
                'rgb(255, 193, 7)',
                'rgb(220, 53, 69)'
            ],
            borderWidth: 1
        }]
    };
    
    // Gráfico de efectividad
    const effectivenessCtx = document.getElementById('effectivenessChart').getContext('2d');
    const effectivenessChart = new Chart(effectivenessCtx, {
        type: 'doughnut',
        data: effectivenessData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
    
    // Datos para el gráfico de seguimiento
    const followUpData = {
        labels: ['Con Seguimiento', 'Sin Seguimiento'],
        datasets: [{
            data: [
                {{ $followUpStats['followed_up'] }}, 
                {{ $followUpStats['not_followed'] }}
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.7)',
                'rgba(220, 53, 69, 0.7)'
            ],
            borderColor: [
                'rgb(40, 167, 69)',
                'rgb(220, 53, 69)'
            ],
            borderWidth: 1
        }]
    };
    
    // Gráfico de seguimiento
    const followUpCtx = document.getElementById('followUpChart').getContext('2d');
    const followUpChart = new Chart(followUpCtx, {
        type: 'pie',
        data: followUpData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
