@extends('layouts.app')

@section('title', 'Bloque 1 Nuevo: Caracterización Familiar')

@section('content')
<div class="container">
    <h2 class="mb-4">Bloque 1 (Nuevo): Caracterización Familiar</h2>
    <form method="POST" action="{{ route('bloque1nuevo.store') }}" autocomplete="off" id="bloque1NuevoForm">
        @csrf
        <input type="hidden" name="block" value="1">
        <input type="hidden" name="tipo_documento" value="{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}">
        <input type="hidden" name="numero_documento" value="{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}">
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Identificación</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="tipo_documento" class="form-label"><strong>Tipo de documento</strong></label>
                        <select class="form-select" id="tipo_documento" name="tipo_documento">
                            <option value="">Seleccione...</option>
                            <option value="4" {{ old('tipo_documento', isset($registro) ? $registro->tipo_documento : '') == '4' ? 'selected' : '' }}>Cédula de ciudadanía</option>
                            <option value="5" {{ old('tipo_documento', isset($registro) ? $registro->tipo_documento : '') == '5' ? 'selected' : '' }}>Cédula de extranjería</option>
                            <option value="6" {{ old('tipo_documento', isset($registro) ? $registro->tipo_documento : '') == '6' ? 'selected' : '' }}>Estatuto de Protección Temporal PPT</option>
                        </select>
                        @error('tipo_documento')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="numero_documento" class="form-label"><strong>Número de documento</strong></label>
                        <input type="text" class="form-control" id="numero_documento" name="numero_documento" value="{{ old('numero_documento', isset($registro) ? $registro->numero_documento : '') }}">
                        @error('numero_documento')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="profesional_documento" class="form-label">Número de documento del profesional</label>
                        <input type="text" class="form-control" id="profesional_documento" name="profesional_documento" value="{{ old('profesional_documento', isset($registro) ? $registro->profesional_documento : '') }}">
                        @error('profesional_documento')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
        <!-- Aquí irían los campos de las preguntas 1 a 16, siguiendo el formato de la plantilla y usando los nombres pN_... -->
        <!-- Ejemplo de pregunta única -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 1</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>1.</strong> ¿En qué comuna vive su núcleo familiar?</label>
                    <select class="form-select" name="p1_comuna">
                        <option value="">Seleccione...</option>
                        @for ($i = 7; $i <= 27; $i++)
                            <option value="{{ $i }}" {{ old('p1_comuna', isset($registro) ? $registro->p1_comuna : '') == $i ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                        @endfor
                    </select>
                    @error('p1_comuna')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <!-- Pregunta 2 -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 2</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>2.</strong> ¿Cuál es el estrato socioeconómico de la vivienda que habita su núcleo familiar?</label>
                    <select class="form-select" name="p2_estrato_conocimiento">
                        <option value="">Seleccione...</option>
                        @for ($i = 28; $i <= 33; $i++)
                            <option value="{{ $i }}" {{ old('p2_estrato_conocimiento', isset($registro) ? $registro->p2_estrato_conocimiento : '') == $i ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                        @endfor
                    </select>
                    @error('p2_estrato_conocimiento')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <!-- Pregunta 3 -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 3</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>3.</strong> ¿Cuántas personas integran su núcleo familiar?</label>
                    <select class="form-select" name="p3_personas_integran">
                        <option value="">Seleccione...</option>
                        @for ($i = 34; $i <= 37; $i++)
                            <option value="{{ $i }}" {{ old('p3_personas_integran', isset($registro) ? $registro->p3_personas_integran : '') == $i ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                        @endfor
                    </select>
                    @error('p3_personas_integran')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <!-- Pregunta 4 (opción múltiple) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 4</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>4.</strong> De las siguientes configuraciones familiares ¿cuál representa a su núcleo familiar? (Puede seleccionar varias)</label>
                    <div class="row">
                        @php $familias = [38,39,40,41,42,43,44,45,46,47,48,49,50,51]; @endphp
                        @foreach ($familias as $i)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="p4_{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}" id="p4_{{ $i }}" value="1" {{ old('p4_'.$i, isset($registro) ? $registro->{'p4_'.$i} : '') == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="p4_{{ $i }}">{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <!-- Pregunta 5 (opción múltiple) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 5</div>
            <div class="card-body">
                <label class="form-label"><strong>5.</strong> ¿Algún o algunos miembros de su núcleo familiar se identifican con los siguientes elementos? (Puede seleccionar varios)</label>
                <div class="row">
                    @php $elementos = [52,53,54,55,56,57,58]; @endphp
                    @foreach ($elementos as $i)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="p5_{{ $i }}" id="p5_{{ $i }}" value="1" {{ old('p5_'.$i, isset($registro) ? $registro->{'p5_'.$i} : '') == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="p5_{{ $i }}">{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Pregunta 6 (opción múltiple) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 6</div>
            <div class="card-body">
                <label class="form-label"><strong>6.</strong> De los siguientes grupos etarios, ¿cuáles están presentes en su núcleo familiar? (Puede seleccionar varios)</label>
                <div class="row">
                    @php $etarios = [60,61,62,63]; @endphp
                    @foreach ($etarios as $i)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="p6_{{ $i }}" id="p6_{{ $i }}" value="1" {{ old('p6_'.$i, isset($registro) ? $registro->{'p6_'.$i} : '') == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="p6_{{ $i }}">{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Pregunta 7 (opción múltiple) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 7</div>
            <div class="card-body">
                <label class="form-label"><strong>7.</strong> ¿Con cuál de las siguientes etnias se identifica su núcleo familiar? (Puede seleccionar varias)</label>
                <div class="row">
                    @php $etnias = [64,65,66,67,68,69,70,71,72]; @endphp
                    @foreach ($etnias as $i)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="p7_{{ $i }}" id="p7_{{ $i }}" value="1" {{ old('p7_'.$i, isset($registro) ? $registro->{'p7_'.$i} : '') == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="p7_{{ $i }}">{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Pregunta 8 -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 8</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>8.</strong> ¿Cuál es el máximo nivel educativo alcanzado por los miembros del núcleo familiar?</label>
                    <select class="form-select" name="p8_maximo_educativo">
                        <option value="">Seleccione...</option>
                        @for ($i = 73; $i <= 79; $i++)
                            <option value="{{ $i }}" {{ old('p8_maximo_educativo', isset($registro) ? $registro->p8_maximo_educativo : '') == $i ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                        @endfor
                    </select>
                    @error('p8_maximo_educativo')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <!-- Pregunta 9 -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 9</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>9.</strong> ¿Algunos de los integrantes de su núcleo familiar han sido víctimas del conflicto armado en Colombia?</label>
                    <select class="form-select" name="p9_integrantes_fuerzas_armadas">
                        <option value="">Seleccione...</option>
                        <option value="82" {{ old('p9_integrantes_fuerzas_armadas', isset($registro) ? $registro->p9_integrantes_fuerzas_armadas : '') == 82 ? 'selected' : '' }}>Sí</option>
                        <option value="83" {{ old('p9_integrantes_fuerzas_armadas', isset($registro) ? $registro->p9_integrantes_fuerzas_armadas : '') == 83 ? 'selected' : '' }}>No</option>
                        <option value="84" {{ old('p9_integrantes_fuerzas_armadas', isset($registro) ? $registro->p9_integrantes_fuerzas_armadas : '') == 84 ? 'selected' : '' }}>No sabe</option>
                    </select>
                    @error('p9_integrantes_fuerzas_armadas')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <!-- Pregunta 10 (opción múltiple) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 10</div>
            <div class="card-body">
                <label class="form-label"><strong>10.</strong> ¿Cuáles han sido los hechos victimizantes? (Seleccione todas las que apliquen)</label>
                <div class="row">
                    @php $hechos = [85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100]; @endphp
                    @foreach ($hechos as $i)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="p10_{{ $i }}" id="p10_{{ $i }}" value="1" {{ old('p10_'.$i, isset($registro) ? $registro->{'p10_'.$i} : '') == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="p10_{{ $i }}">{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Pregunta 11 -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 11</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>11.</strong> ¿Cuántas personas forman ingresos en su núcleo familiar?</label>
                    <select class="form-select" name="p11_cuantas_personas">
                        <option value="">Seleccione...</option>
                        @for ($i = 101; $i <= 104; $i++)
                            <option value="{{ $i }}" {{ old('p11_cuantas_personas', isset($registro) ? $registro->p11_cuantas_personas : '') == $i ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                        @endfor
                    </select>
                    @error('p11_cuantas_personas')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <!-- Pregunta 12 -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 12</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>12.</strong> ¿Quién es la jefatura o figura de representación en tu núcleo familiar?</label>
                    <select class="form-select" name="p12_jefatura">
                        <option value="">Seleccione...</option>
                        @for ($i = 107; $i <= 117; $i++)
                            <option value="{{ $i }}" {{ old('p12_jefatura', isset($registro) ? $registro->p12_jefatura : '') == $i ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                        @endfor
                    </select>
                    @error('p12_jefatura')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <!-- Pregunta 13 -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 13</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>13.</strong> En tu familia hay personas que acceden calidad permanente?</label>
                    <select class="form-select" name="p13_habla_permanente">
                        <option value="">Seleccione...</option>
                        <option value="118" {{ old('p13_habla_permanente', isset($registro) ? $registro->p13_habla_permanente : '') == 118 ? 'selected' : '' }}>Sí</option>
                        <option value="119" {{ old('p13_habla_permanente', isset($registro) ? $registro->p13_habla_permanente : '') == 119 ? 'selected' : '' }}>No</option>
                        <option value="120" {{ old('p13_habla_permanente', isset($registro) ? $registro->p13_habla_permanente : '') == 120 ? 'selected' : '' }}>No sabe</option>
                    </select>
                    @error('p13_habla_permanente')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <!-- Pregunta 14 -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 14</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>14.</strong> ¿Cuántas personas aportan ingresos en el núcleo familiar?</label>
                    <select class="form-select" name="p14_personas_aportan_ingresos">
                        <option value="">Seleccione...</option>
                        @for ($i = 101; $i <= 104; $i++)
                            <option value="{{ $i }}" {{ old('p14_personas_aportan_ingresos', isset($registro) ? $registro->p14_personas_aportan_ingresos : '') == $i ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                        @endfor
                    </select>
                    @error('p14_personas_aportan_ingresos')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <!-- Pregunta 15 -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 15</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>15.</strong> ¿Quién es la jefatura o figura de representación en tu núcleo familiar?</label>
                    <select class="form-select" name="p15_jefatura_figura">
                        <option value="">Seleccione...</option>
                        @for ($i = 107; $i <= 117; $i++)
                            <option value="{{ $i }}" {{ old('p15_jefatura_figura', isset($registro) ? $registro->p15_jefatura_figura : '') == $i ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                        @endfor
                    </select>
                    @error('p15_jefatura_figura')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <!-- Pregunta 16 -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Pregunta 16</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>16.</strong> En tu familia hay personas que acceden calidad permanente?</label>
                    <select class="form-select" name="p16_personas_calidad_permanente">
                        <option value="">Seleccione...</option>
                        <option value="118" {{ old('p16_personas_calidad_permanente', isset($registro) ? $registro->p16_personas_calidad_permanente : '') == 118 ? 'selected' : '' }}>Sí</option>
                        <option value="119" {{ old('p16_personas_calidad_permanente', isset($registro) ? $registro->p16_personas_calidad_permanente : '') == 119 ? 'selected' : '' }}>No</option>
                        <option value="120" {{ old('p16_personas_calidad_permanente', isset($registro) ? $registro->p16_personas_calidad_permanente : '') == 120 ? 'selected' : '' }}>No sabe</option>
                    </select>
                    @error('p16_personas_calidad_permanente')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-success">Guardar</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                timer: 2000,
                showConfirmButton: false
            });
        @endif
        // Validaciones amigables aquí si lo deseas
    });
</script>
@endsection
