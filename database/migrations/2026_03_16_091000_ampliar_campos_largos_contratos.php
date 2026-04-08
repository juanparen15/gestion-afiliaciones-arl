<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ampliar a TEXT los campos descriptivos de contratos que exceden varchar(200).
     * Campos afectados: nombre_rubro, nombre_proyecto, programa, subprograma,
     * meta_plan_desarrollo, producto_mga, producto_cpc, descripcion_unspsc,
     * objeto, observaciones, funciones.
     */
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->text('nombre_rubro')->nullable()->change();
            $table->text('nombre_proyecto')->nullable()->change();
            $table->text('programa')->nullable()->change();
            $table->text('subprograma')->nullable()->change();
            $table->text('meta_plan_desarrollo')->nullable()->change();
            $table->text('producto_mga')->nullable()->change();
            $table->text('producto_cpc')->nullable()->change();
            $table->text('descripcion_unspsc')->nullable()->change();
            $table->text('objeto')->nullable()->change();
            $table->text('observaciones')->nullable()->change();
            $table->text('funciones')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->string('nombre_rubro', 500)->nullable()->change();
            $table->string('nombre_proyecto', 500)->nullable()->change();
            $table->string('programa', 500)->nullable()->change();
            $table->string('subprograma', 500)->nullable()->change();
            $table->string('meta_plan_desarrollo', 500)->nullable()->change();
            $table->string('producto_mga', 500)->nullable()->change();
            $table->string('producto_cpc', 500)->nullable()->change();
            $table->string('descripcion_unspsc', 500)->nullable()->change();
            $table->string('objeto', 500)->nullable()->change();
            $table->string('observaciones', 500)->nullable()->change();
            $table->string('funciones', 500)->nullable()->change();
        });
    }
};
