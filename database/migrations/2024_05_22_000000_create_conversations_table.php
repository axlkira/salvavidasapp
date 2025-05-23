<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('professional_id')->nullable(); // ID del profesional
            $table->string('patient_document')->nullable(); // Documento del paciente (si aplica)
            $table->string('title'); // Título de la conversación
            $table->string('provider')->default('ollama'); // Proveedor de IA
            $table->string('model')->default('llama3'); // Modelo de IA
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('professional_id');
            $table->index('patient_document');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversations');
    }
}
