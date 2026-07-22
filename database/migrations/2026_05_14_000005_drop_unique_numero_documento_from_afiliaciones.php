<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un mismo contratista puede tener varias afiliaciones (nuevos contratos),
     * por lo que el número de documento NO debe ser único. Se elimina el índice
     * único y se conserva el índice normal para búsquedas.
     */
    public function up(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->dropUnique('afiliaciones_numero_documento_unique');
        });
    }

    public function down(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->unique('numero_documento');
        });
    }
};
