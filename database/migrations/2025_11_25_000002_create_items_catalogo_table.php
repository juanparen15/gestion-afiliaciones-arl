<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo central de ítems disponibles
        Schema::create('items_catalogo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // Código único del ítem
            $table->string('nombre'); // Nombre corto del ítem
            $table->text('descripcion'); // Descripción completa del ítem
            $table->decimal('valor_unitario', 15, 2); // Valor unitario estándar
            $table->string('unidad'); // Unidad de medida (Unidad, Mes, Kg, etc.)
            $table->string('tipo')->nullable(); // Tipo de ítem (Suministro, Servicio, etc.)
            $table->string('etapa')->nullable(); // Etapa (Precontractual, Contractual, etc.)
            $table->string('perfil')->nullable(); // Perfil profesional si aplica
            $table->boolean('activo')->default(true); // Si está disponible para uso
            $table->decimal('valor_total_disponible', 15, 2)->default(0); // Valor total disponible en catálogo
            $table->decimal('valor_ejecutado', 15, 2)->default(0); // Valor total ya ejecutado
            $table->decimal('valor_restante', 15, 2)->default(0); // Valor restante disponible
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('codigo');
            $table->index('nombre');
            $table->index('tipo');
            $table->index('etapa');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_catalogo');
    }
};
