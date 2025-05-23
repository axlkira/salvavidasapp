<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateMessagesTableCharset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cambiar el charset de la tabla messages a utf8mb4
        DB::statement('ALTER TABLE messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        
        // Específicamente cambiar la columna content
        DB::statement('ALTER TABLE messages MODIFY content TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir a la configuración anterior si es necesario
        DB::statement('ALTER TABLE messages CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci');
        DB::statement('ALTER TABLE messages MODIFY content TEXT CHARACTER SET utf8 COLLATE utf8_general_ci');
    }
}
