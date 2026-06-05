<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contratos', 'planadquisicione_id')) {
            Schema::table('contratos', function (Blueprint $t) {
                $t->unsignedBigInteger('planadquisicione_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contratos', 'planadquisicione_id')) {
            Schema::table('contratos', fn (Blueprint $t) => $t->dropColumn('planadquisicione_id'));
        }
    }
};
