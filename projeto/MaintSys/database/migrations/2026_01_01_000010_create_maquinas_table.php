<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maquinas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_serie')->unique();
            $table->string('modelo');
            $table->string('fabricante')->nullable();
            $table->string('localizacao');
            $table->date('data_instalacao')->nullable();
            $table->enum('status', [
                'operacional',
                'em_manutencao',
                'parada_critica',
                'inativa',
            ])->default('operacional');
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maquinas');
    }
};