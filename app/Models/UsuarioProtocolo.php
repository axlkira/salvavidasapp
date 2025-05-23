<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioProtocolo extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'familiam_bdprotocoloservidor.t_usuarioprotocolo';

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
        'apellido2'
    ];

    /**
     * Obtener el nombre completo del profesional.
     *
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre1 . ' ' . $this->nombre2 . ' ' . $this->apellido1 . ' ' . $this->apellido2);
    }

    /**
     * Obtener las historias clínicas relacionadas con este profesional.
     */
    public function historiasClinicas()
    {
        return $this->hasMany(Individual::class, 'Profesional', 'documento');
    }
}
