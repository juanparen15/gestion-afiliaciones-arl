<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actas_necesidad', function (Blueprint $table) {
            $table->unsignedSmallInteger('duracion_valor_2')->nullable()->after('duracion_unidad');
            $table->string('duracion_unidad_2', 10)->nullable()->after('duracion_valor_2'); // DIAS | MESES | AÑOS
        });
    }

    public function down(): void
    {
        Schema::table('actas_necesidad', function (Blueprint $table) {
            $table->dropColumn(['duracion_valor_2', 'duracion_unidad_2']);
        });
    }
};
