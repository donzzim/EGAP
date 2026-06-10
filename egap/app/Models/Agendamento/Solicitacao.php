<?php

namespace App\Models\Agendamento;

use App\Models\Almoxarifado\SituacaoPedido;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solicitacao extends Model
{
    //protected $connection = 'egap';
    protected $table = 'age_solicitacao';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'data_alteracao',
        'id_user',
        'tipo',
        'id_situacao',
        'id_solicitante',
        'setor_solicitante',
        'data_inicio',
        'hora_inicio',
        'data_termino',
        'hora_termino',
        'justificativa',
        'local_saida',
        'local_destino',
        'motivo_cancelamento',
        'motivo_edicao',
        'finalizar',
        'regiao',
        'unidade_solicitante',
        'anexo',
        'agendamento_pai',
    ];

    protected $casts = [
        'justificativa' => 'array',
    ];

    public function idUserRef() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'id_user', 'id');
    }

    public function idSolicitanteRef() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'id_solicitante', 'id');
    }

    public function idSituacaoRef() : BelongsTo
    {
        return $this->belongsTo(SituacaoPedido::class, 'id_situacao', 'id');
    }

    public function unidadeSolicitanteRef() : BelongsTo
    {
        return $this->belongsTo(Setores::class, 'unidade_solicitante', 'id');
    }

    public function setorSolicitanteRef() : BelongsTo
    {
        return $this->belongsTo(Setores::class, 'setor_solicitante', 'id');
    }

    public function regiaoRef() : BelongsTo
    {
        return $this->belongsTo(Regiao::class, 'regiao', 'id');
    }

    public function getJustificativaListaAttribute(): string
    {
        $data = $this->justificativa;

        if (blank($data) || ! is_array($data)) {
            return '-';
        }

        $linhas = [];

        if (!empty($data['telefone'])) {
            $linhas[] = "Telefone: {$data['telefone']}";
        }

        if (!empty($data['celular'])) {
            $linhas[] = "Celular: {$data['celular']}";
        }

        if (!empty($data['passageiros']) && is_array($data['passageiros'])) {
            $linhas[] = "Passageiros: " . implode(', ', $data['passageiros']);
        }

        if (!empty($data['destino']) && is_array($data['destino'])) {
            $linhas[] = "Destino: " . implode(', ', $data['destino']);
        }

        if (!empty($data['justificativa'])) {
            $linhas[] = "Justificativa: {$data['justificativa']}";
        }

        if (!empty($data['justificativa_motorista'])) {
            $linhas[] = "Motorista: {$data['justificativa_motorista']}";
        }

        return '• ' . implode("\n• ", $linhas);
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->id_user = auth()->id();
        });
    }
}
