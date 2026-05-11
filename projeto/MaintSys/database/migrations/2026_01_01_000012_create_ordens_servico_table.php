<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->enum('tipo', ['preventiva', 'corretiva']);
            $table->enum('status', ['aberta', 'em_andamento', 'concluida', 'cancelada'])->default('aberta');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'critica'])->default('media');
            $table->text('descricao');
            $table->text('solucao')->nullable();
            $table->foreignId('maquina_id')->constrained('maquinas')->onDelete('restrict');
            $table->foreignId('tecnico_id')->constrained('tecnicos')->onDelete('restrict');
            $table->datetime('data_abertura');
            $table->date('data_prevista')->nullable();
            $table->datetime('data_conclusao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};