<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Capítulos de cada proyecto
        Schema::create('proyecto_capitulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')
                ->constrained('proyectos')
                ->onDelete('cascade');
            $table->integer('numero_capitulo'); // Número del capítulo (I, II, III, etc.)
            $table->text('nombre_capitulo'); // Nombre completo editable del capítulo
            $table->decimal('valor_total', 15, 2)->default(0); // Suma de todos los ítems del capítulo
            $table->decimal('valor_ejecutado', 15, 2)->default(0); // Suma ejecutada en este capítulo
            $table->decimal('valor_restante', 15, 2)->default(0); // Valor restante del capítulo
            $table->integer('orden')->default(0); // Para ordenar los capítulos
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('proyecto_id');
            $table->index('numero_capitulo');
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_capitulos');
    }
};
