<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // UNSPSC
        $this->createIfMissing('segmentos', function (Blueprint $t) {
            $t->id();
            $t->string('detsegmento');
            $t->string('slug')->nullable();
            $t->timestamps();
        });
        $this->createIfMissing('familias', function (Blueprint $t) {
            $t->id();
            $t->string('detfamilia');
            $t->string('slug')->nullable();
            $t->unsignedBigInteger('segmento_id')->nullable()->index();
            $t->timestamps();
        });
        $this->createIfMissing('clases', function (Blueprint $t) {
            $t->id();
            $t->string('detclase');
            $t->string('slug')->nullable();
            $t->unsignedBigInteger('familia_id')->nullable()->index();
            $t->timestamps();
        });
        $this->createIfMissing('productos', function (Blueprint $t) {
            $t->id();
            $t->string('detproducto');
            $t->string('slug')->nullable();
            $t->unsignedBigInteger('clase_id')->nullable()->index();
            $t->timestamps();
        });

        // Lookups
        $this->createIfMissing('estadovigencias', function (Blueprint $t) {
            $t->id(); $t->integer('codigo')->nullable(); $t->string('detestadovigencia'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('meses', function (Blueprint $t) {
            $t->id(); $t->string('nommes'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('modalidades', function (Blueprint $t) {
            $t->id(); $t->string('codigo')->nullable(); $t->string('detmodalidad'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('intervalos', function (Blueprint $t) {
            $t->id(); $t->integer('codigo')->nullable(); $t->string('intervalo'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('vigenfuturas', function (Blueprint $t) {
            $t->id(); $t->integer('codigo')->nullable(); $t->string('detvigencia'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('tipozonas', function (Blueprint $t) {
            $t->id(); $t->string('tipozona'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('tipoprocesos', function (Blueprint $t) {
            $t->id(); $t->string('dettipoproceso'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('tipoadquisiciones', function (Blueprint $t) {
            $t->id(); $t->string('dettipoadquisicion'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('requiproyectos', function (Blueprint $t) {
            $t->id(); $t->string('detproyeto'); $t->string('slug')->nullable(); $t->timestamps(); // typo legacy intencional
        });
        $this->createIfMissing('fuentes', function (Blueprint $t) {
            $t->id(); $t->integer('codigo')->nullable(); $t->string('detfuente'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('tipoprioridades', function (Blueprint $t) {
            $t->id(); $t->string('detprioridad'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('requipoais', function (Blueprint $t) {
            $t->id(); $t->string('detpoai'); $t->string('slug')->nullable(); $t->timestamps();
        });
    }

    private function createIfMissing(string $table, \Closure $cb): void
    {
        if (! Schema::hasTable($table)) {
            Schema::create($table, $cb);
        }
    }

    public function down(): void
    {
        foreach (['requipoais','tipoprioridades','fuentes','requiproyectos','tipoadquisiciones','tipoprocesos','tipozonas','vigenfuturas','intervalos','modalidades','meses','estadovigencias','productos','clases','familias','segmentos'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
