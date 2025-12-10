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
            $table->boolean('tiene_adicion')->default(false)->after('supervisor_contrato');
            $table->text('descripcion_adicion')->nullable()->after('tiene_adicion');
            $table->decimal('valor_adicion', 15, 2)->nullable()->after('descripcion_adicion');
            $table->date('fecha_adicion')->nullable()->after('valor_adicion');

            $table->boolean('tiene_prorroga')->default(false)->after('fecha_adicion');
            $table->text('descripcion_prorroga')->nullable()->after('tiene_prorroga');
            $table->integer('meses_prorroga')->nullable()->after('descripcion_prorroga');
            $table->integer('dias_prorroga')->nullable()->after('meses_prorroga');
            $table->date('nueva_fecha_fin_prorroga')->nullable()->after('dias_prorroga');

            $table->boolean('tiene_terminacion_anticipada')->default(false)->after('nueva_fecha_fin_prorroga');
            $table->date('fecha_terminacion_anticipada')->nullable()->after('tiene_terminacion_anticipada');
            $table->text('motivo_terminacion_anticipada')->nullable()->after('fecha_terminacion_anticipada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->dropColumn([
                'tiene_adicion',
                'descripcion_adicion',
                'valor_adicion',
                'fecha_adicion',
                'tiene_prorroga',
                'descripcion_prorroga',
                'meses_prorroga',
                'dias_prorroga',
                'nueva_fecha_fin_prorroga',
                'tiene_terminacion_anticipada',
                'fecha_terminacion_anticipada',
                'motivo_terminacion_anticipada',
            ]);
        });
    }
};
