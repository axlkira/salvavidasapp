<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TObsBloque1 extends Model
{
    protected $table = 't_obs_bloque1';
    protected $primaryKey = ['tipo_documento', 'numero_documento'];
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'profesional_documento',
        'estado',
        // Preguntas
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
    ];

    // Para llaves compuestas
    public function setKeysForSaveQuery($query)
    {
        $query->where('tipo_documento', $this->getAttribute('tipo_documento'));
        $query->where('numero_documento', $this->getAttribute('numero_documento'));
        return $query;
    }
}
