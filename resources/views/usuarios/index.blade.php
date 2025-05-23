@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Usuarios gestionados</h2>
    <table id="usuariosTable" class="table table-bordered table-hover table-striped align-middle">
        <thead class="table-primary">
            <tr>
                <th>Tipo de documento</th>
                <th>Número de documento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->tipo_documento }}</td>
                    <td>{{ $usuario->numero_documento }}</td>
                    <td>
                        <a href="{{ url('form/1/' . $usuario->tipo_documento . '/' . $usuario->numero_documento) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#usuariosTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            responsive: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
            columnDefs: [
                { orderable: false, targets: 2 }
            ]
        });
    });
</script>
@endsection
