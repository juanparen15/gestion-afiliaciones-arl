<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actas_necesidad', function (Blueprint $table) {
            // Vigencia (año) del PAA seleccionada. Se persiste porque el N° Reg
            // (id_vigencia) se repite entre años y antes se adivinaba mal.
            if (! Schema::hasColumn('actas_necesidad', 'paa_vigencia')) {
                $table->smallInteger('paa_vigencia')->unsigned()->nullable()->after('codigo_paa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('actas_necesidad', function (Blueprint $table) {
            $table->dropColumn('paa_vigencia');
        });
    }
};
