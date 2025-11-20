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
            // Añadir columnas solo si no existen
            if (!Schema::hasColumn('afiliaciones', 'pdf_arl')) {
                $table->string('pdf_arl')->nullable();
            }
            if (!Schema::hasColumn('afiliaciones', 'contrato_pdf_o_word')) {
                $table->string('contrato_pdf_o_word')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afiliaciones', function (Blueprint $table) {
            if (Schema::hasColumn('afiliaciones', 'pdf_arl')) {
                $table->dropColumn('pdf_arl');
            }
            if (Schema::hasColumn('afiliaciones', 'contrato_pdf_o_word')) {
                $table->dropColumn('contrato_pdf_o_word');
            }
        });
    }
};
