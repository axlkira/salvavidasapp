@extends('layouts.app')

@section('title', 'Dashboard - SalvaVidas')

@section('page-title', 'Dashboard')

@section('content')
<div class="dashboard-container">
    <div class="row">
        <!-- Tarjetas de estadísticas -->
        <div class="col-md-3 mb-4">
            <div class="stat-card bg-primary">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-card-info">
                        <div class="stat-card-value">1,250</div>
                        <div class="stat-card-title">Pacientes Totales</div>
                    </div>
                </div>
                <div class="stat-card-footer">
                    <span>+3.5% desde el mes pasado</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="stat-card bg-success">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-card-info">
                        <div class="stat-card-value">218</div>
                        <div class="stat-card-title">Evaluaciones</div>
                    </div>
                </div>
                <div class="stat-card-footer">
                    <span>+12% desde el mes pasado</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="stat-card bg-warning">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-card-info">
                        <div class="stat-card-value">42</div>
                        <div class="stat-card-title">Casos Alto Riesgo</div>
                    </div>
                </div>
                <div class="stat-card-footer">
                    <span>-5.2% desde el mes pasado</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="stat-card bg-danger">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-comment-medical"></i>
                    </div>
                    <div class="stat-card-info">
                        <div class="stat-card-value">356</div>
                        <div class="stat-card-title">Conversaciones IA</div>
                    </div>
                </div>
                <div class="stat-card-footer">
                    <span>+28% desde el mes pasado</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Gráfico de casos por riesgo -->
        <div class="col-lg-8 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title">Distribución de Casos por Nivel de Riesgo</h5>
                    <div class="card-actions">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Este Año
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Este Mes</a></li>
                                <li><a class="dropdown-item" href="#">Este Año</a></li>
                                <li><a class="dropdown-item" href="#">Todo el Tiempo</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="riskChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pacientes de alto riesgo -->
        <div class="col-lg-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title">Pacientes de Alto Riesgo</h5>
                    <div class="card-actions">
                        <a href="{{ route('risk.index') }}?risk_level=alto" class="btn btn-sm btn-light">Ver Todos</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="high-risk-list">
                        <div class="high-risk-item">
                            <div class="patient-info">
                                <div class="patient-name">Carlos Martínez</div>
                                <div class="patient-details">35 años - 89% riesgo</div>
                            </div>
                            <div class="patient-actions">
                                <a href="#" class="btn btn-sm btn-danger">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="high-risk-item">
                            <div class="patient-info">
                                <div class="patient-name">Laura Gómez</div>
                                <div class="patient-details">19 años - 85% riesgo</div>
                            </div>
                            <div class="patient-actions">
                                <a href="#" class="btn btn-sm btn-danger">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="high-risk-item">
                            <div class="patient-info">
                                <div class="patient-name">Javier Rodríguez</div>
                                <div class="patient-details">42 años - 78% riesgo</div>
                            </div>
                            <div class="patient-actions">
                                <a href="#" class="btn btn-sm btn-danger">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="high-risk-item">
                            <div class="patient-info">
                                <div class="patient-name">María Sánchez</div>
                                <div class="patient-details">28 años - 75% riesgo</div>
                            </div>
                            <div class="patient-actions">
                                <a href="#" class="btn btn-sm btn-danger">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="high-risk-item">
                            <div class="patient-info">
                                <div class="patient-name">Roberto Fernández</div>
                                <div class="patient-details">52 años - 72% riesgo</div>
                            </div>
                            <div class="patient-actions">
                                <a href="#" class="btn btn-sm btn-danger">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Últimas Evaluaciones -->
        <div class="col-lg-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title">Últimas Evaluaciones</h5>
                    <div class="card-actions">
                        <a href="{{ route('risk.index') }}" class="btn btn-sm btn-light">Ver Todas</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Paciente</th>
                                    <th>Fecha</th>
                                    <th>Nivel de Riesgo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Ana Castillo</td>
                                    <td>22/05/2025</td>
                                    <td><span class="badge bg-success">Bajo</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Miguel Torres</td>
                                    <td>21/05/2025</td>
                                    <td><span class="badge bg-warning">Moderado</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Laura Gómez</td>
                                    <td>20/05/2025</td>
                                    <td><span class="badge bg-danger">Alto</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>José Ramírez</td>
                                    <td>18/05/2025</td>
                                    <td><span class="badge bg-warning">Moderado</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Carmen Vega</td>
                                    <td>15/05/2025</td>
                                    <td><span class="badge bg-success">Bajo</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Últimas Conversaciones -->
        <div class="col-lg-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title">Conversaciones Recientes</h5>
                    <div class="card-actions">
                        <a href="{{ route('chat.index') }}" class="btn btn-sm btn-light">Ver Todas</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="conversation-list">
                        <div class="conversation-item">
                            <div class="conversation-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-title">Evaluación preliminar de Alejandro García</div>
                                <div class="conversation-excerpt">La IA ha identificado posibles factores de riesgo relacionados con...</div>
                                <div class="conversation-meta">hace 2 horas</div>
                            </div>
                            <div class="conversation-actions">
                                <a href="#" class="btn btn-sm btn-primary">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="conversation-item">
                            <div class="conversation-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-title">Seguimiento tratamiento Laura Gómez</div>
                                <div class="conversation-excerpt">Según el análisis de las últimas entradas, se observa una mejora en...</div>
                                <div class="conversation-meta">hace 5 horas</div>
                            </div>
                            <div class="conversation-actions">
                                <a href="#" class="btn btn-sm btn-primary">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="conversation-item">
                            <div class="conversation-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-title">Análisis de riesgo recurrente</div>
                                <div class="conversation-excerpt">Los patrones detectados sugieren un incremento en los niveles de...</div>
                                <div class="conversation-meta">hace 1 día</div>
                            </div>
                            <div class="conversation-actions">
                                <a href="#" class="btn btn-sm btn-primary">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="conversation-item">
                            <div class="conversation-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-title">Consulta guía de intervención</div>
                                <div class="conversation-excerpt">Basado en el historial clínico y los factores identificados, se recomienda...</div>
                                <div class="conversation-meta">hace 2 días</div>
                            </div>
                            <div class="conversation-actions">
                                <a href="#" class="btn btn-sm btn-primary">
                                    <i class="fas fa-arrow-right"></i>
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
    .dashboard-container {
        padding: 10px;
    }
    
    /* Tarjetas de estadísticas */
    .stat-card {
        border-radius: 10px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        height: 100%;
    }
    
    .stat-card-body {
        padding: 20px;
        display: flex;
        align-items: center;
    }
    
    .stat-card-icon {
        font-size: 2.5rem;
        margin-right: 15px;
        opacity: 0.8;
    }
    
    .stat-card-info {
        flex: 1;
    }
    
    .stat-card-value {
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 5px;
        line-height: 1;
    }
    
    .stat-card-title {
        font-size: 0.9rem;
        opacity: 0.8;
    }
    
    .stat-card-footer {
        background: rgba(0, 0, 0, 0.1);
        padding: 10px 20px;
        font-size: 0.8rem;
    }
    
    /* Tarjetas del dashboard */
    .dashboard-card {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border-radius: 10px;
        border: none;
        height: 100%;
    }
    
    .dashboard-card .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .dashboard-card .card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #333;
    }
    
    .card-actions {
        display: flex;
        align-items: center;
    }
    
    /* Lista de pacientes de alto riesgo */
    .high-risk-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .high-risk-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .high-risk-item:last-child {
        border-bottom: none;
    }
    
    .patient-name {
        font-weight: 500;
        margin-bottom: 2px;
    }
    
    .patient-details {
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    /* Lista de conversaciones */
    .conversation-list {
        max-height: 376px;
        overflow-y: auto;
    }
    
    .conversation-item {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .conversation-item:last-child {
        border-bottom: none;
    }
    
    .conversation-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #3498db;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .conversation-info {
        flex: 1;
        min-width: 0;
    }
    
    .conversation-title {
        font-weight: 500;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .conversation-excerpt {
        font-size: 0.8rem;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .conversation-meta {
        font-size: 0.75rem;
        color: #adb5bd;
        margin-top: 2px;
    }
    
    .conversation-actions {
        margin-left: 10px;
    }
    
    /* Tablas */
    .table-responsive {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table td, .table th {
        vertical-align: middle;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .stat-card-body {
            padding: 15px;
        }
        
        .stat-card-icon {
            font-size: 2rem;
            margin-right: 10px;
        }
        
        .stat-card-value {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuración del gráfico de riesgo
        const ctx = document.getElementById('riskChart').getContext('2d');
        const riskChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'],
                datasets: [
                    {
                        label: 'Bajo Riesgo',
                        data: [42, 35, 50, 45, 58],
                        backgroundColor: '#2ecc71'
                    },
                    {
                        label: 'Riesgo Moderado',
                        data: [18, 25, 30, 22, 28],
                        backgroundColor: '#f39c12'
                    },
                    {
                        label: 'Alto Riesgo',
                        data: [8, 12, 15, 10, 12],
                        backgroundColor: '#e74c3c'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
