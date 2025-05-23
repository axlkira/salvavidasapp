@extends('layouts.app')

@section('content')
<div class="container">
    <ul class="nav nav-tabs mb-4" id="bloque4TabNav" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque1-tab" href="{{ route('form.show', ['block' => 1, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab" aria-controls="bloque1-tab-pane" aria-selected="false">
                Bloque 1: Caracterización Familiar
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque2-tab" href="{{ route('form.show', ['block' => 2, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab" aria-controls="bloque2-tab-pane" aria-selected="false">
                Bloque 2: Vida Digna
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque3-tab" href="{{ route('form.show', ['block' => 3, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab" aria-controls="bloque3-tab-pane" aria-selected="false">
                Bloque 3: Trabajo Digno
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="bloque4-tab" data-bs-toggle="tab" href="#bloque4-tab-pane" role="tab" aria-controls="bloque4-tab-pane" aria-selected="true">
                Bloque 4: Salud y Bienestar
            </a>
        </li>
    </ul>
    <div class="tab-content" id="bloque4TabsContent">
        <div class="tab-pane fade show active" id="bloque4-tab-pane" role="tabpanel" aria-labelledby="bloque4-tab">
            <div class="container">
                <h2 class="mb-4">Bloque 4: Salud y Bienestar</h2>
                <form method="POST" action="{{ route('form.store') }}" autocomplete="off" id="bloque4Form">
                    @csrf
                    <input type="hidden" name="block" value="4">
                    <input type="hidden" name="tipo_documento" value="{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}">
                    <input type="hidden" name="numero_documento" value="{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}">
                    <input type="hidden" name="profesional_documento" value="{{ session('profesional_documento', old('profesional_documento', isset($registro) ? $registro->profesional_documento : '0')) }}">
                    @if(isset($registro))
                        <input type="hidden" name="es_actualizacion" value="1">
                    @endif
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">27. ¿Cuál es el sistema de salud al que está afiliado la mayoría de los integrantes de tu núcleo familiar?</div>
                        <div class="card-body">
                            <select class="form-select" name="sistema_salud" required>
                                <option value="">Seleccione...</option>
                                <option value="139" {{ old('sistema_salud', $registro->sistema_salud ?? '') == 139 ? 'selected' : '' }}>Régimen Subsidiado (Sisben)</option>
                                <option value="140" {{ old('sistema_salud', $registro->sistema_salud ?? '') == 140 ? 'selected' : '' }}>Régimen Contributivo (Cotizante)</option>
                                <option value="141" {{ old('sistema_salud', $registro->sistema_salud ?? '') == 141 ? 'selected' : '' }}>Régimen Contributivo (Beneficiario)</option>
                                <option value="142" {{ old('sistema_salud', $registro->sistema_salud ?? '') == 142 ? 'selected' : '' }}>Régimen Especial y de excepción (fuerzas militares, policía nacional, ecopetrol magisterio, universidades públicas)</option>
                                <option value="42" {{ old('sistema_salud', $registro->sistema_salud ?? '') == 42 ? 'selected' : '' }}>Ninguno</option>
                            </select>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">28. ¿Algún miembro de tu núcleo familiar ha sido diagnosticado con alguna enfermedad mental, como depresión, ansiedad, trastornos del estado de ánimo, entre otros?</div>
                        <div class="card-body">
                            <select class="form-select" name="enfermedad_mental" required>
                                <option value="">Seleccione...</option>
                                <option value="1" {{ old('enfermedad_mental', $registro->enfermedad_mental ?? '') == 1 ? 'selected' : '' }}>Sí</option>
                                <option value="2" {{ old('enfermedad_mental', $registro->enfermedad_mental ?? '') == 2 ? 'selected' : '' }}>No</option>
                                <option value="44" {{ old('enfermedad_mental', $registro->enfermedad_mental ?? '') == 44 ? 'selected' : '' }}>No sabe</option>
                                <option value="92" {{ old('enfermedad_mental', $registro->enfermedad_mental ?? '') == 92 ? 'selected' : '' }}>No responde</option>
                            </select>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">29. ¿En tu núcleo familiar acceden a espacios recreativos y deportivos como canchas, gimnasios al aire libre, teatro, cine, danzas, conciertos, bibliotecas, entre otros?</div>
                        <div class="card-body">
                            <select class="form-select" name="acceso_espacios_recreativos" required>
                                <option value="">Seleccione...</option>
                                <option value="103" {{ old('acceso_espacios_recreativos', $registro->acceso_espacios_recreativos ?? '') == 103 ? 'selected' : '' }}>Frecuentemente</option>
                                <option value="143" {{ old('acceso_espacios_recreativos', $registro->acceso_espacios_recreativos ?? '') == 143 ? 'selected' : '' }}>A veces</option>
                                <option value="144" {{ old('acceso_espacios_recreativos', $registro->acceso_espacios_recreativos ?? '') == 144 ? 'selected' : '' }}>Rara vez</option>
                                <option value="106" {{ old('acceso_espacios_recreativos', $registro->acceso_espacios_recreativos ?? '') == 106 ? 'selected' : '' }}>Nunca</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <a href="/observatorioapp/public/form/3/{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}/{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}" class="btn btn-secondary btn-lg me-2">
                            <i class="bi bi-arrow-left-circle"></i> Anterior
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg me-2">Guardar</button>
                        <a href="/observatorioapp/public/form/5/{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}/{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}"
                           class="btn btn-success btn-lg{{ empty($registro) ? ' disabled' : '' }}"
                           tabindex="{{ empty($registro) ? '-1' : '0' }}"
                           aria-disabled="{{ empty($registro) ? 'true' : 'false' }}">
                            Siguiente <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar SweetAlert de éxito si existe mensaje en sesión
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Guardado exitosamente!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#0c6efd'
        });
    @endif

    // Mostrar SweetAlert de error si existe error de usuario existente
    @if($errors->has('usuario_existente'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ $errors->first('usuario_existente') }}',
            confirmButtonColor: '#d33'
        });
    @endif

    // Spinner y deshabilitar botón al guardar
    const form = document.getElementById('bloque4Form');
    if (form) {
        form.addEventListener('submit', function(e) {
            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        });
    }
});
</script>
@endsection
