<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            // Modificar columnas para permitir NULL
            $table->decimal('valor_total', 15, 2)->nullable()->default(0)->change();
            $table->decimal('valor_ejecutado', 15, 2)->nullable()->default(0)->change();
            $table->decimal('valor_restante', 15, 2)->nullable()->default(0)->change();
            $table->decimal('porcentaje_ejecucion', 5, 2)->nullable()->default(0)->change();
        });

        Schema::table('items_catalogo', function (Blueprint $table) {
            // Hacer que valor_total_disponible tenga valor por defecto
            $table->decimal('valor_total_disponible', 15, 2)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->decimal('valor_total', 15, 2)->default(0)->change();
            $table->decimal('valor_ejecutado', 15, 2)->default(0)->change();
            $table->decimal('valor_restante', 15, 2)->default(0)->change();
            $table->decimal('porcentaje_ejecucion', 5, 2)->default(0)->change();
        });

        Schema::table('items_catalogo', function (Blueprint $table) {
            $table->decimal('valor_total_disponible', 15, 2)->default(0)->change();
        });
    }
};
