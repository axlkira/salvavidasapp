@extends('layouts.app')

@section('title', 'Patrones de Conversación - SalvaVidas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="page-title">
                    <i class="fas fa-comment-dots text-info me-2"></i> Patrones de Conversación
                </h2>
                <div>
                    <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
            <p class="text-muted">Analiza las conversaciones y detecta patrones lingüísticos que indican riesgo elevado</p>
        </div>
    </div>

    <div class="row">
        <!-- Estadísticas de conversación -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Estadísticas de Conversación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height:200px;">
                                <canvas id="conversationDistributionChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="conversation-stats">
                                <div class="conversation-stat-item">
                                    <div class="stat-label">Conversaciones de alto riesgo:</div>
                                    <div class="stat-value">
                                        <span class="badge bg-danger rounded-pill">{{ $patterns['high_risk_conversations'] }}</span>
                                        <span class="text-muted small">
                                            {{ $patterns['conversation_percentage'] }}% del total
                                        </span>
                                    </div>
                                </div>
                                <div class="conversation-stat-item">
                                    <div class="stat-label">Otras conversaciones:</div>
                                    <div class="stat-value">
                                        <span class="badge bg-secondary rounded-pill">{{ $patterns['other_conversations'] }}</span>
                                        <span class="text-muted small">
                                            {{ 100 - $patterns['conversation_percentage'] }}% del total
                                        </span>
                                    </div>
                                </div>
                                <div class="conversation-stat-item">
                                    <div class="stat-label">Long. promedio (alto riesgo):</div>
                                    <div class="stat-value">
                                        <span class="badge bg-info rounded-pill">{{ $patterns['high_risk_avg_length'] }} mensajes</span>
                                    </div>
                                </div>
                                <div class="conversation-stat-item">
                                    <div class="stat-label">Long. promedio (otras):</div>
                                    <div class="stat-value">
                                        <span class="badge bg-light text-dark rounded-pill">{{ $patterns['other_avg_length'] }} mensajes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Análisis:</strong> 
                        @if($patterns['high_risk_avg_length'] > $patterns['other_avg_length'])
                            Las conversaciones de alto riesgo tienden a ser más largas ({{ $patterns['high_risk_avg_length'] }} vs {{ $patterns['other_avg_length'] }} mensajes), lo que podría indicar una exploración más profunda de los factores de riesgo.
                        @elseif($patterns['high_risk_avg_length'] < $patterns['other_avg_length'])
                            Las conversaciones de alto riesgo tienden a ser más cortas ({{ $patterns['high_risk_avg_length'] }} vs {{ $patterns['other_avg_length'] }} mensajes), lo que podría indicar una identificación temprana del riesgo.
                        @else
                            Las conversaciones de alto riesgo y otras tienen longitudes similares ({{ $patterns['high_risk_avg_length'] }} mensajes).
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparativa de longitud -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Comparativa de Longitud de Conversaciones</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:250px;">
                        <canvas id="conversationLengthChart"></canvas>
                    </div>
                    
                    <div class="alert alert-secondary mt-3">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Insight:</strong> 
                        La diferencia en la longitud de las conversaciones puede ayudar a identificar patrones de interacción que requieren atención especial por parte de los profesionales.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Frases clave -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Frases Clave Detectadas en Conversaciones de Alto Riesgo</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Frase</th>
                                            <th class="text-center">Frecuencia</th>
                                            <th class="text-center">Nivel de Riesgo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($keyPhrases as $index => $phrase)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $phrase['phrase'] }}</td>
                                                <td class="text-center">{{ $phrase['frequency'] }}</td>
                                                <td class="text-center">
                                                    @if($phrase['risk_level'] == 'alto')
                                                        <span class="badge bg-danger">Alto</span>
                                                    @elseif($phrase['risk_level'] == 'medio')
                                                        <span class="badge bg-warning text-dark">Medio</span>
                                                    @else
                                                        <span class="badge bg-success">Bajo</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="keyPhrasesChart"></canvas>
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
                    <h5 class="card-title">Recomendaciones Basadas en Patrones de Conversación</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-primary">
                        <h5><i class="fas fa-lightbulb me-2"></i>Estrategias de Intervención Recomendadas</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="recommendation-card">
                                    <div class="recommendation-icon bg-info">
                                        <i class="fas fa-comment-medical"></i>
                                    </div>
                                    <h6>Preguntas Clave</h6>
                                    <p>Implementar preguntas específicas cuando se detecten frases como:</p>
                                    <ul class="small">
                                        @if(isset($keyPhrases[0]))
                                            <li><strong>{{ $keyPhrases[0]['phrase'] }}</strong></li>
                                        @endif
                                        @if(isset($keyPhrases[1]))
                                            <li><strong>{{ $keyPhrases[1]['phrase'] }}</strong></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="recommendation-card">
                                    <div class="recommendation-icon bg-warning">
                                        <i class="fas fa-hand-holding-heart"></i>
                                    </div>
                                    <h6>Técnicas de Escucha Activa</h6>
                                    <p>Reforzar técnicas de escucha activa y validación emocional cuando los pacientes expresen ideas de desesperanza.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="recommendation-card">
                                    <div class="recommendation-icon bg-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <h6>Protocolos de Escalada</h6>
                                    <p>Implementar protocolos de escalada inmediata cuando se detecten frases de alto riesgo como:</p>
                                    <ul class="small">
                                        @foreach($keyPhrases as $phrase)
                                            @if($phrase['risk_level'] == 'alto' && $loop->index < 2)
                                                <li><strong>{{ $phrase['phrase'] }}</strong></li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h6 class="fw-bold">Implementación en el Sistema</h6>
                            <p>Se recomienda integrar un sistema de detección automática de estas frases clave dentro de la aplicación de chat para alertar a los profesionales en tiempo real sobre posibles casos de alto riesgo.</p>
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
    .conversation-stats {
        padding: 0 15px;
    }
    
    .conversation-stat-item {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .conversation-stat-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .stat-label {
        font-size: 14px;
        color: #495057;
        margin-bottom: 5px;
    }
    
    .stat-value {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .stat-value .badge {
        padding: 7px 10px;
        margin-right: 10px;
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
        margin-bottom: 10px;
    }
    
    .recommendation-card ul {
        padding-left: 15px;
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
    // Datos para el gráfico de distribución de conversaciones
    const conversationDistributionData = {
        labels: ['Alto Riesgo', 'Otras'],
        datasets: [{
            data: [
                {{ $patterns['high_risk_conversations'] }}, 
                {{ $patterns['other_conversations'] }}
            ],
            backgroundColor: [
                'rgba(220, 53, 69, 0.7)',
                'rgba(108, 117, 125, 0.7)'
            ],
            borderColor: [
                'rgb(220, 53, 69)',
                'rgb(108, 117, 125)'
            ],
            borderWidth: 1
        }]
    };
    
    // Gráfico de distribución de conversaciones
    const distributionCtx = document.getElementById('conversationDistributionChart').getContext('2d');
    const distributionChart = new Chart(distributionCtx, {
        type: 'pie',
        data: conversationDistributionData,
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
    
    // Datos para el gráfico de longitud de conversaciones
    const lengthData = {
        labels: ['Alto Riesgo', 'Otras Conversaciones'],
        datasets: [{
            label: 'Promedio de mensajes por conversación',
            data: [
                {{ $patterns['high_risk_avg_length'] }}, 
                {{ $patterns['other_avg_length'] }}
            ],
            backgroundColor: [
                'rgba(220, 53, 69, 0.7)',
                'rgba(108, 117, 125, 0.7)'
            ],
            borderColor: [
                'rgb(220, 53, 69)',
                'rgb(108, 117, 125)'
            ],
            borderWidth: 1
        }]
    };
    
    // Gráfico de longitud de conversaciones
    const lengthCtx = document.getElementById('conversationLengthChart').getContext('2d');
    const lengthChart = new Chart(lengthCtx, {
        type: 'bar',
        data: lengthData,
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
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Promedio de mensajes'
                    }
                }
            }
        }
    });
    
    // Datos para el gráfico de frases clave
    const phrasesData = {
        labels: [
            @foreach($keyPhrases as $phrase)
                @if($loop->index < 5)
                    '{{ $phrase['phrase'] }}',
                @endif
            @endforeach
        ],
        datasets: [{
            label: 'Frecuencia',
            data: [
                @foreach($keyPhrases as $phrase)
                    @if($loop->index < 5)
                        {{ $phrase['frequency'] }},
                    @endif
                @endforeach
            ],
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
    };
    
    // Gráfico de frases clave
    const phrasesCtx = document.getElementById('keyPhrasesChart').getContext('2d');
    const phrasesChart = new Chart(phrasesCtx, {
        type: 'horizontalBar',
        data: phrasesData,
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
