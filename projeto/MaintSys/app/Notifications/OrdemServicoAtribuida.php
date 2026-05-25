<?php

namespace App\Notifications;

use App\Models\OrdemServico;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrdemServicoAtribuida extends Notification
{
    use Queueable;

    public function __construct(private OrdemServico $ordem)
    {
        $this->ordem->loadMissing(['maquina']);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $maquina = $this->ordem->maquina;

        return [
            'ordem_id' => $this->ordem->id,
            'numero' => $this->ordem->numero,
            'titulo' => 'Nova O.S. atribuida',
            'mensagem' => "Voce recebeu a O.S. {$this->ordem->numero}.",
            'maquina' => $maquina?->modelo,
            'numero_serie' => $maquina?->numero_serie,
            'prioridade' => $this->ordem->prioridade,
            'status' => $this->ordem->status,
            'data_prevista' => $this->ordem->data_prevista?->format('Y-m-d'),
            'url' => route('ordens.show', $this->ordem->id, false),
            'atribuida_em' => now()->toDateTimeString(),
        ];
    }
}
