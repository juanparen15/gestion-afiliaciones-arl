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
            // Agregar cantidad disponible/límite del item
            $table->integer('cantidad')->default(0)->after('unidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items_catalogo', function (Blueprint $table) {
            $table->dropColumn('cantidad');
        });
    }
};
