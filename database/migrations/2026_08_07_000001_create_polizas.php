<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de aprobación de pólizas, enlazado al contrato (registro), a la
 * dependencia y al elaborador que proyecta/aprueba.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polizas', function (Blueprint $table) {
            $table->id();
            $table->string('consecutivo')->nullable();
            $table->date('fecha')->nullable();
            $table->string('contrato_texto')->nullable();            // ej. "468 de 2025"
            $table->foreignId('contrato_registro_id')->nullable()->constrained('contrato_registros')->nullOnDelete();
            $table->string('estado')->nullable();                    // APROBACION | ADICION | PRORROGA | ...
            $table->foreignId('dependencia_id')->nullable()->constrained('dependencias')->restrictOnDelete();
            $table->string('dependencia_nombre')->nullable();
            $table->foreignId('aprobador_id')->nullable()->constrained('elaboradores')->restrictOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('consecutivo');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polizas');
    }
};
