@extends('layouts.app')

@section('content')
<div class="container">
    <ul class="nav nav-tabs mb-4" id="bloque9TabNav" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque1-tab" href="{{ route('form.show', ['block' => 1, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab">Bloque 1: Caracterización Familiar</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque2-tab" href="{{ route('form.show', ['block' => 2, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab">Bloque 2: Vida Digna</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque3-tab" href="{{ route('form.show', ['block' => 3, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab">Bloque 3: Trabajo Digno</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque4-tab" href="{{ route('form.show', ['block' => 4, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab">Bloque 4: Salud y Bienestar</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque5-tab" href="{{ route('form.show', ['block' => 5, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab">Bloque 5: Protección</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque6-tab" href="{{ route('form.show', ['block' => 6, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab">Bloque 6: Respeto y Comunicación</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque7-tab" href="{{ route('form.show', ['block' => 7, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab">Bloque 7: Participación</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque8-tab" href="{{ route('form.show', ['block' => 8, 'tipo_documento' => request('tipo_documento'), 'numero_documento' => request('numero_documento')]) }}" role="tab">Bloque 8: Representación</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="bloque9-tab" data-bs-toggle="tab" href="#bloque9-tab-pane" role="tab" aria-controls="bloque9-tab-pane" aria-selected="true">Bloque 9: Derechos</a>
        </li>
    </ul>
    <div class="tab-content" id="bloque9TabsContent">
        <div class="tab-pane fade show active" id="bloque9-tab-pane" role="tabpanel" aria-labelledby="bloque9-tab">
            <div class="container">
                <h2 class="mb-4">Bloque 9: Derechos</h2>
                <form method="POST" action="{{ route('form.store') }}" autocomplete="off" id="bloque9Form">
                    @csrf
                    <input type="hidden" name="block" value="9">
                    <input type="hidden" name="tipo_documento" value="{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}">
                    <input type="hidden" name="numero_documento" value="{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}">
                    <input type="hidden" name="profesional_documento" value="{{ session('profesional_documento', old('profesional_documento', isset($registro) ? $registro->profesional_documento : '0')) }}">
                    @if(isset($registro))
                        <input type="hidden" name="es_actualizacion" value="1">
                    @endif
                    <!-- Pregunta 46: Opción múltiple (máx 5) -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">46. ¿En tu núcleo familiar se han vulnerado algunos de los siguientes derechos? <span class="fw-normal">(seleccione máx 5)</span></div>
                        <div class="card-body">
                            @php
                                $p46 = [
                                    'p46_vida_libre_violencia' => old('p46_vida_libre_violencia', $registro->p46_vida_libre_violencia ?? 2),
                                    'p46_participacion_representacion' => old('p46_participacion_representacion', $registro->p46_participacion_representacion ?? 2),
                                    'p46_trabajo_digno' => old('p46_trabajo_digno', $registro->p46_trabajo_digno ?? 2),
                                    'p46_salud_seguridad' => old('p46_salud_seguridad', $registro->p46_salud_seguridad ?? 2),
                                    'p46_educacion_igualdad' => old('p46_educacion_igualdad', $registro->p46_educacion_igualdad ?? 2),
                                    'p46_recreacion_cultura' => old('p46_recreacion_cultura', $registro->p46_recreacion_cultura ?? 2),
                                    'p46_honra_dignidad' => old('p46_honra_dignidad', $registro->p46_honra_dignidad ?? 2),
                                    'p46_igualdad' => old('p46_igualdad', $registro->p46_igualdad ?? 2),
                                    'p46_armonia_unidad' => old('p46_armonia_unidad', $registro->p46_armonia_unidad ?? 2),
                                    'p46_proteccion_asistencia' => old('p46_proteccion_asistencia', $registro->p46_proteccion_asistencia ?? 2),
                                    'p46_entornos_seguros' => old('p46_entornos_seguros', $registro->p46_entornos_seguros ?? 2),
                                    'p46_decidir_hijos' => old('p46_decidir_hijos', $registro->p46_decidir_hijos ?? 2),
                                    'p46_orientacion_asesoria' => old('p46_orientacion_asesoria', $registro->p46_orientacion_asesoria ?? 2),
                                    'p46_respetar_formacion_hijos' => old('p46_respetar_formacion_hijos', $registro->p46_respetar_formacion_hijos ?? 2),
                                    'p46_respeto_reciproco' => old('p46_respeto_reciproco', $registro->p46_respeto_reciproco ?? 2),
                                    'p46_proteccion_patrimonio' => old('p46_proteccion_patrimonio', $registro->p46_proteccion_patrimonio ?? 2),
                                    'p46_alimentacion_necesidades' => old('p46_alimentacion_necesidades', $registro->p46_alimentacion_necesidades ?? 2),
                                    'p46_bienestar' => old('p46_bienestar', $registro->p46_bienestar ?? 2),
                                    'p46_apoyo_estado_mayores' => old('p46_apoyo_estado_mayores', $registro->p46_apoyo_estado_mayores ?? 2),
                                    'p46_ninguno_anteriores' => old('p46_ninguno_anteriores', $registro->p46_ninguno_anteriores ?? 2),
                                ];
                                $opciones = [
                                    'p46_vida_libre_violencia' => '1. Derecho a una vida libre de violencia',
                                    'p46_participacion_representacion' => '2. Derecho a la participación y representación de sus miembros',
                                    'p46_trabajo_digno' => '3. Derecho a un trabajo digno e ingresos justos',
                                    'p46_salud_seguridad' => '4. Derecho a la salud plena y a la seguridad social',
                                    'p46_educacion_igualdad' => '5. Derecho a la educación con igualdad de oportunidades',
                                    'p46_recreacion_cultura' => '6. Derecho a la recreación, cultura y deporte',
                                    'p46_honra_dignidad' => '7. Derecho a la honra, dignidad e intimidad',
                                    'p46_igualdad' => '8. Derecho de igualdad',
                                    'p46_armonia_unidad' => '9. Derecho a la armonía y unidad',
                                    'p46_proteccion_asistencia' => '10. Derecho a recibir protección y asistencia social cuando sus derechos sean vulnerados o amenazados',
                                    'p46_entornos_seguros' => '11. Derecho a vivir en entornos seguros y dignos',
                                    'p46_decidir_hijos' => '12. Derecho a decidir libre y responsablemente el número de hijos',
                                    'p46_orientacion_asesoria' => '13. Derecho a la orientación y asesoría en el afianzamiento de la relación de pareja',
                                    'p46_respetar_formacion_hijos' => '14. Respeto y libertad en la formación de los hijos de acuerdo a sus principios y valores',
                                    'p46_respeto_reciproco' => '15. Derecho al respeto recíproco entre los miembros de la familia',
                                    'p46_proteccion_patrimonio' => '16. Derecho a la protección del patrimonio familiar',
                                    'p46_alimentacion_necesidades' => '17. Derecho a una alimentación que supla sus necesidades básicas',
                                    'p46_bienestar' => '18. Derecho al bienestar físico, mental y emocional',
                                    'p46_apoyo_estado_mayores' => '19. Derecho a recibir apoyo del Estado y la Sociedad para el cuidado y atención de personas adultas mayores',
                                    'p46_ninguno_anteriores' => '20. Ninguno de los anteriores',
                                ];
                            @endphp
                            @foreach($opciones as $campo => $texto)
                                <div class="form-check form-switch">
                                    <input class="form-check-input p46-switch" type="checkbox" name="{{ $campo }}" id="{{ $campo }}" value="1" {{ $p46[$campo] == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="{{ $campo }}">{{ $texto }}</label>
                                </div>
                            @endforeach
                            <div id="maxCheckMsg" class="text-danger mt-2" style="display:none;">Solo puede seleccionar hasta 5 opciones.</div>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <a href="/observatorioapp/public/form/8/{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}/{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}" class="btn btn-secondary btn-lg me-2">
                            <i class="bi bi-arrow-left-circle"></i> Anterior
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg me-2" id="guardarBtn">Guardar</button>
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
    // Lógica para máximo 5 checks y exclusividad de "Ninguno de los anteriores"
    const checkboxes = document.querySelectorAll('.p46-switch');
    const ningunoCheckbox = document.getElementById('p46_ninguno_anteriores');
    const maxCheckMsg = document.getElementById('maxCheckMsg');
    function updateChecks(e) {
        let checked = Array.from(checkboxes).filter(cb => cb.checked && cb.id !== 'p46_ninguno_anteriores');
        // Si seleccionan "Ninguno de los anteriores", deschequea las demás
        if (e && e.target.id === 'p46_ninguno_anteriores' && e.target.checked) {
            checkboxes.forEach(cb => { if(cb.id !== 'p46_ninguno_anteriores') cb.checked = false; });
        }
        // Si seleccionan otra respuesta diferente a 20, se deschequea 20
        if (e && e.target.id !== 'p46_ninguno_anteriores' && e.target.checked) {
            ningunoCheckbox.checked = false;
        }
        // Máximo 5 checks
        checked = Array.from(checkboxes).filter(cb => cb.checked && cb.id !== 'p46_ninguno_anteriores');
        if (checked.length > 5) {
            e.target.checked = false;
            maxCheckMsg.style.display = 'block';
            setTimeout(() => { maxCheckMsg.style.display = 'none'; }, 2000);
        }
    }
    checkboxes.forEach(cb => cb.addEventListener('change', updateChecks));

    // VALIDACIÓN ESTRICTA + SPINNER AL ENVIAR EL FORMULARIO
    const form = document.getElementById('bloque9Form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Pregunta 46 (opción múltiple, mínimo 1, máximo 5)
            const p46Checks = Array.from(document.querySelectorAll('.p46-switch'));
            let p46Marcada = false;
            let checkedCount = 0;
            p46Checks.forEach(chk => { if (chk.checked) { p46Marcada = true; checkedCount++; } });
            if (!p46Marcada) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Debes seleccionar al menos un derecho vulnerado (Pregunta 46).'
                });
                p46Checks[0].focus();
                return;
            }
            if (checkedCount > 5) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Límite excedido',
                    text: 'Solo puedes seleccionar hasta 5 derechos vulnerados (Pregunta 46).'
                });
                return;
            }
            // Si pasa la validación, mostrar spinner
            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    }

    // SweetAlert de éxito
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Guardado exitosamente!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#0c6efd'
        });
    @endif

    // SweetAlert de error si existe error de usuario existente
    @if($errors->has('usuario_existente'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ $errors->first('usuario_existente') }}',
            confirmButtonColor: '#d33'
        });
    @endif
});
</script>
@endsection
