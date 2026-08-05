<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo del equipo de contratación (quién elaboró / aprobó).
        Schema::create('elaboradores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('cargo')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Procesos de selección (mínima/menor cuantía, subasta, concurso, licitación).
        Schema::create('procesos_seleccion', function (Blueprint $table) {
            $table->id();
            $table->string('consecutivo')->nullable();               // ej. "001"
            $table->date('fecha')->nullable();
            $table->text('objeto')->nullable();                      // objeto abreviado
            $table->string('modalidad');                             // MINIMA_CUANTIA, MENOR_CUANTIA, ...
            $table->foreignId('dependencia_id')->nullable()->constrained('dependencias')->restrictOnDelete();
            $table->string('dependencia_nombre')->nullable();        // texto original (respaldo)
            $table->string('consecutivo_paa')->nullable();           // ej. "2026-221"
            $table->foreignId('planadquisicione_id')->nullable()->constrained('planadquisiciones')->nullOnDelete();
            $table->foreignId('elaborador_id')->nullable()->constrained('elaboradores')->restrictOnDelete();
            $table->string('estado')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['modalidad', 'consecutivo']);
            $table->index('consecutivo_paa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procesos_seleccion');
        Schema::dropIfExists('elaboradores');
    }
};
