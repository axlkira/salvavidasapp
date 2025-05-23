@extends('layouts.app')

@section('title', 'Bloque 3: Trabajo Digno')

@section('content')
<div class="container">
    <h2 class="mb-4">Bloque 3: Trabajo Digno</h2>
    <ul class="nav nav-tabs mb-4" id="bloque3TabNav" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque1-tab" href="{{ route('form.show', ['block' => 1, 'tipo_documento' => $tipo_documento, 'numero_documento' => $numero_documento]) }}" role="tab">Bloque 1: Caracterización Familiar</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque2-tab" href="{{ route('form.show', ['block' => 2, 'tipo_documento' => $tipo_documento, 'numero_documento' => $numero_documento]) }}" role="tab">Bloque 2: Vida Digna</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="bloque3-tab" data-bs-toggle="tab" href="#bloque3-tab-pane" role="tab" aria-controls="bloque3-tab-pane" aria-selected="true">Bloque 3: Trabajo Digno</a>
        </li>
    </ul>
    <div class="tab-content" id="bloque3TabsContent">
        <div class="tab-pane fade show active" id="bloque3-tab-pane" role="tabpanel" aria-labelledby="bloque3-tab">
            <form method="POST" action="{{ route('form.store') }}" autocomplete="off" id="bloque3Form">
                @csrf
                <input type="hidden" name="block" value="3">
                @if(isset($registro))
                    <input type="hidden" name="es_actualizacion" value="1">
                @endif
                <input type="hidden" name="tipo_documento" value="{{ $tipo_documento }}">
                <input type="hidden" name="numero_documento" value="{{ $numero_documento }}">
                <input type="hidden" name="profesional_documento" value="{{ session('profesional_documento', old('profesional_documento', isset($registro) ? $registro->profesional_documento : '0')) }}">
                <!-- Pregunta 20 -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">20. ¿Cuál es la principal fuente de ingresos de tu núcleo familiar?</div>
                    <div class="card-body">
                        <select class="form-select" name="fuente_ingreso" required>
                            <option value="">Seleccione...</option>
                            <option value="118" {{ old('fuente_ingreso', $registro->fuente_ingreso ?? '') == 118 ? 'selected' : '' }}>Empleo formal</option>
                            <option value="119" {{ old('fuente_ingreso', $registro->fuente_ingreso ?? '') == 119 ? 'selected' : '' }}>Empleo informal</option>
                            <option value="120" {{ old('fuente_ingreso', $registro->fuente_ingreso ?? '') == 120 ? 'selected' : '' }}>Independiente</option>
                            <option value="121" {{ old('fuente_ingreso', $registro->fuente_ingreso ?? '') == 121 ? 'selected' : '' }}>Apoyo de familiares y amigos</option>
                            <option value="122" {{ old('fuente_ingreso', $registro->fuente_ingreso ?? '') == 122 ? 'selected' : '' }}>Pensión</option>
                            <option value="123" {{ old('fuente_ingreso', $registro->fuente_ingreso ?? '') == 123 ? 'selected' : '' }}>Subsidios del gobierno</option>
                            <option value="124" {{ old('fuente_ingreso', $registro->fuente_ingreso ?? '') == 124 ? 'selected' : '' }}>Ninguna</option>
                        </select>
                    </div>
                </div>
                <!-- Pregunta 21 -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">21. ¿Cuántas personas de tu núcleo familiar cuentan con un ingreso fijo?</div>
                    <div class="card-body">
                        <select class="form-select" name="ingreso_fijo" required>
                            <option value="">Seleccione...</option>
                            <option value="125" {{ old('ingreso_fijo', $registro->ingreso_fijo ?? '') == 125 ? 'selected' : '' }}>1 integrante del núcleo familiar</option>
                            <option value="126" {{ old('ingreso_fijo', $registro->ingreso_fijo ?? '') == 126 ? 'selected' : '' }}>2 integrantes del núcleo familiar</option>
                            <option value="127" {{ old('ingreso_fijo', $registro->ingreso_fijo ?? '') == 127 ? 'selected' : '' }}>Más de 2 integrantes del núcleo familiar</option>
                            <option value="2" {{ old('ingreso_fijo', $registro->ingreso_fijo ?? '') == 2 ? 'selected' : '' }}>Ninguno</option>
                        </select>
                    </div>
                </div>
                <!-- Pregunta 22 -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">22. ¿Considera que los integrantes de tu núcleo familiar tienen un equilibrio entre la vida laboral y familiar para compartir o tener espacios de recreación?</div>
                    <div class="card-body">
                        <select class="form-select" name="equilibrio_vida_laboral" required>
                            <option value="">Seleccione...</option>
                            <option value="1" {{ old('equilibrio_vida_laboral', $registro->equilibrio_vida_laboral ?? '') == 1 ? 'selected' : '' }}>Sí</option>
                            <option value="2" {{ old('equilibrio_vida_laboral', $registro->equilibrio_vida_laboral ?? '') == 2 ? 'selected' : '' }}>No</option>
                            <option value="44" {{ old('equilibrio_vida_laboral', $registro->equilibrio_vida_laboral ?? '') == 44 ? 'selected' : '' }}>No sabe</option>
                        </select>
                    </div>
                </div>
                <!-- Pregunta 23 -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">23. ¿El trabajo interfiere en la capacidad de cuidar a los niños/as, adultos mayores o personas con discapacidad de tu núcleo familiar?</div>
                    <div class="card-body">
                        <select class="form-select" name="interfiere_cuidado" required>
                            <option value="">Seleccione...</option>
                            <option value="1" {{ old('interfiere_cuidado', $registro->interfiere_cuidado ?? '') == 1 ? 'selected' : '' }}>Sí</option>
                            <option value="2" {{ old('interfiere_cuidado', $registro->interfiere_cuidado ?? '') == 2 ? 'selected' : '' }}>No</option>
                            <option value="128" {{ old('interfiere_cuidado', $registro->interfiere_cuidado ?? '') == 128 ? 'selected' : '' }}>No tenemos personas que requieran cuidado</option>
                        </select>
                    </div>
                </div>
                <!-- Pregunta 24 -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">24. ¿Los trabajos de cuidado y domésticos le impiden acceder a tu familia a trabajos remunerados?</div>
                    <div class="card-body">
                        <select class="form-select" name="trabajos_domesticos_impiden" required>
                            <option value="">Seleccione...</option>
                            <option value="129" {{ old('trabajos_domesticos_impiden', $registro->trabajos_domesticos_impiden ?? '') == 129 ? 'selected' : '' }}>Totalmente de acuerdo</option>
                            <option value="130" {{ old('trabajos_domesticos_impiden', $registro->trabajos_domesticos_impiden ?? '') == 130 ? 'selected' : '' }}>De acuerdo</option>
                            <option value="131" {{ old('trabajos_domesticos_impiden', $registro->trabajos_domesticos_impiden ?? '') == 131 ? 'selected' : '' }}>Ni de acuerdo, ni en desacuerdo</option>
                            <option value="132" {{ old('trabajos_domesticos_impiden', $registro->trabajos_domesticos_impiden ?? '') == 132 ? 'selected' : '' }}>En desacuerdo</option>
                            <option value="133" {{ old('trabajos_domesticos_impiden', $registro->trabajos_domesticos_impiden ?? '') == 133 ? 'selected' : '' }}>Totalmente en desacuerdo</option>
                        </select>
                    </div>
                </div>
                <!-- Pregunta 25 -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">25. ¿Cuál es el principal medio a través del cual tu núcleo familiar obtiene los alimentos?</div>
                    <div class="card-body">
                        <select class="form-select" name="medio_obtencion_alimentos" required>
                            <option value="">Seleccione...</option>
                            <option value="134" {{ old('medio_obtencion_alimentos', $registro->medio_obtencion_alimentos ?? '') == 134 ? 'selected' : '' }}>Compras en almacenes</option>
                            <option value="135" {{ old('medio_obtencion_alimentos', $registro->medio_obtencion_alimentos ?? '') == 135 ? 'selected' : '' }}>Bonos y paquetes alimentarios del gobierno</option>
                            <option value="136" {{ old('medio_obtencion_alimentos', $registro->medio_obtencion_alimentos ?? '') == 136 ? 'selected' : '' }}>Bonos de la empresa</option>
                            <option value="137" {{ old('medio_obtencion_alimentos', $registro->medio_obtencion_alimentos ?? '') == 137 ? 'selected' : '' }}>Cultivos propios</option>
                            <option value="138" {{ old('medio_obtencion_alimentos', $registro->medio_obtencion_alimentos ?? '') == 138 ? 'selected' : '' }}>Redes comunitarias</option>
                        </select>
                    </div>
                </div>
                <!-- Pregunta 26 -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">26. ¿Tu núcleo familiar cuenta con algún patrimonio que les permita solventarse ante una eventualidad o crisis?</div>
                    <div class="card-body">
                        <select class="form-select" name="patrimonio" required>
                            <option value="">Seleccione...</option>
                            <option value="1" {{ old('patrimonio', $registro->patrimonio ?? '') == 1 ? 'selected' : '' }}>Sí</option>
                            <option value="2" {{ old('patrimonio', $registro->patrimonio ?? '') == 2 ? 'selected' : '' }}>No</option>
                            <option value="44" {{ old('patrimonio', $registro->patrimonio ?? '') == 44 ? 'selected' : '' }}>No sabe</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <a href="{{ route('form.show', ['block' => 2, 'tipo_documento' => $tipo_documento, 'numero_documento' => $numero_documento]) }}" class="btn btn-secondary btn-lg me-2">
                        <i class="bi bi-arrow-left-circle"></i> Anterior
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg me-2">Guardar</button>
                    <button id="btnSiguienteBloque4" type="button" class="btn btn-success btn-lg" {{ !isset($registro) ? 'disabled' : '' }}>
                        Siguiente <i class="bi bi-arrow-right-circle"></i>
                    </button>
                </div>
            </form>
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
    const form = document.getElementById('bloque3Form');
    if (form) {
        form.addEventListener('submit', function(e) {
            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        });
    }
    // Lógica para el botón Siguiente
    const btnSiguiente = document.getElementById('btnSiguienteBloque4');
    @if(isset($registro))
        btnSiguiente.disabled = false;
        btnSiguiente.addEventListener('click', function() {
            const tipo_documento = '{{ $registro->tipo_documento ?? $tipo_documento }}';
            const numero_documento = '{{ $registro->numero_documento ?? $numero_documento }}';
            // Construye la URL relativa correcta
            window.location.href = "/observatorioapp/public/form/4/" + tipo_documento + "/" + numero_documento;
        });
    @else
        btnSiguiente.disabled = true;
    @endif
});
</script>
@endsection
