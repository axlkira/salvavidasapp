<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('t_obs_bloque1', function (Blueprint $table) {
            $table->string('tipo_documento', 3);
            $table->string('numero_documento', 30);
            $table->string('profesional_documento', 30);
            $table->integer('estado')->default(0);

            // Pregunta 1
            $table->unsignedTinyInteger('p1_comuna')->nullable();

            // Pregunta 2
            $table->unsignedTinyInteger('p2_estrato_conocimiento')->nullable();

            // Pregunta 3
            $table->unsignedTinyInteger('p3_personas_integran')->nullable();

            // Pregunta 4 (opción múltiple)
            $table->unsignedTinyInteger('p4_familia_nuclear')->nullable();
            $table->unsignedTinyInteger('p4_mixta_compleja')->nullable();
            $table->unsignedTinyInteger('p4_bicultural')->nullable();
            $table->unsignedTinyInteger('p4_homoparental')->nullable();
            $table->unsignedTinyInteger('p4_monoparental')->nullable();
            $table->unsignedTinyInteger('p4_reconstituida')->nullable();
            $table->unsignedTinyInteger('p4_adoptiva')->nullable();
            $table->unsignedTinyInteger('p4_extensa')->nullable();
            $table->unsignedTinyInteger('p4_transnacional')->nullable();
            $table->unsignedTinyInteger('p4_campesina')->nullable();
            $table->unsignedTinyInteger('p4_multiespecie')->nullable();
            $table->unsignedTinyInteger('p4_unipersonal')->nullable();
            $table->unsignedTinyInteger('p4_poliamorosa')->nullable();
            $table->unsignedTinyInteger('p4_dink')->nullable();

            // Pregunta 5 (opción múltiple)
            $table->unsignedTinyInteger('p5_elemento1')->nullable();
            $table->unsignedTinyInteger('p5_elemento2')->nullable();
            $table->unsignedTinyInteger('p5_elemento3')->nullable();
            $table->unsignedTinyInteger('p5_elemento4')->nullable();
            $table->unsignedTinyInteger('p5_elemento5')->nullable();
            $table->unsignedTinyInteger('p5_elemento6')->nullable();
            $table->unsignedTinyInteger('p5_elemento7')->nullable();

            // Pregunta 6 (opción múltiple)
            $table->unsignedTinyInteger('p6_primera_infancia')->nullable();
            $table->unsignedTinyInteger('p6_jovenes')->nullable();
            $table->unsignedTinyInteger('p6_adultos')->nullable();
            $table->unsignedTinyInteger('p6_adultos_mayores')->nullable();

            // Pregunta 7 (opción múltiple)
            $table->unsignedTinyInteger('p7_indigena')->nullable();
            $table->unsignedTinyInteger('p7_afrodescendiente')->nullable();
            $table->unsignedTinyInteger('p7_mestizo')->nullable();
            $table->unsignedTinyInteger('p7_room_gitano')->nullable();
            $table->unsignedTinyInteger('p7_raizal')->nullable();
            $table->unsignedTinyInteger('p7_palenquero')->nullable();
            $table->unsignedTinyInteger('p7_negro')->nullable();
            $table->unsignedTinyInteger('p7_ninguno')->nullable();
            $table->unsignedTinyInteger('p7_prefiero_no_decirlo')->nullable();

            // Pregunta 8
            $table->unsignedTinyInteger('p8_maximo_educativo')->nullable();

            // Pregunta 9
            $table->unsignedTinyInteger('p9_integrantes_fuerzas_armadas')->nullable();

            // Pregunta 10 (opción múltiple, 16 hechos)
            $table->unsignedTinyInteger('p10_hecho1')->nullable();
            $table->unsignedTinyInteger('p10_hecho2')->nullable();
            $table->unsignedTinyInteger('p10_hecho3')->nullable();
            $table->unsignedTinyInteger('p10_hecho4')->nullable();
            $table->unsignedTinyInteger('p10_hecho5')->nullable();
            $table->unsignedTinyInteger('p10_hecho6')->nullable();
            $table->unsignedTinyInteger('p10_hecho7')->nullable();
            $table->unsignedTinyInteger('p10_hecho8')->nullable();
            $table->unsignedTinyInteger('p10_hecho9')->nullable();
            $table->unsignedTinyInteger('p10_hecho10')->nullable();
            $table->unsignedTinyInteger('p10_hecho11')->nullable();
            $table->unsignedTinyInteger('p10_hecho12')->nullable();
            $table->unsignedTinyInteger('p10_hecho13')->nullable();
            $table->unsignedTinyInteger('p10_hecho14')->nullable();
            $table->unsignedTinyInteger('p10_hecho15')->nullable();
            $table->unsignedTinyInteger('p10_hecho16')->nullable();

            // Pregunta 11
            $table->unsignedTinyInteger('p11_cuantas_personas')->nullable();

            // Pregunta 12
            $table->unsignedTinyInteger('p12_jefatura')->nullable();

            // Pregunta 13
            $table->unsignedTinyInteger('p13_habla_permanente')->nullable();

            $table->primary(['tipo_documento', 'numero_documento']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_obs_bloque1');
    }
};
