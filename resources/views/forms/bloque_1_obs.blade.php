@extends('layouts.app')
@section('title', 'Bloque 1 OBS: Caracterización Familiar Nueva')
@section('content')
<div class="container">
    <h2 class="mb-4">Bloque 1 OBS: Caracterización Familiar Nueva</h2>
    <form method="POST" action="{{ route('form.bloque1obs.store') }}" autocomplete="off" id="bloque1ObsForm">
        @csrf
        <input type="hidden" name="tipo_documento" value="{{ $tipo_documento ?? ($registro->tipo_documento ?? '') }}">
        <input type="hidden" name="numero_documento" value="{{ $numero_documento ?? ($registro->numero_documento ?? '') }}">
        <input type="hidden" name="profesional_documento" value="{{ $registro->profesional_documento ?? '' }}">
        <!-- Ejemplo de campo obligatorio -->
        <div class="mb-3">
            <label class="form-label"><strong>3.</strong> ¿En qué comuna vive su núcleo familiar?</label>
            <select class="form-select" name="p3_comuna_nucleo_familiar" required>
                <option value="">Seleccione...</option>
                @for ($i = 7; $i <= 27; $i++)
                    <option value="{{ $i }}" {{ (old('p3_comuna_nucleo_familiar', $registro->p3_comuna_nucleo_familiar ?? '') == $i) ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                @endfor
            </select>
            @error('p3_comuna_nucleo_familiar')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <!-- ... Agrega aquí los campos para las preguntas 4 a 16 siguiendo la estructura ... -->
        <!-- Ejemplo de checkboxes para pregunta múltiple -->
        <div class="mb-3">
            <label class="form-label"><strong>7.</strong> ¿Algún miembro del núcleo familiar se identifica con las siguientes orientaciones sexuales?</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="p7_lesbiana" id="p7_lesbiana" value="1" {{ old('p7_lesbiana', $registro->p7_lesbiana ?? '') == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="p7_lesbiana">Lesbiana</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="p7_gay" id="p7_gay" value="1" {{ old('p7_gay', $registro->p7_gay ?? '') == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="p7_gay">Gay</label>
                    </div>
                </div>
                <!-- ... Resto de opciones ... -->
            </div>
            @error('p7_lesbiana')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            timer: 2500,
            showConfirmButton: false
        });
    @endif
    const form = document.getElementById('bloque1ObsForm');
    form.addEventListener('submit', function(e) {
        // Validación amigable: ejemplo para un campo obligatorio
        const campo = form.querySelector('[name="p3_comuna_nucleo_familiar"]');
        if (campo && campo.value === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Debe seleccionar la comuna donde vive el núcleo familiar.'
            });
            campo.focus();
            return false;
        }
        // ... Agrega aquí validaciones adicionales ...
    });
});
</script>
@endsection
