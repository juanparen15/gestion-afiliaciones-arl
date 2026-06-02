<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->timestamp('novedad_registrada_at')
                ->nullable()
                ->after('novedad_registrada_por')
                ->comment('Fecha y hora en que se registró la adición/prórroga');
        });
    }

    public function down(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->dropColumn('novedad_registrada_at');
        });
    }
};
