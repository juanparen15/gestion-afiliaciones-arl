<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro unificado de contratos, convenios y comodatos (consecutivos internos),
 * enlazado a Proceso de Selección, Plan de Adquisiciones, dependencia y elaborador.
 * Distinto de 'contratos' (SECOP), al que puede apuntar opcionalmente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_registros', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');                                  // CONTRATO | CONVENIO | COMODATO
            $table->string('numero')->nullable();                    // No. contrato / ITEM
            $table->date('fecha')->nullable();
            $table->string('contratista')->nullable();
            $table->string('proceso_texto')->nullable();             // ej. "CD-CPS 001 DE 2026"
            $table->foreignId('proceso_seleccion_id')->nullable()->constrained('procesos_seleccion')->nullOnDelete();
            $table->string('modalidad')->nullable();
            $table->foreignId('dependencia_id')->nullable()->constrained('dependencias')->restrictOnDelete();
            $table->string('dependencia_nombre')->nullable();
            $table->string('consecutivo_paa')->nullable();           // "2026-221"
            $table->foreignId('planadquisicione_id')->nullable()->constrained('planadquisiciones')->nullOnDelete();
            $table->foreignId('elaborador_id')->nullable()->constrained('elaboradores')->restrictOnDelete();
            $table->decimal('valor', 18, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('contrato_secop_id')->nullable()->constrained('contratos')->nullOnDelete();
            $table->timestamps();

            $table->index(['tipo', 'numero']);
            $table->index('consecutivo_paa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_registros');
    }
};
