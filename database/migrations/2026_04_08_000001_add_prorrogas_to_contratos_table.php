<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            // Prórroga 1
            $table->date('fecha_prorroga_1')->nullable()->after('plazo_dias_adicional_3');
            $table->unsignedSmallInteger('plazo_anos_prorroga_1')->nullable()->after('fecha_prorroga_1');
            $table->unsignedSmallInteger('plazo_meses_prorroga_1')->nullable()->after('plazo_anos_prorroga_1');
            $table->unsignedSmallInteger('plazo_dias_prorroga_1')->nullable()->after('plazo_meses_prorroga_1');
            // Prórroga 2
            $table->date('fecha_prorroga_2')->nullable()->after('plazo_dias_prorroga_1');
            $table->unsignedSmallInteger('plazo_anos_prorroga_2')->nullable()->after('fecha_prorroga_2');
            $table->unsignedSmallInteger('plazo_meses_prorroga_2')->nullable()->after('plazo_anos_prorroga_2');
            $table->unsignedSmallInteger('plazo_dias_prorroga_2')->nullable()->after('plazo_meses_prorroga_2');
            // Prórroga 3
            $table->date('fecha_prorroga_3')->nullable()->after('plazo_dias_prorroga_2');
            $table->unsignedSmallInteger('plazo_anos_prorroga_3')->nullable()->after('fecha_prorroga_3');
            $table->unsignedSmallInteger('plazo_meses_prorroga_3')->nullable()->after('plazo_anos_prorroga_3');
            $table->unsignedSmallInteger('plazo_dias_prorroga_3')->nullable()->after('plazo_meses_prorroga_3');
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_prorroga_1', 'plazo_anos_prorroga_1', 'plazo_meses_prorroga_1', 'plazo_dias_prorroga_1',
                'fecha_prorroga_2', 'plazo_anos_prorroga_2', 'plazo_meses_prorroga_2', 'plazo_dias_prorroga_2',
                'fecha_prorroga_3', 'plazo_anos_prorroga_3', 'plazo_meses_prorroga_3', 'plazo_dias_prorroga_3',
            ]);
        });
    }
};
