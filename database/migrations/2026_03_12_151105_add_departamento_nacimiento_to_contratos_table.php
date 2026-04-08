<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->string('departamento_nacimiento', 80)->nullable()->after('lugar_nacimiento');
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn('departamento_nacimiento');
        });
    }
};
