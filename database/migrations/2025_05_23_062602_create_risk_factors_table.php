<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskFactorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_factors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_assessment_id')->constrained()->onDelete('cascade');
            $table->text('description')->comment('Descripción del factor de riesgo detectado');
            $table->string('factor_type')->nullable()->comment('Tipo de factor de riesgo');
            $table->float('weight')->default(1.0)->comment('Peso del factor en la puntuación');
            $table->text('context')->nullable()->comment('Contexto donde se detectó el factor');
            $table->string('source')->default('conversation')->comment('Fuente: conversation, historia_clinica, etc');
            $table->timestamps();
            
            // Índices
            $table->index('factor_type');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_factors');
    }
}
