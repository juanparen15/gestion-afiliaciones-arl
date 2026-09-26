<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla principal de proyectos
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_proyecto');
            $table->string('codigo_bpim')->nullable();
            $table->string('codigo_bpin')->nullable();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->decimal('valor_total_con_ajuste', 15, 2)->default(0);
            $table->decimal('valor_ejecutado', 15, 2)->default(0); // Se calcula automáticamente
            $table->decimal('valor_restante', 15, 2)->default(0); // Se calcula automáticamente
            $table->decimal('porcentaje_ejecucion', 5, 2)->default(0); // Porcentaje ejecutado
            $table->text('descripcion')->nullable();
            $table->string('estado')->default('activo'); // activo, finalizado, cancelado
            $table->timestamps();
            $table->softDeletes();

            // Índices para búsquedas
            $table->index('nombre_proyecto');
            $table->index('codigo_bpim');
            $table->index('codigo_bpin');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
