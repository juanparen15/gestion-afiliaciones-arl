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
        Schema::create('archivos_afiliaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('afiliacion_id')->constrained('afiliaciones')->onDelete('cascade');
            $table->string('nombre_original');
            $table->string('nombre_archivo');
            $table->string('ruta');
            $table->string('tipo_archivo')->nullable(); // pdf, jpg, png, etc.
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamano')->nullable(); // en bytes
            $table->enum('tipo_documento', ['soporte_arl', 'contrato', 'otro'])->default('soporte_arl');
            $table->text('descripcion')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índice
            $table->index('afiliacion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos_afiliaciones');
    }
};
