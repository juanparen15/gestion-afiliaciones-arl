<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->decimal('valor_total_con_ajuste', 15, 2)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->decimal('valor_total_con_ajuste', 15, 2)->default(0)->change();
        });
    }
};
