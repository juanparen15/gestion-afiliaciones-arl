<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actas_necesidad', function (Blueprint $table) {
            $table->string('nombre_secretario_supervisor')->nullable()->after('nombre_solicitante');
            $table->unsignedSmallInteger('duracion_valor')->nullable()->after('duracion');
            $table->string('duracion_unidad', 10)->nullable()->after('duracion_valor'); // DIAS | MESES | AÑOS
        });
    }

    public function down(): void
    {
        Schema::table('actas_necesidad', function (Blueprint $table) {
            $table->dropColumn(['nombre_secretario_supervisor', 'duracion_valor', 'duracion_unidad']);
        });
    }
};
