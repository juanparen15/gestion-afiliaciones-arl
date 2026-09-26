<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ítems dentro de cada capítulo del proyecto
        Schema::create('capitulo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_capitulo_id')
                ->constrained('proyecto_capitulos')
                ->onDelete('cascade');
            $table->foreignId('item_catalogo_id')
                ->nullable()
                ->constrained('items_catalogo')
                ->onDelete('set null'); // Si se elimina del catálogo, mantener el registro

            // Campos del ítem (copiados del catálogo o editables)
            $table->string('etapa')->nullable();
            $table->string('tipo')->nullable();
            $table->string('item'); // Nombre del ítem
            $table->integer('anio')->nullable(); // Año de ejecución
            $table->text('descripcion'); // Descripción del ítem
            $table->string('perfil')->nullable(); // Perfil requerido
            $table->decimal('valor_unitario', 15, 2); // Valor unitario del ítem
            $table->string('unidad'); // Unidad de medida
            $table->decimal('cantidad', 10, 2); // Cantidad solicitada
            $table->decimal('valor_total', 15, 2); // valor_unitario * cantidad

            // Control de ejecución
            $table->decimal('cantidad_ejecutada', 10, 2)->default(0); // Cantidad ya ejecutada
            $table->decimal('valor_ejecutado', 15, 2)->default(0); // Valor ya ejecutado
            $table->decimal('valor_restante', 15, 2)->default(0); // Valor restante

            $table->integer('orden')->default(0); // Orden dentro del capítulo
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('proyecto_capitulo_id');
            $table->index('item_catalogo_id');
            $table->index('etapa');
            $table->index('tipo');
            $table->index('anio');
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capitulo_items');
    }
};
