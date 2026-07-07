<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acta_configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('label_alcalde')->default('Vo Bo. Alcalde Municipal');
            $table->string('firma_alcalde_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acta_configuraciones');
    }
};
