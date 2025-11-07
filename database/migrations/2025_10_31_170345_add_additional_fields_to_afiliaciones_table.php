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
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->after('telefono_contratista');
            $table->string('barrio')->nullable()->after('fecha_nacimiento');
            $table->string('direccion_residencia')->nullable()->after('barrio');
            $table->string('eps')->nullable()->after('direccion_residencia');
            $table->string('afp')->nullable()->after('eps');
            $table->decimal('honorarios_mensual', 15, 2)->nullable()->after('valor_contrato');
            $table->decimal('ibc', 15, 2)->nullable()->after('honorarios_mensual');
            $table->integer('meses_contrato')->nullable()->after('fecha_fin');
            $table->integer('dias_contrato')->nullable()->after('meses_contrato');
            $table->date('fecha_terminacion_afiliacion')->nullable()->after('fecha_afiliacion_arl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_nacimiento',
                'barrio',
                'direccion_residencia',
                'eps',
                'afp',
                'honorarios_mensual',
                'ibc',
                'meses_contrato',
                'dias_contrato',
                'fecha_terminacion_afiliacion'
            ]);
        });
    }
};
