<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items_catalogo', function (Blueprint $table) {
            // Agregar proyecto origen del item
            $table->foreignId('proyecto_origen_id')
                ->nullable()
                ->after('id')
                ->constrained('proyectos')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items_catalogo', function (Blueprint $table) {
            $table->dropForeign(['proyecto_origen_id']);
            $table->dropColumn('proyecto_origen_id');
        });
    }
};
