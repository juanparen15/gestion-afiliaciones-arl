<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procesos_seleccion', function (Blueprint $table) {
            $table->unsignedSmallInteger('vigencia')->nullable()->after('modalidad')->index();
        });

        $driver = Schema::getConnection()->getDriverName();
        $anio = $driver === 'sqlite'
            ? "CAST(strftime('%Y', COALESCE(fecha, created_at)) AS INTEGER)"
            : 'YEAR(COALESCE(fecha, created_at))';
        DB::statement("UPDATE procesos_seleccion SET vigencia = {$anio} WHERE vigencia IS NULL");

        // Renombrar la modalidad LICITACION → LICITACION_PUBLICA (nueva nomenclatura).
        DB::table('procesos_seleccion')->where('modalidad', 'LICITACION')->update(['modalidad' => 'LICITACION_PUBLICA']);
    }

    public function down(): void
    {
        Schema::table('procesos_seleccion', function (Blueprint $table) {
            $table->dropColumn('vigencia');
        });
    }
};
