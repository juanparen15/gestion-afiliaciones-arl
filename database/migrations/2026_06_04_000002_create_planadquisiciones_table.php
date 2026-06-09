<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('planadquisiciones')) {
            Schema::create('planadquisiciones', function (Blueprint $t) {
                $t->id();
                $t->integer('id_vigencia')->nullable();
                $t->string('descripcioncont', 500);
                $t->string('valorestimadocont');   // string: datos legacy con separador de miles
                $t->string('valorestimadovig');
                $t->string('duracont');
                $t->string('codbpim')->nullable();
                $t->unsignedBigInteger('intervalo_id')->nullable();
                $t->unsignedBigInteger('area_id')->nullable();          // → areas (ARL)
                $t->unsignedBigInteger('vigenfutura_id')->nullable();
                $t->unsignedBigInteger('tipozona_id')->nullable();
                $t->unsignedBigInteger('estadovigencia_id')->nullable();
                $t->unsignedBigInteger('modalidade_id')->nullable();
                $t->unsignedBigInteger('tipoproceso_id')->nullable();
                $t->unsignedBigInteger('tipoadquisicione_id')->nullable();
                $t->unsignedBigInteger('requiproyecto_id')->nullable();
                $t->unsignedBigInteger('fuente_id')->nullable();
                $t->unsignedBigInteger('tipoprioridade_id')->nullable();
                $t->unsignedBigInteger('mese_id')->nullable();
                $t->unsignedBigInteger('requipoai_id')->nullable();
                $t->unsignedBigInteger('user_id')->nullable();          // → users (ARL)
                $t->string('slug', 1000)->nullable();
                $t->timestamps();

                $t->index(['area_id', 'id_vigencia']);
            });
        }

        if (! Schema::hasTable('planadquisicione_producto')) {
            Schema::create('planadquisicione_producto', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('planadquisicione_id')->index();
                $t->unsignedBigInteger('producto_id')->nullable()->index();
                $t->unsignedBigInteger('clase_id')->nullable()->index();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('planadquisicione_producto');
        Schema::dropIfExists('planadquisiciones');
    }
};
