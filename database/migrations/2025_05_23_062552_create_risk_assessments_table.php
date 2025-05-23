<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskAssessmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('individual_id')->nullable()->comment('ID de la historia clínica relacionada');
            $table->string('patient_document')->nullable()->comment('Documento del paciente');
            $table->unsignedBigInteger('professional_id')->nullable()->comment('ID del profesional que realizó la evaluación');
            $table->float('risk_score')->default(0)->comment('Puntuación de riesgo calculada');
            $table->enum('risk_level', ['bajo', 'medio', 'alto', 'crítico'])->default('bajo')->comment('Nivel de riesgo');
            $table->string('provider')->default('sistema')->comment('Proveedor del análisis');
            $table->string('model')->default('risk-detection-v1')->comment('Modelo usado para la evaluación');
            $table->enum('status', ['pending', 'reviewed', 'archived'])->default('pending')->comment('Estado de la evaluación');
            $table->timestamp('reviewed_at')->nullable()->comment('Fecha de revisión');
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('ID del usuario que revisó');
            $table->timestamps();
            
            // Índices
            $table->index('patient_document');
            $table->index('risk_level');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_assessments');
    }
}
