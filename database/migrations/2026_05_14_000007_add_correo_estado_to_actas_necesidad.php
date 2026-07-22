<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actas_necesidad', function (Blueprint $table) {
            $table->boolean('correo_enviado')->default(false)->after('pdf_path');
            $table->timestamp('correo_enviado_at')->nullable()->after('correo_enviado');
            $table->text('correo_error')->nullable()->after('correo_enviado_at');
        });
    }

    public function down(): void
    {
        Schema::table('actas_necesidad', function (Blueprint $table) {
            $table->dropColumn(['correo_enviado', 'correo_enviado_at', 'correo_error']);
        });
    }
};
