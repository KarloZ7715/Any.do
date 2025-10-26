<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['pendiente', 'completada'])->default('pendiente');
            $table->tinyInteger('prioridad')->unsigned()->default(2);
            $table->timestamp('fecha_completada')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('prioridad');
            $table->index('fecha_vencimiento');
        });

        // Agregar índice FULLTEXT solo si el driver lo soporta
        try {
            $driver = Schema::connection(null)->getConnection()->getDriverName();
            if ($driver !== 'sqlite') {
                Schema::table('tareas', function (Blueprint $table) {
                    $table->fullText(['titulo', 'descripcion'], 'idx_busqueda');
                });
            }
        } catch (\Exception $e) {
            // Silenciar error en SQLite para testing
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
