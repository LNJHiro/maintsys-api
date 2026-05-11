<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_manutencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maquina_id')->constrained('maquinas')->onDelete('restrict');
            $table->foreignId('tecnico_id')->constrained('tecnicos')->onDelete('restrict');
            $table->foreignId('ordem_id')->nullable()->constrained('ordens_servico')->onDelete('set null');
            $table->enum('tipo', ['preventiva', 'corretiva']);
            $table->text('descricao');
            $table->text('solucao')->nullable();
            $table->text('pecas_utilizadas')->nullable();
            $table->decimal('tempo_parada_horas', 6, 2)->default(0);
            $table->decimal('custo', 10, 2)->default(0);
            $table->datetime('data_inicio');
            $table->datetime('data_fim')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_manutencoes');
    }
};