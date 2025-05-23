<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->enum('role', ['system', 'user', 'assistant']); // Rol del mensaje: sistema, usuario o asistente
            $table->text('content'); // Contenido del mensaje
            $table->timestamps();
            
            // Clave foránea para la relación con la tabla de conversaciones
            $table->foreign('conversation_id')
                  ->references('id')
                  ->on('conversations')
                  ->onDelete('cascade'); // Si se elimina la conversación, se eliminan todos sus mensajes
            
            // Índice para búsquedas rápidas
            $table->index('conversation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
