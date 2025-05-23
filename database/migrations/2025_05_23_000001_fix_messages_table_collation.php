<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixMessagesTableCollation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modificar la tabla messages para usar la misma colación en toda la base de datos
        DB::statement('ALTER TABLE messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        
        // Convertir específicamente la columna content
        DB::statement('ALTER TABLE messages MODIFY content LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        
        // Asegurar que no haya mezcla de colaciones
        DB::statement('ALTER DATABASE ' . DB::connection()->getDatabaseName() . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No es necesario revertir estos cambios
    }
}
