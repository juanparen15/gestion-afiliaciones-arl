<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->foreignId('dependencia_id')
                ->nullable()
                ->after('dependencia_contrato')
                ->constrained('dependencias')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Dependencia::class);
            $table->dropColumn('dependencia_id');
        });
    }
};
