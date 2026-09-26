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
        Schema::table('presupuesto_items', function (Blueprint $table) {
            // Renombrar meses a cantidad
            $table->renameColumn('meses', 'cantidad');

            // Agregar campo unidad
            $table->string('unidad')->nullable()->after('valor_unitario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presupuesto_items', function (Blueprint $table) {
            // Revertir los cambios
            $table->renameColumn('cantidad', 'meses');
            $table->dropColumn('unidad');
        });
    }
};
