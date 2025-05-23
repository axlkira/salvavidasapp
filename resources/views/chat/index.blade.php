@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-comments text-primary"></i> Conversaciones con IA</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newConversationModal">
                    <i class="fas fa-plus"></i> Nueva conversación
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if(count($conversations) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Paciente</th>
                                        <th>Modelo</th>
                                        <th>Última actualización</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conversations as $conversation)
                                    <tr>
                                        <td>{{ $conversation->title }}</td>
                                        <td>
                                            @if($conversation->patient_document)
                                                {{ $conversation->patient ? $conversation->patient->nombre . ' ' . $conversation->patient->apellidos : 'Paciente no encontrado' }}
                                            @else
                                                <span class="text-muted">Sin paciente</span>
                                            @endif
                                        </td>
                                        <td><span class="badge badge-info">{{ $conversation->model }}</span></td>
                                        <td>{{ $conversation->updated_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('chat.show', ['id' => $conversation->id]) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-comments"></i> Continuar
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/1041/1041916.png" alt="No conversations" width="80">
                            <h4 class="mt-3">No hay conversaciones</h4>
                            <p class="text-muted">Crea una nueva conversación para comenzar a hablar con la IA</p>
                            <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#newConversationModal">
                                <i class="fas fa-plus"></i> Nueva conversación
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nueva conversación -->
<div class="modal fade" id="newConversationModal" tabindex="-1" role="dialog" aria-labelledby="newConversationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newConversationModalLabel">Nueva conversación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('chat.create') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Título de la conversación</label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="Ej: Consulta sobre paciente con ideación suicida">
                    </div>
                    <div class="form-group">
                        <label for="patient_document">Documento del paciente (opcional)</label>
                        <input type="text" class="form-control" id="patient_document" name="patient_document" placeholder="Documento de identidad">
                        <small class="form-text text-muted">Deja en blanco si no está asociado a un paciente específico</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear conversación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        border-radius: 10px;
    }
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>
    // Mostrar el modal automáticamente si hay un error en la creación
    @if($errors->any())
        $(document).ready(function(){
            var myModal = new bootstrap.Modal(document.getElementById('newConversationModal'));
            myModal.show();
        });
    @endif
</script>
@endpush
