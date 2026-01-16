<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->string('cargo')->nullable()->after('nombre_contratista');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            $table->dropColumn('cargo');
        });
    }
};
