<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Elimina columna usuario_id de subtareas (redundante).
     * El usuario se obtiene a través de la relación tarea->usuario.
     */
    public function up(): void
    {
        Schema::table('subtareas', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign('fk_comentarios_usuario_id');
            } else {
                $table->dropForeign(['usuario_id']);
            }

            // Eliminar columna
            $table->dropColumn('usuario_id');
        });
    }    /**
         * Reverse the migrations.
         * 
         * Restaura la columna usuario_id con su foreign key.
         */
    public function down(): void
    {
        Schema::table('subtareas', function (Blueprint $table) {
            // Agregar columna de vuelta
            $table->foreignId('usuario_id')
                ->after('tarea_id')
                ->constrained('usuarios')
                ->onDelete('cascade');
        });
    }
};
