<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Renombra la tabla 'comentarios' a 'subtareas' y agrega columna 'estado'.
     * Las subtareas son tareas secundarias que pertenecen a una tarea principal.
     */
    public function up(): void
    {
        // 1. Renombrar tabla
        Schema::rename('comentarios', 'subtareas');

        // 2. Agregar columna estado
        Schema::table('subtareas', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'completada'])
                ->default('pendiente')
                ->after('usuario_id')
                ->comment('Estado de la subtarea');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Revierte los cambios: elimina columna estado y renombra tabla de vuelta.
     */
    public function down(): void
    {
        // 1. Eliminar columna estado
        Schema::table('subtareas', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        // 2. Renombrar tabla de vuelta
        Schema::rename('subtareas', 'comentarios');
    }
};
