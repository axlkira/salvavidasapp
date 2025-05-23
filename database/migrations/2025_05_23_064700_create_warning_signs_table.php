<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarningSignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warning_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_assessment_id')->constrained()->onDelete('cascade');
            $table->string('type', 50)->nullable(); // Tipo de señal (comportamental, verbal, etc.)
            $table->text('description'); // Descripción de la señal
            $table->integer('severity')->default(1); // Gravedad de la señal (1-5)
            $table->string('source', 100)->nullable(); // Fuente de la señal (mensaje, historia clínica, etc.)
            $table->text('context')->nullable(); // Contexto de la señal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warning_signs');
    }
}
