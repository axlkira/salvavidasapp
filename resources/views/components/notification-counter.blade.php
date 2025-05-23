<a href="{{ route('notifications.index') }}" class="nav-link d-flex align-items-center">
    <i class="fas fa-bell mr-2"></i> Notificaciones
    @if($unreadCount > 0)
        <span id="notification-counter" class="badge badge-danger ml-auto">{{ $unreadCount }}</span>
    @else
        <span id="notification-counter" class="badge badge-danger ml-auto" style="display: none;">0</span>
    @endif
</a>

@push('scripts')
<script>
    // Función para actualizar el contador de notificaciones
    function updateNotificationCounter() {
        $.ajax({
            url: '{{ route("notifications.unread") }}',
            type: 'GET',
            success: function(response) {
                const counter = $('#notification-counter');
                if (response.count > 0) {
                    counter.text(response.count).show();
                } else {
                    counter.text('0').hide();
                }
            },
            error: function(error) {
                console.error('Error al obtener notificaciones', error);
            }
        });
    }

    // Actualizar el contador cada 60 segundos
    $(document).ready(function() {
        // Actualización inicial
        updateNotificationCounter();
        
        // Configurar actualización periódica
        setInterval(updateNotificationCounter, 60000); // 60 segundos
    });
</script>
@endpush
