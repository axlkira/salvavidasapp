@extends('layouts.app')

@section('content')
<div class="container">
    <ul class="nav nav-tabs mb-4" id="bloque5TabNav" role="tablist">
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
            <a class="nav-link active" id="bloque5-tab" data-bs-toggle="tab" href="#bloque5-tab-pane" role="tab" aria-controls="bloque5-tab-pane" aria-selected="true">Bloque 5: Protección</a>
        </li>
    </ul>
    <div class="tab-content" id="bloque5TabsContent">
        <div class="tab-pane fade show active" id="bloque5-tab-pane" role="tabpanel" aria-labelledby="bloque5-tab">
            <div class="container">
                <h2 class="mb-4">Bloque 5: Protección</h2>
                <form method="POST" action="{{ route('form.store') }}" autocomplete="off" id="bloque5Form">
                    @csrf
                    <input type="hidden" name="block" value="5">
                    <input type="hidden" name="tipo_documento" value="{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}">
                    <input type="hidden" name="numero_documento" value="{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}">
                    <input type="hidden" name="profesional_documento" value="{{ session('profesional_documento', old('profesional_documento', isset($registro) ? $registro->profesional_documento : '0')) }}">
                    @if(isset($registro))
                        <input type="hidden" name="es_actualizacion" value="1">
                    @endif
                    <!-- Pregunta 30 -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">30. La vivienda que actualmente habitan es:</div>
                        <div class="card-body">
                            <select class="form-select" name="p30_vivienda" id="p30_vivienda" required>
                                <option value="">Seleccione...</option>
                                <option value="145" {{ old('p30_vivienda', $registro->p30_vivienda ?? '') == 145 ? 'selected' : '' }}>Propia</option>
                                <option value="146" {{ old('p30_vivienda', $registro->p30_vivienda ?? '') == 146 ? 'selected' : '' }}>Arrendada</option>
                                <option value="147" {{ old('p30_vivienda', $registro->p30_vivienda ?? '') == 147 ? 'selected' : '' }}>Heredada</option>
                                <option value="148" {{ old('p30_vivienda', $registro->p30_vivienda ?? '') == 148 ? 'selected' : '' }}>Cedida (prestada)</option>
                                <option value="149" {{ old('p30_vivienda', $registro->p30_vivienda ?? '') == 149 ? 'selected' : '' }}>Familiar</option>
                                <option value="150" {{ old('p30_vivienda', $registro->p30_vivienda ?? '') == 150 ? 'selected' : '' }}>Inquilinato</option>
                                <option value="151" {{ old('p30_vivienda', $registro->p30_vivienda ?? '') == 151 ? 'selected' : '' }}>Albergue</option>
                                <option value="152" {{ old('p30_vivienda', $registro->p30_vivienda ?? '') == 152 ? 'selected' : '' }}>Otro</option>
                            </select>
                            <div class="mt-3" id="p30_vivienda_otro_div" style="display: none;">
                                <label for="p30_vivienda_otro" class="form-label">¿Cuál?</label>
                                <input type="text" class="form-control" name="p30_vivienda_otro" id="p30_vivienda_otro" value="{{ old('p30_vivienda_otro', $registro->p30_vivienda_otro ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <!-- Pregunta 31 -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">31. ¿Tu núcleo familiar ha considerado migrar debido a razones de seguridad o en busca de una mejor calidad de vida?</div>
                        <div class="card-body">
                            <select class="form-select" name="p31_migracion" required>
                                <option value="">Seleccione...</option>
                                <option value="153" {{ old('p31_migracion', $registro->p31_migracion ?? '') == 153 ? 'selected' : '' }}>Sí, por razones de seguridad</option>
                                <option value="154" {{ old('p31_migracion', $registro->p31_migracion ?? '') == 154 ? 'selected' : '' }}>Sí, para mejorar la calidad de vida</option>
                                <option value="155" {{ old('p31_migracion', $registro->p31_migracion ?? '') == 155 ? 'selected' : '' }}>Sí, por ambas razones</option>
                                <option value="156" {{ old('p31_migracion', $registro->p31_migracion ?? '') == 156 ? 'selected' : '' }}>No, nunca lo hemos considerado</option>
                            </select>
                        </div>
                    </div>
                    <!-- Pregunta 32 -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">32. Cuando en tu núcleo familiar se han presentado casos de vulneración de derechos o amenazas en el contexto familiar o por conflicto armado, ¿cuál de las siguientes opciones ha servido como red de apoyo para tramitarlas? (opción múltiple)</div>
                        <div class="card-body">
                            @php
                                $red_apoyo = [
                                    'red_apoyo_estado' => old('red_apoyo_estado', $registro->red_apoyo_estado ?? 0),
                                    'red_apoyo_organizaciones_internacionales' => old('red_apoyo_organizaciones_internacionales', $registro->red_apoyo_organizaciones_internacionales ?? 0),
                                    'red_apoyo_organizaciones_no_gubernamentales' => old('red_apoyo_organizaciones_no_gubernamentales', $registro->red_apoyo_organizaciones_no_gubernamentales ?? 0),
                                    'red_apoyo_iglesia' => old('red_apoyo_iglesia', $registro->red_apoyo_iglesia ?? 0),
                                    'red_apoyo_amigos' => old('red_apoyo_amigos', $registro->red_apoyo_amigos ?? 0),
                                    'red_apoyo_vecinos' => old('red_apoyo_vecinos', $registro->red_apoyo_vecinos ?? 0),
                                    'red_apoyo_otros_familiares' => old('red_apoyo_otros_familiares', $registro->red_apoyo_otros_familiares ?? 0),
                                    'red_apoyo_otros' => old('red_apoyo_otros', $registro->red_apoyo_otros ?? 0),
                                    'red_apoyo_no_hemos_tenido' => old('red_apoyo_no_hemos_tenido', $registro->red_apoyo_no_hemos_tenido ?? 0),
                                    'red_apoyo_no_aplica' => old('red_apoyo_no_aplica', $registro->red_apoyo_no_aplica ?? 0),
                                ];
                            @endphp
                            <div class="form-check form-switch">
                                <input class="form-check-input red-apoyo-switch" type="checkbox" name="red_apoyo_estado" id="red_apoyo_estado" value="1" {{ $red_apoyo['red_apoyo_estado'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="red_apoyo_estado">Estado</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input red-apoyo-switch" type="checkbox" name="red_apoyo_organizaciones_internacionales" id="red_apoyo_organizaciones_internacionales" value="1" {{ $red_apoyo['red_apoyo_organizaciones_internacionales'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="red_apoyo_organizaciones_internacionales">Organizaciones Internacionales</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input red-apoyo-switch" type="checkbox" name="red_apoyo_organizaciones_no_gubernamentales" id="red_apoyo_organizaciones_no_gubernamentales" value="1" {{ $red_apoyo['red_apoyo_organizaciones_no_gubernamentales'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="red_apoyo_organizaciones_no_gubernamentales">Organizaciones No gubernamentales</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input red-apoyo-switch" type="checkbox" name="red_apoyo_iglesia" id="red_apoyo_iglesia" value="1" {{ $red_apoyo['red_apoyo_iglesia'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="red_apoyo_iglesia">La Iglesia</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input red-apoyo-switch" type="checkbox" name="red_apoyo_amigos" id="red_apoyo_amigos" value="1" {{ $red_apoyo['red_apoyo_amigos'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="red_apoyo_amigos">Amigos</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input red-apoyo-switch" type="checkbox" name="red_apoyo_vecinos" id="red_apoyo_vecinos" value="1" {{ $red_apoyo['red_apoyo_vecinos'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="red_apoyo_vecinos">Vecinos</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input red-apoyo-switch" type="checkbox" name="red_apoyo_otros_familiares" id="red_apoyo_otros_familiares" value="1" {{ $red_apoyo['red_apoyo_otros_familiares'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="red_apoyo_otros_familiares">Otros Familiares</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input red-apoyo-switch" type="checkbox" name="red_apoyo_otros" id="red_apoyo_otros" value="1" {{ $red_apoyo['red_apoyo_otros'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="red_apoyo_otros">Otros</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input red-apoyo-switch" type="checkbox" name="red_apoyo_no_hemos_tenido" id="red_apoyo_no_hemos_tenido" value="1" {{ $red_apoyo['red_apoyo_no_hemos_tenido'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="red_apoyo_no_hemos_tenido">No hemos tenido red de apoyo</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input red-apoyo-switch" type="checkbox" name="red_apoyo_no_aplica" id="red_apoyo_no_aplica" value="1" {{ $red_apoyo['red_apoyo_no_aplica'] == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="red_apoyo_no_aplica">No Aplica</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <a href="/observatorioapp/public/form/4/{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}/{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}" class="btn btn-secondary btn-lg me-2">
                            <i class="bi bi-arrow-left-circle"></i> Anterior
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg me-2" id="guardarBtn">
                            Guardar
                        </button>
                        <a href="/observatorioapp/public/form/6/{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}/{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}"
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
    // Mostrar/ocultar campo "¿Cuál?" en vivienda
    function toggleOtroVivienda() {
        const select = document.getElementById('p30_vivienda');
        const otroDiv = document.getElementById('p30_vivienda_otro_div');
        if (select.value == '152') {
            otroDiv.style.display = 'block';
            document.getElementById('p30_vivienda_otro').required = true;
        } else {
            otroDiv.style.display = 'none';
            document.getElementById('p30_vivienda_otro').required = false;
            document.getElementById('p30_vivienda_otro').value = '';
        }
    }
    document.getElementById('p30_vivienda').addEventListener('change', toggleOtroVivienda);
    toggleOtroVivienda();

    // Exclusividad de switches en red de apoyo
    function exclusividadRedApoyo() {
        const checkboxes = document.querySelectorAll('.red-apoyo-switch');
        const ninguna = document.getElementById('red_apoyo_no_hemos_tenido');
        const noaplica = document.getElementById('red_apoyo_no_aplica');
        checkboxes.forEach(chk => {
            chk.addEventListener('change', function() {
                if (this.checked && (this.id == 'red_apoyo_no_hemos_tenido' || this.id == 'red_apoyo_no_aplica')) {
                    checkboxes.forEach(cb => {
                        if (cb !== this) cb.checked = false;
                    });
                } else if (this.checked) {
                    ninguna.checked = false;
                    noaplica.checked = false;
                }
            });
        });
    }
    exclusividadRedApoyo();

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
    document.getElementById('bloque5Form').addEventListener('submit', function(e) {
        // Pregunta 30
        const vivienda = document.getElementById('p30_vivienda');
        if (!vivienda.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Debes seleccionar el tipo de vivienda (Pregunta 30).'
            });
            vivienda.focus();
            return;
        }
        // Si elige "Otro", debe llenar el campo adicional
        if (vivienda.value == '152') {
            const viviendaOtro = document.getElementById('p30_vivienda_otro');
            if (!viviendaOtro.value.trim()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Debes especificar el tipo de vivienda en "¿Cuál?" (Pregunta 30).'
                });
                viviendaOtro.focus();
                return;
            }
        }
        // Pregunta 31
        const migracion = document.querySelector('[name="p31_migracion"]');
        if (!migracion.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Debes seleccionar una opción en la pregunta 31.'
            });
            migracion.focus();
            return;
        }
        // Pregunta 32 (opción múltiple)
        const redApoyoChecks = document.querySelectorAll('.red-apoyo-switch');
        let algunaMarcada = false;
        redApoyoChecks.forEach(chk => { if (chk.checked) algunaMarcada = true; });
        if (!algunaMarcada) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Debes seleccionar al menos una red de apoyo (Pregunta 32).'
            });
            redApoyoChecks[0].focus();
            return;
        }
    });
});
</script>
@endsection
