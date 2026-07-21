<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar 'anulado' al enum de estado
        DB::statement("ALTER TABLE actas_necesidad MODIFY estado ENUM('pendiente','aprobado','rechazado','anulado') NOT NULL DEFAULT 'pendiente'");

        Schema::table('actas_necesidad', function (Blueprint $table) {
            $table->string('codigo_verificacion', 40)->nullable()->unique()->after('consecutivo')
                ->comment('Código único para validar autenticidad vía QR');
            $table->text('motivo_anulacion')->nullable()->after('motivo_rechazo');
            $table->foreignId('anulado_por')->nullable()->after('aprobado_por')->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_anulacion')->nullable()->after('fecha_aprobado');
        });
    }

    public function down(): void
    {
        Schema::table('actas_necesidad', function (Blueprint $table) {
            $table->dropConstrainedForeignId('anulado_por');
            $table->dropColumn(['codigo_verificacion', 'motivo_anulacion', 'fecha_anulacion']);
        });
        DB::statement("ALTER TABLE actas_necesidad MODIFY estado ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente'");
    }
};
