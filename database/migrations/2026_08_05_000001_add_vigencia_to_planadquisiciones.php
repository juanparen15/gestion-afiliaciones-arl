<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vigencia explícita del Plan de Adquisiciones. Antes se deducía de created_at
 * (poco fiable para vincular con procesos/contratos). Se agrega un campo propio,
 * inicializado con el año de created_at (ajustable después).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planadquisiciones', function (Blueprint $table) {
            $table->unsignedSmallInteger('vigencia')->nullable()->after('id_vigencia')->index();
        });

        // Backfill inicial = año de created_at.
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement("UPDATE planadquisiciones SET vigencia = CAST(strftime('%Y', created_at) AS INTEGER) WHERE vigencia IS NULL");
        } else {
            DB::statement('UPDATE planadquisiciones SET vigencia = YEAR(created_at) WHERE vigencia IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('planadquisiciones', function (Blueprint $table) {
            $table->dropColumn('vigencia');
        });
    }
};
