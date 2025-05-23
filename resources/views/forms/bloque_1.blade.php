@extends('layouts.app')

@section('title', 'Bloque 1: Caracterización Familiar')

@section('content')
<div class="container">
    <h2 class="mb-4">Bloque 1: Caracterización Familiar</h2>
    <ul class="nav nav-tabs mb-4" id="bloque1TabNav" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="bloque1-tab" data-bs-toggle="tab" href="#bloque1-tab-pane" role="tab" aria-controls="bloque1-tab-pane" aria-selected="true">
                Bloque 1: Caracterización Familiar
            </a>
        </li>
        @if(isset($registro))
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bloque2-tab" href="/observatorioapp/public/form/2/{{ $registro->tipo_documento }}/{{ $registro->numero_documento }}" role="tab" aria-controls="bloque2-tab-pane" aria-selected="false">
                Bloque 2: Vida Digna
            </a>
        </li>
        @endif
    </ul>
    <div class="tab-content" id="bloque1TabsContent">
        <div class="tab-pane fade show active" id="bloque1-tab-pane" role="tabpanel" aria-labelledby="bloque1-tab">
            <form method="POST" action="{{ route('form.store') }}" autocomplete="off" id="bloque1Form">
                @csrf
                <input type="hidden" name="block" value="1">
                @if(isset($registro))
                    <input type="hidden" name="es_actualizacion" value="1">
                @endif
                <input type="hidden" name="tipo_documento" value="{{ $registro->tipo_documento ?? request('tipo_documento', request()->route('tipo_documento')) }}">
                <input type="hidden" name="numero_documento" value="{{ $registro->numero_documento ?? request('numero_documento', request()->route('numero_documento')) }}">
                <!-- Identificación -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">Identificación</div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="tipo_documento" class="form-label"><strong>1.</strong> Tipo de documento</label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento">
                                    <option value="">Seleccione...</option>
                                    <option value="3" {{ old('tipo_documento', isset($registro) ? $registro->tipo_documento : '') == '3' ? 'selected' : '' }}>Cédula de ciudadanía</option>
                                    <option value="4" {{ old('tipo_documento', isset($registro) ? $registro->tipo_documento : '') == '4' ? 'selected' : '' }}>Cédula de extranjería</option>
                                    <option value="5" {{ old('tipo_documento', isset($registro) ? $registro->tipo_documento : '') == '5' ? 'selected' : '' }}>Permiso por Protección Temporal (PPT)</option>
                                </select>
                                @error('tipo_documento')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="numero_documento" class="form-label"><strong>2.</strong> Número de documento</label>
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
                <!-- Núcleo Familiar -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">Núcleo Familiar</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><strong>3.</strong> ¿En qué comuna vive su núcleo familiar?</label>
                            <select class="form-select" name="comuna_nucleo_familiar">
                                <option value="">Seleccione...</option>
                                @for ($i = 6; $i <= 26; $i++)
                                    <option value="{{ $i }}" {{ old('comuna_nucleo_familiar', isset($registro) ? $registro->comuna_nucleo_familiar : '') == $i ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                                @endfor
                            </select>
                            @error('comuna_nucleo_familiar')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>4.</strong> ¿Algún miembro del núcleo familiar se identifica con las siguientes orientaciones sexuales? (Puede seleccionar varias)</label>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="orientacion_lesbiana" id="orientacion_lesbiana" value="1" {{ old('orientacion_lesbiana', isset($registro) ? $registro->orientacion_lesbiana : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="orientacion_lesbiana">Lesbiana</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="orientacion_gay" id="orientacion_gay" value="1" {{ old('orientacion_gay', isset($registro) ? $registro->orientacion_gay : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="orientacion_gay">Gay</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="orientacion_bisexual" id="orientacion_bisexual" value="1" {{ old('orientacion_bisexual', isset($registro) ? $registro->orientacion_bisexual : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="orientacion_bisexual">Bisexual</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="orientacion_pansexual" id="orientacion_pansexual" value="1" {{ old('orientacion_pansexual', isset($registro) ? $registro->orientacion_pansexual : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="orientacion_pansexual">Pansexual</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="orientacion_asexual" id="orientacion_asexual" value="1" {{ old('orientacion_asexual', isset($registro) ? $registro->orientacion_asexual : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="orientacion_asexual">Asexual</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="orientacion_otra" id="orientacion_otra" value="1" {{ old('orientacion_otra', isset($registro) ? $registro->orientacion_otra : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="orientacion_otra">Otra</label>
                                    </div>
                                </div>
                                <div class="col-md-3 align-items-center d-flex">
                                    <input type="text" class="form-control ms-2" id="orientacion_otra_cual" name="orientacion_otra_cual" placeholder="¿Cuál?" value="{{ old('orientacion_otra_cual', isset($registro) ? $registro->orientacion_otra_cual : '') }}" style="display: none; max-width: 180px;">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="orientacion_prefiere_no_responder" id="orientacion_prefiere_no_responder" value="1" {{ old('orientacion_prefiere_no_responder', isset($registro) ? $registro->orientacion_prefiere_no_responder : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="orientacion_prefiere_no_responder">Prefiere no responder</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="orientacion_no_aplica" id="orientacion_no_aplica" value="1" {{ old('orientacion_no_aplica', isset($registro) ? $registro->orientacion_no_aplica : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="orientacion_no_aplica">No aplica</label>
                                    </div>
                                </div>
                            </div>
                            @error('orientacion')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>5.</strong> ¿De los siguientes grupos etarios, cuáles están presentes en su núcleo familiar? (Puede seleccionar varias)</label>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="grupo_primera_infancia" id="grupo_primera_infancia" value="1" {{ old('grupo_primera_infancia', isset($registro) ? $registro->grupo_primera_infancia : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="grupo_primera_infancia">Primera infancia/adolescentes (0-13 años)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="grupo_jovenes" id="grupo_jovenes" value="1" {{ old('grupo_jovenes', isset($registro) ? $registro->grupo_jovenes : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="grupo_jovenes">Jóvenes (14-28 años)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="grupo_adultos" id="grupo_adultos" value="1" {{ old('grupo_adultos', isset($registro) ? $registro->grupo_adultos : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="grupo_adultos">Adultos (29-59 años)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="grupo_adultos_mayores" id="grupo_adultos_mayores" value="1" {{ old('grupo_adultos_mayores', isset($registro) ? $registro->grupo_adultos_mayores : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="grupo_adultos_mayores">Adultos mayores (más de 60 años)</label>
                                    </div>
                                </div>
                            </div>
                            @error('grupo')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>6.</strong> ¿Cuáles han sido los hechos victimizantes? (Seleccione todas las que apliquen)</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hecho_homicidio" id="hecho_homicidio" value="1" {{ old('hecho_homicidio', isset($registro) ? $registro->hecho_homicidio : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hecho_homicidio">Homicidio</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hecho_desaparicion" id="hecho_desaparicion" value="1" {{ old('hecho_desaparicion', isset($registro) ? $registro->hecho_desaparicion : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hecho_desaparicion">Desaparición forzada</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hecho_confinamiento" id="hecho_confinamiento" value="1" {{ old('hecho_confinamiento', isset($registro) ? $registro->hecho_confinamiento : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hecho_confinamiento">Confinamiento</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hecho_desplazamiento" id="hecho_desplazamiento" value="1" {{ old('hecho_desplazamiento', isset($registro) ? $registro->hecho_desplazamiento : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hecho_desplazamiento">Desplazamiento forzado</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hecho_tortura" id="hecho_tortura" value="1" {{ old('hecho_tortura', isset($registro) ? $registro->hecho_tortura : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hecho_tortura">Tortura</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hecho_amenaza" id="hecho_amenaza" value="1" {{ old('hecho_amenaza', isset($registro) ? $registro->hecho_amenaza : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hecho_amenaza">Amenaza</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hecho_otro" id="hecho_otro" value="1" {{ old('hecho_otro', isset($registro) ? $registro->hecho_otro : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hecho_otro">Otro</label>
                                    </div>
                                </div>
                                <div class="col-md-4 align-items-center d-flex">
                                    <input type="text" class="form-control ms-2" id="hecho_otro_cual" name="hecho_otro_cual" placeholder="¿Cuál?" value="{{ old('hecho_otro_cual', isset($registro) ? $registro->hecho_otro_cual : '') }}" style="display: none; max-width: 180px;">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hecho_no_aplica" id="hecho_no_aplica" value="1" {{ old('hecho_no_aplica', isset($registro) ? $registro->hecho_no_aplica : '') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hecho_no_aplica">No aplica</label>
                                    </div>
                                </div>
                            </div>
                            @error('hecho')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>7.</strong> ¿La familia es migrante?</label>
                            <select class="form-select" name="familia_migrante">
                                <option value="">Seleccione...</option>
                                <option value="1" {{ old('familia_migrante', isset($registro) ? $registro->familia_migrante : '') == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="2" {{ old('familia_migrante', isset($registro) ? $registro->familia_migrante : '') == '2' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('familia_migrante')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>8.</strong> ¿Con cuál de los siguientes grupos étnicos se identifica la familia?</label>
                            <select class="form-select" name="grupo_etnico">
                                <option value="">Seleccione...</option>
                                <option value="38" {{ old('grupo_etnico', isset($registro) ? $registro->grupo_etnico : '') == '38' ? 'selected' : '' }}>Indígena</option>
                                <option value="39" {{ old('grupo_etnico', isset($registro) ? $registro->grupo_etnico : '') == '39' ? 'selected' : '' }}>Afrodescendiente</option>
                                <option value="40" {{ old('grupo_etnico', isset($registro) ? $registro->grupo_etnico : '') == '40' ? 'selected' : '' }}>Mestizo</option>
                                <option value="41" {{ old('grupo_etnico', isset($registro) ? $registro->grupo_etnico : '') == '41' ? 'selected' : '' }}>Room o Gitano</option>
                                <option value="42" {{ old('grupo_etnico', isset($registro) ? $registro->grupo_etnico : '') == '42' ? 'selected' : '' }}>Ninguno</option>
                                <option value="43" {{ old('grupo_etnico', isset($registro) ? $registro->grupo_etnico : '') == '43' ? 'selected' : '' }}>Prefiero no decirlo</option>
                            </select>
                            @error('grupo_etnico')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <!-- Victimización -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">Victimización</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><strong>9.</strong> ¿Alguno de los miembros de su núcleo familiar ha sido víctima del conflicto armado y/o social en Colombia?</label>
                            <select class="form-select" name="victima_conflicto">
                                <option value="">Seleccione...</option>
                                <option value="1" {{ old('victima_conflicto', isset($registro) ? $registro->victima_conflicto : '') == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="2" {{ old('victima_conflicto', isset($registro) ? $registro->victima_conflicto : '') == '2' ? 'selected' : '' }}>No</option>
                                <option value="44" {{ old('victima_conflicto', isset($registro) ? $registro->victima_conflicto : '') == '44' ? 'selected' : '' }}>No sabe</option>
                            </select>
                            @error('victima_conflicto')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>10.</strong> ¿En el núcleo familiar han sido desplazados por conflicto armado o grupos delincuenciales en Colombia?</label>
                            <select class="form-select" name="desplazado_conflicto">
                                <option value="">Seleccione...</option>
                                <option value="1" {{ old('desplazado_conflicto', isset($registro) ? $registro->desplazado_conflicto : '') == '1' ? 'selected' : '' }}>Sí, todos</option>
                                <option value="2" {{ old('desplazado_conflicto', isset($registro) ? $registro->desplazado_conflicto : '') == '2' ? 'selected' : '' }}>Sí, un integrante</option>
                                <option value="3" {{ old('desplazado_conflicto', isset($registro) ? $registro->desplazado_conflicto : '') == '3' ? 'selected' : '' }}>Sí, más de un integrante</option>
                                <option value="4" {{ old('desplazado_conflicto', isset($registro) ? $registro->desplazado_conflicto : '') == '4' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('desplazado_conflicto')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <!-- Educación y Composición -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">Educación y Composición</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><strong>11.</strong> ¿Cuál es el máximo nivel educativo alcanzado por alguno de los miembros de su núcleo familiar?</label>
                            <select class="form-select" name="nivel_educativo">
                                <option value="">Seleccione...</option>
                                <option value="60" {{ old('nivel_educativo', isset($registro) ? $registro->nivel_educativo : '') == '60' ? 'selected' : '' }}>Primaria</option>
                                <option value="61" {{ old('nivel_educativo', isset($registro) ? $registro->nivel_educativo : '') == '61' ? 'selected' : '' }}>Bachiller</option>
                                <option value="62" {{ old('nivel_educativo', isset($registro) ? $registro->nivel_educativo : '') == '62' ? 'selected' : '' }}>Técnico</option>
                                <option value="63" {{ old('nivel_educativo', isset($registro) ? $registro->nivel_educativo : '') == '63' ? 'selected' : '' }}>Tecnológico</option>
                                <option value="64" {{ old('nivel_educativo', isset($registro) ? $registro->nivel_educativo : '') == '64' ? 'selected' : '' }}>Pregrado</option>
                                <option value="65" {{ old('nivel_educativo', isset($registro) ? $registro->nivel_educativo : '') == '65' ? 'selected' : '' }}>Posgrado</option>
                                <option value="0" {{ old('nivel_educativo', isset($registro) ? $registro->nivel_educativo : '') == '0' ? 'selected' : '' }}>Ninguno</option>
                            </select>
                            @error('nivel_educativo')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>12.</strong> ¿Cuántas personas integran su núcleo familiar?</label>
                            <select class="form-select" name="personas_nucleo">
                                <option value="">Seleccione...</option>
                                <option value="66" {{ old('personas_nucleo', isset($registro) ? $registro->personas_nucleo : '') == '66' ? 'selected' : '' }}>1 persona</option>
                                <option value="67" {{ old('personas_nucleo', isset($registro) ? $registro->personas_nucleo : '') == '67' ? 'selected' : '' }}>2 personas</option>
                                <option value="68" {{ old('personas_nucleo', isset($registro) ? $registro->personas_nucleo : '') == '68' ? 'selected' : '' }}>Entre 3 y 5 personas</option>
                                <option value="69" {{ old('personas_nucleo', isset($registro) ? $registro->personas_nucleo : '') == '69' ? 'selected' : '' }}>Más de 5 personas</option>
                            </select>
                            @error('personas_nucleo')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <!-- Configuración Familiar -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">Configuración Familiar</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><strong>13.</strong> De las siguientes configuraciones familiares ¿cuál representa a su núcleo familiar?</label>
                            <select class="form-select" name="config_familiar">
                                <option value="">Seleccione...</option>
                                @for ($i = 70; $i <= 83; $i++)
                                    <option value="{{ $i }}" {{ old('config_familiar', isset($registro) ? $registro->config_familiar : '') == $i ? 'selected' : '' }}>{{ DB::table('t_diccionario_datos_observatorio')->where('id', $i)->value('descripcion') }}</option>
                                @endfor
                            </select>
                            @error('config_familiar')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>14.</strong> ¿Cuántas personas aportan ingresos en el núcleo familiar?</label>
                            <select class="form-select" name="personas_aportan">
                                <option value="">Seleccione...</option>
                                <option value="66" {{ old('personas_aportan', isset($registro) ? $registro->personas_aportan : '') == '66' ? 'selected' : '' }}>1 persona</option>
                                <option value="67" {{ old('personas_aportan', isset($registro) ? $registro->personas_aportan : '') == '67' ? 'selected' : '' }}>2 personas</option>
                                <option value="68" {{ old('personas_aportan', isset($registro) ? $registro->personas_aportan : '') == '68' ? 'selected' : '' }}>Entre 3 y 5 personas</option>
                                <option value="69" {{ old('personas_aportan', isset($registro) ? $registro->personas_aportan : '') == '69' ? 'selected' : '' }}>Más de 5 personas</option>
                                <option value="84" {{ old('personas_aportan', isset($registro) ? $registro->personas_aportan : '') == '84' ? 'selected' : '' }}>Dependemos de apoyos externos</option>
                            </select>
                            @error('personas_aportan')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>15.</strong> ¿En tu familia hay personas que requieren cuidado permanente?</label>
                            <select class="form-select" name="personas_cuidado">
                                <option value="">Seleccione...</option>
                                <option value="1" {{ old('personas_cuidado', isset($registro) ? $registro->personas_cuidado : '') == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="2" {{ old('personas_cuidado', isset($registro) ? $registro->personas_cuidado : '') == '2' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('personas_cuidado')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Guardar
                    </button>
                    <button type="button" class="btn btn-success btn-lg ms-2" id="btnSiguiente"  {{ isset($registro) ? '' : 'disabled' }}>
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

        // Lógica para el botón Siguiente
        const btnSiguiente = document.getElementById('btnSiguiente');
        @if(isset($registro))
            btnSiguiente.disabled = false;
            btnSiguiente.addEventListener('click', function() {
                // Usar SIEMPRE las llaves del registro guardado para URL limpia
                const tipo_documento = '{{ $registro->tipo_documento }}';
                const numero_documento = '{{ $registro->numero_documento }}';
                window.location.href = "/observatorioapp/public/form/2/" + tipo_documento + "/" + numero_documento;
            });
        @endif

        // Mostrar SweetAlert de error si existe error de usuario existente
        @if($errors->has('usuario_existente'))
            Swal.fire({
                icon: 'error',
                title: 'Usuario ya existe',
                text: 'Este usuario ya existe, solo puedes ingresar usuarios nuevos.',
                confirmButtonColor: '#d33'
            });
        @endif

        // Mostrar/ocultar campo CUAL en pregunta 4
        const chkOtraOrientacion = document.getElementById('orientacion_otra');
        const txtOtraOrientacion = document.getElementById('orientacion_otra_cual');
        if(chkOtraOrientacion) {
            function toggleOtraOrientacion() {
                txtOtraOrientacion.style.display = chkOtraOrientacion.checked ? 'block' : 'none';
            }
            chkOtraOrientacion.addEventListener('change', toggleOtraOrientacion);
            toggleOtraOrientacion();
        }
        // Exclusividad No aplica en pregunta 4
        const chkNoAplicaOrientacion = document.getElementById('orientacion_no_aplica');
        const orientacionChecks = document.querySelectorAll('input[name^="orientacion_"]:not(#orientacion_no_aplica)');
        if(chkNoAplicaOrientacion) {
            chkNoAplicaOrientacion.addEventListener('change', function() {
                if(this.checked) {
                    orientacionChecks.forEach(chk => { if(chk.id !== 'orientacion_no_aplica') chk.checked = false; });
                    if(chkOtraOrientacion) txtOtraOrientacion.style.display = 'none';
                }
            });
        }
        orientacionChecks.forEach(chk => {
            chk.addEventListener('change', function() {
                if(this.checked && chkNoAplicaOrientacion.checked) chkNoAplicaOrientacion.checked = false;
            });
        });

        // Mostrar/ocultar campo CUAL en pregunta 6
        const chkHechoOtro = document.getElementById('hecho_otro');
        const txtHechoOtro = document.getElementById('hecho_otro_cual');
        if(chkHechoOtro) {
            function toggleHechoOtro() {
                txtHechoOtro.style.display = chkHechoOtro.checked ? 'block' : 'none';
            }
            chkHechoOtro.addEventListener('change', toggleHechoOtro);
            toggleHechoOtro();
        }
        // Exclusividad No aplica en pregunta 6
        const chkNoAplicaHecho = document.getElementById('hecho_no_aplica');
        const hechoChecks = document.querySelectorAll('input[name^="hecho_"]:not(#hecho_no_aplica)');
        if(chkNoAplicaHecho) {
            chkNoAplicaHecho.addEventListener('change', function() {
                if(this.checked) {
                    hechoChecks.forEach(chk => { if(chk.id !== 'hecho_no_aplica') chk.checked = false; });
                    if(chkHechoOtro) txtHechoOtro.style.display = 'none';
                }
            });
        }
        hechoChecks.forEach(chk => {
            chk.addEventListener('change', function() {
                if(this.checked && chkNoAplicaHecho.checked) chkNoAplicaHecho.checked = false;
            });
        });

        // Spinner al guardar
        const form = document.getElementById('bloque1Form');
        form.addEventListener('submit', function(e) {
            // Validación amigable: solo mostrar SweetAlert para el primer campo vacío
            const campos = [
                {selector: '#tipo_documento', label: '1. Tipo de documento'},
                {selector: '#numero_documento', label: '2. Número de documento'},
                {selector: '#profesional_documento', label: 'Número de documento del profesional'},
                {selector: 'select[name="comuna_nucleo_familiar"]', label: '3. Comuna núcleo familiar'},
                {selector: 'select[name="familia_migrante"]', label: '7. ¿La familia es migrante?'},
                {selector: 'select[name="grupo_etnico"]', label: '8. Grupo étnico'},
                {selector: 'select[name="victima_conflicto"]', label: '9. ¿Ha sido víctima del conflicto armado?'},
                {selector: 'select[name="desplazado_conflicto"]', label: '10. ¿Han sido desplazados?'},
                {selector: 'select[name="nivel_educativo"]', label: '11. Nivel educativo'},
                {selector: 'select[name="personas_nucleo"]', label: '12. Personas en el núcleo familiar'},
                {selector: 'select[name="config_familiar"]', label: '13. Configuración familiar'},
                {selector: 'select[name="personas_aportan"]', label: '14. Personas que aportan ingresos'},
                {selector: 'select[name="personas_cuidado"]', label: '15. ¿Requieren cuidado permanente?'}
            ];
            for(let campo of campos) {
                let el = document.querySelector(campo.selector);
                if(el && (el.value === '' || el.value === null)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo obligatorio',
                        text: `Debes llenar el campo: ${campo.label}`,
                        confirmButtonColor: '#0c6efd'
                    });
                    el.focus();
                    return false;
                }
            }
            // Validar selección múltiple: al menos uno marcado
            const grupos = [
                {name: 'orientación sexual', prefix: 'orientacion_', label: '4. ¿Algún miembro del núcleo familiar se identifica con las siguientes orientaciones sexuales?'},
                {name: 'grupo etario', prefix: 'grupo_', label: '5. ¿De los siguientes grupos etarios, cuáles están presentes en su núcleo familiar?'},
                {name: 'hechos victimizantes', prefix: 'hecho_', label: '6. ¿Cuáles han sido los hechos victimizantes?'}
            ];
            for(let grupo of grupos) {
                let checks = this.querySelectorAll(`input[type=checkbox][name^='${grupo.prefix}']`);
                let checked = Array.from(checks).some(chk => chk.checked);
                if(!checked) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo obligatorio',
                        text: `Debes seleccionar al menos una opción en: ${grupo.label}`,
                        confirmButtonColor: '#0c6efd'
                    });
                    checks[0].focus();
                    return false;
                }
            }
            // Validación campo "¿Cuál?" pregunta 4
            const chkOtraOrientacion = document.getElementById('orientacion_otra');
            const chkNoAplicaOrientacion = document.getElementById('orientacion_no_aplica');
            const txtOtraOrientacion = document.getElementById('orientacion_otra_cual');
            if(chkOtraOrientacion && chkOtraOrientacion.checked && (!chkNoAplicaOrientacion || !chkNoAplicaOrientacion.checked)) {
                if(txtOtraOrientacion && txtOtraOrientacion.value.trim() === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo obligatorio',
                        text: 'Debes llenar el campo: 4. ¿Cuál? (especifica la orientación sexual)',
                        confirmButtonColor: '#0c6efd'
                    });
                    txtOtraOrientacion.focus();
                    return false;
                }
            }
            // Validación campo "¿Cuál?" pregunta 6
            const chkHechoOtro = document.getElementById('hecho_otro');
            const chkNoAplicaHecho = document.getElementById('hecho_no_aplica');
            const txtHechoOtro = document.getElementById('hecho_otro_cual');
            if(chkHechoOtro && chkHechoOtro.checked && (!chkNoAplicaHecho || !chkNoAplicaHecho.checked)) {
                if(txtHechoOtro && txtHechoOtro.value.trim() === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo obligatorio',
                        text: 'Debes llenar el campo: 6. ¿Cuál? (especifica el hecho victimizante)',
                        confirmButtonColor: '#0c6efd'
                    });
                    txtHechoOtro.focus();
                    return false;
                }
            }
            // Mostrar spinner sobre el formulario
            let spinner = document.createElement('div');
            spinner.id = 'loadingSpinner';
            spinner.style.position = 'fixed';
            spinner.style.top = '0';
            spinner.style.left = '0';
            spinner.style.width = '100vw';
            spinner.style.height = '100vh';
            spinner.style.background = 'rgba(255,255,255,0.7)';
            spinner.style.display = 'flex';
            spinner.style.alignItems = 'center';
            spinner.style.justifyContent = 'center';
            spinner.style.zIndex = '9999';
            spinner.innerHTML = `<div class='spinner-border text-primary' style='width: 4rem; height: 4rem;' role='status'><span class='visually-hidden'>Cargando...</span></div>`;
            document.body.appendChild(spinner);
        });
    });
</script>
@endsection
