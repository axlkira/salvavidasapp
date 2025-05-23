<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TObsBloque1;
use Illuminate\Support\Facades\DB;

class Bloque1NuevoController extends Controller
{
    public function create(Request $request)
    {
        $registro = null;
        if ($request->has(['tipo_documento', 'numero_documento'])) {
            $registro = TObsBloque1::where('tipo_documento', $request->tipo_documento)
                ->where('numero_documento', $request->numero_documento)
                ->first();
        }
        return view('forms.bloque_1_nuevo', compact('registro'));
    }

    public function store(Request $request)
    {
        $rules = [
            'tipo_documento' => 'required',
            'numero_documento' => 'required',
            'profesional_documento' => 'required',
            // Puedes agregar validaciones específicas para cada pregunta aquí
        ];
        $validated = $request->validate($rules);

        // Preparar datos para guardar
        $data = $request->only([
            'tipo_documento',
            'numero_documento',
            'profesional_documento',
            // Preguntas 1 a 16 (agrega aquí todos los campos de preguntas según tu migración y vista)
            'p1_comuna',
            'p2_estrato_conocimiento',
            'p3_personas_integran',
            'p4_familia_nuclear', 'p4_mixta_compleja', 'p4_bicultural', 'p4_homoparental', 'p4_monoparental',
            'p4_reconstituida', 'p4_adoptiva', 'p4_extensa', 'p4_transnacional', 'p4_campesina',
            'p4_multiespecie', 'p4_unipersonal', 'p4_poliamorosa', 'p4_dink',
            'p5_elemento1', 'p5_elemento2', 'p5_elemento3', 'p5_elemento4', 'p5_elemento5', 'p5_elemento6', 'p5_elemento7',
            'p6_primera_infancia', 'p6_jovenes', 'p6_adultos', 'p6_adultos_mayores',
            'p7_indigena', 'p7_afrodescendiente', 'p7_mestizo', 'p7_room_gitano', 'p7_raizal',
            'p7_palenquero', 'p7_negro', 'p7_ninguno', 'p7_prefiero_no_decirlo',
            'p8_maximo_educativo',
            'p9_integrantes_fuerzas_armadas',
            'p10_hecho1', 'p10_hecho2', 'p10_hecho3', 'p10_hecho4', 'p10_hecho5', 'p10_hecho6', 'p10_hecho7', 'p10_hecho8',
            'p10_hecho9', 'p10_hecho10', 'p10_hecho11', 'p10_hecho12', 'p10_hecho13', 'p10_hecho14', 'p10_hecho15', 'p10_hecho16',
            'p11_cuantas_personas',
            'p12_jefatura',
            'p13_habla_permanente',
        ]);

        // Opciones múltiples: transformar checkboxes (si no están presentes, poner 2)
        $checkboxes = [
            // Ejemplo para pregunta 4
            'p4_familia_nuclear','p4_mixta_compleja','p4_bicultural','p4_homoparental','p4_monoparental',
            'p4_reconstituida','p4_adoptiva','p4_extensa','p4_transnacional','p4_campesina','p4_multiespecie','p4_unipersonal','p4_poliamorosa','p4_dink',
            // Igual para preguntas 5, 6, 7, 10
            'p5_elemento1','p5_elemento2','p5_elemento3','p5_elemento4','p5_elemento5','p5_elemento6','p5_elemento7',
            'p6_primera_infancia','p6_jovenes','p6_adultos','p6_adultos_mayores',
            'p7_indigena','p7_afrodescendiente','p7_mestizo','p7_room_gitano','p7_raizal','p7_palenquero','p7_negro','p7_ninguno','p7_prefiero_no_decirlo',
            'p10_hecho1','p10_hecho2','p10_hecho3','p10_hecho4','p10_hecho5','p10_hecho6','p10_hecho7','p10_hecho8','p10_hecho9','p10_hecho10','p10_hecho11','p10_hecho12','p10_hecho13','p10_hecho14','p10_hecho15','p10_hecho16',
        ];
        foreach ($checkboxes as $cb) {
            $data[$cb] = $request->has($cb) ? 1 : 2;
        }

        // Guardar o actualizar
        $registro = TObsBloque1::updateOrCreate(
            [
                'tipo_documento' => $data['tipo_documento'],
                'numero_documento' => $data['numero_documento']
            ],
            $data
        );

        return redirect()->back()->with('success', '¡Formulario guardado exitosamente!');
    }
}
