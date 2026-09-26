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
        Schema::table('solicitudes_bpim', function (Blueprint $table) {
            // Eliminar numero_item_presupuesto ya que ahora se selecciona del catálogo
            $table->dropColumn('numero_item_presupuesto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes_bpim', function (Blueprint $table) {
            // Restaurar numero_item_presupuesto
            $table->string('numero_item_presupuesto')->nullable()->after('cartera');
        });
    }
};
