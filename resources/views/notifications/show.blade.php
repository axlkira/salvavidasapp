@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        @if($notification->type === 'high_risk_detected')
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                        @elseif($notification->type === 'status_changed')
                            <i class="fas fa-exchange-alt mr-2"></i>
                        @else
                            <i class="fas fa-bell mr-2"></i>
                        @endif
                        {{ $notification->title }}
                    </h5>
                </div>

                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <p class="mb-0">{{ $notification->message }}</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Fecha:</strong> {{ $notification->created_at->format('d/m/Y H:i:s') }}</p>
                            <p><strong>Estado:</strong> 
                                @if($notification->read_at)
                                    <span class="badge badge-success">Leída el {{ $notification->read_at->format('d/m/Y H:i:s') }}</span>
                                @else
                                    <span class="badge badge-warning">No leída</span>
                                @endif
                            </p>
                            <p><strong>Tipo:</strong> 
                                @if($notification->type === 'high_risk_detected')
                                    <span class="badge badge-danger">Alerta de Riesgo Alto</span>
                                @elseif($notification->type === 'status_changed')
                                    <span class="badge badge-info">Cambio de Estado</span>
                                @else
                                    <span class="badge badge-secondary">{{ $notification->type }}</span>
                                @endif
                            </p>
                        </div>
                        
                        @if($notification->risk_assessment_id)
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Información de la Evaluación</h6>
                                    @if(isset($notification->data['risk_level']))
                                        <p><strong>Nivel de riesgo:</strong> 
                                            <span class="badge 
                                                @if($notification->data['risk_level'] === 'alto' || $notification->data['risk_level'] === 'crítico')
                                                    badge-danger
                                                @elseif($notification->data['risk_level'] === 'medio')
                                                    badge-warning
                                                @else
                                                    badge-success
                                                @endif
                                            ">
                                                {{ ucfirst($notification->data['risk_level']) }}
                                            </span>
                                        </p>
                                    @endif
                                    
                                    @if(isset($notification->data['risk_score']))
                                        <p><strong>Puntuación:</strong> {{ $notification->data['risk_score'] }}</p>
                                    @endif
                                    
                                    @if(isset($notification->data['patient_name']))
                                        <p><strong>Paciente:</strong> {{ $notification->data['patient_name'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Volver a notificaciones
                        </a>
                        
                        @if($notification->risk_assessment_id)
                            <a href="{{ route('risk-assessment.show', $notification->risk_assessment_id) }}" class="btn btn-primary">
                                <i class="fas fa-eye mr-1"></i> Ver evaluación de riesgo
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
