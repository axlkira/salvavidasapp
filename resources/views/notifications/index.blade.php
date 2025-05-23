@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-bell mr-2"></i> Centro de Notificaciones
                    </h5>
                    @if(count($notifications) > 0)
                    <button id="mark-all-read" class="btn btn-sm btn-light">
                        <i class="fas fa-check-double"></i> Marcar todas como leídas
                    </button>
                    @endif
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(count($notifications) === 0)
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                            <h4>No tienes notificaciones</h4>
                            <p class="text-muted">Estás al día con todas tus alertas</p>
                        </div>
                    @else
                        <div class="list-group notification-list">
                            @foreach($notifications as $notification)
                                <a href="{{ route('notifications.show', $notification->id) }}" 
                                   class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'unread' }}"
                                   data-notification-id="{{ $notification->id }}">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <h5 class="mb-1">
                                            @if($notification->type === 'high_risk_detected')
                                                <span class="badge badge-danger mr-2">
                                                    <i class="fas fa-exclamation-triangle"></i> Riesgo Alto
                                                </span>
                                            @elseif($notification->type === 'status_changed')
                                                <span class="badge badge-info mr-2">
                                                    <i class="fas fa-exchange-alt"></i> Estado
                                                </span>
                                            @endif
                                            {{ $notification->title }}
                                        </h5>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">{{ $notification->message }}</p>
                                    
                                    @if(!$notification->read_at)
                                        <span class="badge badge-primary">No leída</span>
                                    @endif
                                    
                                    <div class="mt-2 text-right">
                                        @if($notification->risk_assessment_id)
                                            <a href="{{ route('risk-assessment.show', $notification->risk_assessment_id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Ver evaluación
                                            </a>
                                        @endif
                                        
                                        @if(!$notification->read_at)
                                            <button class="btn btn-sm btn-outline-secondary mark-read-btn" 
                                                    data-notification-id="{{ $notification->id }}">
                                                <i class="fas fa-check"></i> Marcar como leída
                                            </button>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Marcar una notificación como leída
        $('.mark-read-btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const notificationId = $(this).data('notification-id');
            const notificationItem = $(this).closest('.list-group-item');
            
            $.ajax({
                url: `/salvavidasapp/notifications/${notificationId}/mark-as-read`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        notificationItem.removeClass('unread');
                        $(e.target).closest('button').remove();
                        notificationItem.find('.badge-primary').remove();
                        updateNotificationCounter();
                    }
                },
                error: function(error) {
                    console.error('Error al marcar notificación como leída', error);
                }
            });
        });
        
        // Marcar todas como leídas
        $('#mark-all-read').click(function() {
            $.ajax({
                url: '{{ route("notifications.mark-all-as-read") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('.notification-list .list-group-item').removeClass('unread');
                        $('.notification-list .badge-primary').remove();
                        $('.notification-list .mark-read-btn').remove();
                        updateNotificationCounter();
                    }
                },
                error: function(error) {
                    console.error('Error al marcar todas las notificaciones como leídas', error);
                }
            });
        });
        
        // Actualizar contador de notificaciones en el sidebar
        function updateNotificationCounter() {
            // Si existe un contador de notificaciones en el sidebar, actualizarlo
            const counter = $('#notification-counter');
            if (counter.length) {
                $.get('{{ route("notifications.unread") }}', function(data) {
                    if (data.count > 0) {
                        counter.text(data.count).show();
                    } else {
                        counter.text('0').hide();
                    }
                });
            }
        }
    });
</script>
@endsection

@section('styles')
<style>
    .notification-list .list-group-item.unread {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }
    
    .notification-list .list-group-item {
        margin-bottom: 10px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    
    .notification-list .list-group-item:hover {
        background-color: #f1f1f1;
    }
    
    .notification-list .badge-danger {
        background-color: #dc3545;
    }
    
    .notification-list .badge-info {
        background-color: #17a2b8;
    }
</style>
@endsection
