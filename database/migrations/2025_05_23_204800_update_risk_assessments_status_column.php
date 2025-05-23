<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRiskAssessmentsStatusColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('risk_assessments', function (Blueprint $table) {
            // Cambiar el tipo de la columna 'status' a VARCHAR para aceptar valores más largos
            $table->string('status', 30)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('risk_assessments', function (Blueprint $table) {
            //
        });
    }
}
