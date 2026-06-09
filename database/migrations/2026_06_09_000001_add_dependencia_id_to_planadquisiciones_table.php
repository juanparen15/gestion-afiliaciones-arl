<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('planadquisiciones', 'dependencia_id')) {
            Schema::table('planadquisiciones', function (Blueprint $t) {
                $t->unsignedBigInteger('dependencia_id')->nullable()->after('area_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('planadquisiciones', 'dependencia_id')) {
            Schema::table('planadquisiciones', fn (Blueprint $t) => $t->dropColumn('dependencia_id'));
        }
    }
};
