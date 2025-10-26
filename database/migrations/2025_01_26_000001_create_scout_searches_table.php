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
            $table->fullText('searchable_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_searches');
    }
};
