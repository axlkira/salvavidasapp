<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'conversation_id',
        'role', // 'user', 'assistant', 'system'
        'content',
        'created_at',
        'updated_at',
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener la conversación a la que pertenece este mensaje.
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
