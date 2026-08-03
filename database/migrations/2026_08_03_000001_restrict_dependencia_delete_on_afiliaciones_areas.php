<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blindaje de datos: antes, borrar una dependencia arrastraba (cascade) sus
 * afiliaciones y áreas. Se cambia a RESTRICT para que la base de datos impida
 * eliminar una dependencia con esos datos asociados.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return; // SQLite (tests) no soporta alterar llaves foráneas; basta el guard del modelo.
        }

        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->dropForeign(['dependencia_id']);
            $table->foreign('dependencia_id')->references('id')->on('dependencias')->restrictOnDelete();
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign(['dependencia_id']);
            $table->foreign('dependencia_id')->references('id')->on('dependencias')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->dropForeign(['dependencia_id']);
            $table->foreign('dependencia_id')->references('id')->on('dependencias')->cascadeOnDelete();
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign(['dependencia_id']);
            $table->foreign('dependencia_id')->references('id')->on('dependencias')->cascadeOnDelete();
        });
    }
};
