<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrincipalIntegrante extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'familiam_modulo_cif.t1_principalintegrantes';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'documento';
    
    /**
     * Indica si la clave primaria es auto-incrementable.
     *
     * @var bool
     */
    public $incrementing = false;
    
    /**
     * El tipo de dato de la clave primaria.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indica si el modelo debe tener timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'documento',
        'nombre1',
        'nombre2',
        'apellido1',
        'apellido2',
        // Añade otros campos necesarios aquí
    ];

    /**
     * Obtener todas las historias clínicas de este paciente.
     */
    public function historiasClinicas()
    {
        return $this->hasMany(Individual::class, 'Documento', 'documento');
    }

    /**
     * Obtener el nombre completo del paciente.
     */
    public function getNombreCompletoAttribute()
    {
        return trim(implode(' ', [
            $this->nombre1 ?? '',
            $this->nombre2 ?? '',
            $this->apellido1 ?? '',
            $this->apellido2 ?? ''
        ]));
    }
}
