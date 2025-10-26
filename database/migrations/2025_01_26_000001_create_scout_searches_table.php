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
        Schema::create('scout_searches', function (Blueprint $table) {
            $table->id();
            $table->string('index_name');
            $table->string('searchable_type');
            $table->unsignedBigInteger('searchable_id');
            $table->text('searchable_text')->nullable();
            $table->timestamps();

            $table->index(['index_name', 'searchable_type', 'searchable_id']);
        });

        // Agregar índice FULLTEXT solo si el driver lo soporta
        try {
            $driver = Schema::connection(null)->getConnection()->getDriverName();
            if ($driver !== 'sqlite') {
                Schema::table('scout_searches', function (Blueprint $table) {
                    $table->fullText('searchable_text');
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
        Schema::dropIfExists('scout_searches');
    }
};
