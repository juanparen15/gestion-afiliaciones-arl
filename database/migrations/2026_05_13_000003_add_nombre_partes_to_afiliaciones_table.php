<?php

use App\Models\Afiliacion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->string('primer_nombre')->nullable()->after('nombre_contratista');
            $table->string('segundo_nombre')->nullable()->after('primer_nombre');
            $table->string('primer_apellido')->nullable()->after('segundo_nombre');
            $table->string('segundo_apellido')->nullable()->after('primer_apellido');
        });

        // Backfill: separar el nombre completo existente en las 4 partes
        DB::table('afiliaciones')
            ->select('id', 'nombre_contratista')
            ->whereNotNull('nombre_contratista')
            ->orderBy('id')
            ->chunk(200, function ($filas) {
                foreach ($filas as $fila) {
                    $partes = Afiliacion::dividirNombre($fila->nombre_contratista);
                    DB::table('afiliaciones')->where('id', $fila->id)->update([
                        'primer_nombre'    => $partes['primer_nombre'] ?: null,
                        'segundo_nombre'   => $partes['segundo_nombre'] ?: null,
                        'primer_apellido'  => $partes['primer_apellido'] ?: null,
                        'segundo_apellido' => $partes['segundo_apellido'] ?: null,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->dropColumn(['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido']);
        });
    }
};
