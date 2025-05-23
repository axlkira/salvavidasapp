@extends('layouts.app')

@section('content')
<div class="container">
    <ul class="nav nav-tabs mb-4" id="bloque7TabNav" role="tablist">
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
            <a class="nav-link active" id="bloque7-tab" data-bs-toggle="tab" href="#bloque7-tab-pane" role="tab" aria-controls="bloque7-tab-pane" aria-selected="true">Bloque 7: Participación</a>
        </li>
    </ul>
    <div class="tab-content" id="bloque7TabsContent">
        <div class="tab-pane fade show active" id="bloque7-tab-pane" role="tabpanel" aria-labelledby="bloque7-tab">
            <div class="container">
                <h2 class="mb-4">Bloque 7: Participación</h2>
                <form method="POST" action="{{ route('form.store') }}" autocomplete="off" id="bloque7Form">
                    @csrf
                    <input type="hidden" name="block" value="7">
                    <input type="hidden" name="tipo_documento" value="{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}">
                    <input type="hidden" name="numero_documento" value="{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}">
                    <input type="hidden" name="profesional_documento" value="{{ session('profesional_documento', old('profesional_documento', isset($registro) ? $registro->profesional_documento : '0')) }}">
                    @if(isset($registro))
                        <input type="hidden" name="es_actualizacion" value="1">
                    @endif
                    <!-- Pregunta 40 -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">40. En tu núcleo familiar, ¿todos los miembros tienen igualdad de oportunidades con relación a educación, salud, trabajo, independientemente de su género, etnia, edad, religión, orientación sexual, discapacidad, estrato?</div>
                        <div class="card-body">
                            <select class="form-select" name="p40_igualdad_oportunidades" required>
                                <option value="">Seleccione...</option>
                                <option value="196" {{ old('p40_igualdad_oportunidades', $registro->p40_igualdad_oportunidades ?? '') == 196 ? 'selected' : '' }}>1. Sí, todos tienen igualdad de oportunidades</option>
                                <option value="197" {{ old('p40_igualdad_oportunidades', $registro->p40_igualdad_oportunidades ?? '') == 197 ? 'selected' : '' }}>2. No siempre, algunos integrantes enfrentan más barreras que otros</option>
                                <option value="198" {{ old('p40_igualdad_oportunidades', $registro->p40_igualdad_oportunidades ?? '') == 198 ? 'selected' : '' }}>3. No, hay desigualdades significativas dentro del núcleo familiar</option>
                                <option value="44" {{ old('p40_igualdad_oportunidades', $registro->p40_igualdad_oportunidades ?? '') == 44 ? 'selected' : '' }}>4. No sabe</option>
                            </select>
                        </div>
                    </div>
                    <!-- Pregunta 41 -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">41. ¿En tu núcleo familiar las posturas políticas, sociales, económicas, religiosas y opiniones son valoradas por igual?</div>
                        <div class="card-body">
                            <select class="form-select" name="p41_valoracion_posturas" required>
                                <option value="">Seleccione...</option>
                                <option value="199" {{ old('p41_valoracion_posturas', $registro->p41_valoracion_posturas ?? '') == 199 ? 'selected' : '' }}>1. Sí, todas las posturas y opiniones son valoradas</option>
                                <option value="200" {{ old('p41_valoracion_posturas', $registro->p41_valoracion_posturas ?? '') == 200 ? 'selected' : '' }}>2. No siempre, algunas posturas y opiniones son tenidas en cuenta más que otras dependiendo de las situaciones</option>
                                <option value="201" {{ old('p41_valoracion_posturas', $registro->p41_valoracion_posturas ?? '') == 201 ? 'selected' : '' }}>3. No, las posturas y opiniones no son valoradas</option>
                                <option value="44" {{ old('p41_valoracion_posturas', $registro->p41_valoracion_posturas ?? '') == 44 ? 'selected' : '' }}>4. No sabe</option>
                            </select>
                        </div>
                    </div>
                    <!-- Pregunta 42 -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">42. ¿Quiénes realizan principalmente trabajos de cuidado en tu núcleo familiar?</div>
                        <div class="card-body">
                            <select class="form-select" name="p42_trabajos_cuidado" required>
                                <option value="">Seleccione...</option>
                                <option value="202" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 202 ? 'selected' : '' }}>1. Pareja</option>
                                <option value="203" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 203 ? 'selected' : '' }}>2. Madre y Padre</option>
                                <option value="204" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 204 ? 'selected' : '' }}>3. Madre</option>
                                <option value="205" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 205 ? 'selected' : '' }}>4. Padre</option>
                                <option value="206" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 206 ? 'selected' : '' }}>5. Hijos/as</option>
                                <option value="207" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 207 ? 'selected' : '' }}>6. Abuelos/as</option>
                                <option value="208" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 208 ? 'selected' : '' }}>7. Todos los integrantes de la familia</option>
                                <option value="209" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 209 ? 'selected' : '' }}>8. Solo yo (Hogar unipersonal)</option>
                                <option value="210" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 210 ? 'selected' : '' }}>9. Hermanos/as</option>
                                <option value="211" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 211 ? 'selected' : '' }}>10. Amigos/as</option>
                                <option value="212" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 212 ? 'selected' : '' }}>11. Otros familiares</option>
                                <option value="213" {{ old('p42_trabajos_cuidado', $registro->p42_trabajos_cuidado ?? '') == 213 ? 'selected' : '' }}>12. Trabajador/a doméstico/a</option>
                            </select>
                        </div>
                    </div>
                    <!-- Pregunta 43 -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">43. Si en el núcleo familiar hay personas mayores ¿Qué tipo de apoyo consideran que debería proporcionar el Estado y la sociedad? (opción múltiple)</div>
                        <div class="card-body">
                            @php
                                $p43 = [
                                    'p43_subsidios_economicos' => old('p43_subsidios_economicos', $registro->p43_subsidios_economicos ?? 2),
                                    'p43_acceso_centros_cuidado' => old('p43_acceso_centros_cuidado', $registro->p43_acceso_centros_cuidado ?? 2),
                                    'p43_atencion_medica' => old('p43_atencion_medica', $registro->p43_atencion_medica ?? 2),
                                    'p43_capacitacion_cuidadores' => old('p43_capacitacion_cuidadores', $registro->p43_capacitacion_cuidadores ?? 2),
                                    'p43_paquetes_alimentarios' => old('p43_paquetes_alimentarios', $registro->p43_paquetes_alimentarios ?? 2),
                                    'p43_redes_apoyo_cuidadores' => old('p43_redes_apoyo_cuidadores', $registro->p43_redes_apoyo_cuidadores ?? 2),
                                    'p43_incentivos_economicos' => old('p43_incentivos_economicos', $registro->p43_incentivos_economicos ?? 2),
                                    'p43_ninguno' => old('p43_ninguno', $registro->p43_ninguno ?? 2),
                                    'p43_no_aplica' => old('p43_no_aplica', $registro->p43_no_aplica ?? 2),
                                ];
                            @endphp
                            <div class="form-check form-switch">
                                <input class="form-check-input p43-switch" type="checkbox" name="p43_subsidios_economicos" id="p43_subsidios_economicos" value="1" {{ $p43['p43_subsidios_economicos'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="p43_subsidios_economicos">1. Subsidios económicos para las familias</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input p43-switch" type="checkbox" name="p43_acceso_centros_cuidado" id="p43_acceso_centros_cuidado" value="1" {{ $p43['p43_acceso_centros_cuidado'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="p43_acceso_centros_cuidado">2. Acceso a centros de cuidado</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input p43-switch" type="checkbox" name="p43_atencion_medica" id="p43_atencion_medica" value="1" {{ $p43['p43_atencion_medica'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="p43_atencion_medica">3. Atención médica especializada y en casa</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input p43-switch" type="checkbox" name="p43_capacitacion_cuidadores" id="p43_capacitacion_cuidadores" value="1" {{ $p43['p43_capacitacion_cuidadores'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="p43_capacitacion_cuidadores">4. Capacitación para cuidadores</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input p43-switch" type="checkbox" name="p43_paquetes_alimentarios" id="p43_paquetes_alimentarios" value="1" {{ $p43['p43_paquetes_alimentarios'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="p43_paquetes_alimentarios">5. Paquetes alimentarios</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input p43-switch" type="checkbox" name="p43_redes_apoyo_cuidadores" id="p43_redes_apoyo_cuidadores" value="1" {{ $p43['p43_redes_apoyo_cuidadores'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="p43_redes_apoyo_cuidadores">6. Redes de apoyo para cuidadores</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input p43-switch" type="checkbox" name="p43_incentivos_economicos" id="p43_incentivos_economicos" value="1" {{ $p43['p43_incentivos_economicos'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="p43_incentivos_economicos">7. Incentivos económicos para cuidadores</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input p43-switch" type="checkbox" name="p43_ninguno" id="p43_ninguno" value="1" {{ $p43['p43_ninguno'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="p43_ninguno">8. Ninguno</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input p43-switch" type="checkbox" name="p43_no_aplica" id="p43_no_aplica" value="1" {{ $p43['p43_no_aplica'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="p43_no_aplica">9. No aplica</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <a href="/observatorioapp/public/form/6/{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}/{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}" class="btn btn-secondary btn-lg me-2">
                            <i class="bi bi-arrow-left-circle"></i> Anterior
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg me-2" id="guardarBtn">
                            Guardar
                        </button>
                        <a href="/observatorioapp/public/form/8/{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}/{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}"
                           id="siguienteBtn"
                           class="btn btn-success btn-lg"
                           @if(!isset($registro)) disabled style="pointer-events: none; opacity: 0.6;" @endif>
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
    // Exclusividad de switches en pregunta 43 (opción múltiple)
    function exclusividadP43() {
        const checkboxes = document.querySelectorAll('.p43-switch');
        const ninguno = document.getElementById('p43_ninguno');
        const noaplica = document.getElementById('p43_no_aplica');
        checkboxes.forEach(chk => {
            chk.addEventListener('change', function() {
                if (this.checked && (this.id == 'p43_ninguno' || this.id == 'p43_no_aplica')) {
                    checkboxes.forEach(cb => {
                        if (cb !== this) cb.checked = false;
                    });
                } else if (this.checked) {
                    ninguno.checked = false;
                    noaplica.checked = false;
                }
            });
        });
    }
    exclusividadP43();

    // Activar botón Siguiente si hay registro guardado
    const siguienteBtn = document.getElementById('siguienteBtn');
    @if(isset($registro))
        if (siguienteBtn) {
            siguienteBtn.removeAttribute('disabled');
            siguienteBtn.style.pointerEvents = 'auto';
            siguienteBtn.style.opacity = '1';
        }
    @endif

    // VALIDACIÓN ESTRICTA AL ENVIAR EL FORMULARIO
    document.getElementById('bloque7Form').addEventListener('submit', function(e) {
        // Pregunta 40
        const p40 = document.querySelector('[name="p40_igualdad_oportunidades"]');
        if (!p40.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Debes seleccionar una opción en la pregunta 40.'
            });
            p40.focus();
            return;
        }
        // Pregunta 41
        const p41 = document.querySelector('[name="p41_valoracion_posturas"]');
        if (!p41.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Debes seleccionar una opción en la pregunta 41.'
            });
            p41.focus();
            return;
        }
        // Pregunta 42
        const p42 = document.querySelector('[name="p42_trabajos_cuidado"]');
        if (!p42.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Debes seleccionar una opción en la pregunta 42.'
            });
            p42.focus();
            return;
        }
        // Pregunta 43 (opción múltiple)
        const p43Checks = document.querySelectorAll('.p43-switch');
        let p43Marcada = false;
        p43Checks.forEach(chk => { if (chk.checked) p43Marcada = true; });
        if (!p43Marcada) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Debes seleccionar al menos una opción en la pregunta 43.'
            });
            p43Checks[0].focus();
            return;
        }
    });
});
</script>
@endsection
