<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->foreignId('novedad_registrada_por')
                ->nullable()
                ->after('pdf_arl_novedad')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario Dependencia que registró la adición/prórroga');
        });
    }

    public function down(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'novedad_registrada_por');
            $table->dropColumn('novedad_registrada_por');
        });
    }
};
