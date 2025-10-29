<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->boolean('es_personal')->default(false)->after('usuario_id');
        });

        // Marcar todas las categorías "Personal" existentes
        DB::table('categorias')
            ->where('nombre', 'Personal')
            ->update(['es_personal' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('es_personal');
        });
    }
};
