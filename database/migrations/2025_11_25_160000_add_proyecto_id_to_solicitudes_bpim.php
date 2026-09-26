<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_bpim', function (Blueprint $table) {
            $table->foreignId('proyecto_id')
                ->nullable()
                ->after('correo_notificacion')
                ->constrained('proyectos')
                ->nullOnDelete();
        });

        Schema::table('presupuesto_items', function (Blueprint $table) {
            $table->foreignId('item_catalogo_id')
                ->nullable()
                ->after('solicitud_bpim_id')
                ->constrained('items_catalogo')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_bpim', function (Blueprint $table) {
            $table->dropForeign(['proyecto_id']);
            $table->dropColumn('proyecto_id');
        });

        Schema::table('presupuesto_items', function (Blueprint $table) {
            $table->dropForeign(['item_catalogo_id']);
            $table->dropColumn('item_catalogo_id');
        });
    }
};
