<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\Almoxarifado\NotaFiscal;
use App\Models\Cadastro\ComplementoSetor;
use App\Models\Cadastro\ContaContabil;
use App\Models\Cadastro\DescricaoDetalhada;
use App\Models\Cadastro\DescricaoResumida;
use App\Models\Cadastro\ElementoDespesa;
use App\Models\Cadastro\Fornecedores;
use App\Models\Cadastro\Marcas;
use App\Models\Cadastro\Modelos;
use App\Models\Cadastro\Setores;
use App\Models\Cadastro\SituacaoBem;
use App\Models\Cadastro\UnidadesDeMedida;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BemMovel extends Model
{
    protected $connection = 'egap';
    protected $table = 'mat_patrimonio';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public static $snakeAttributes = false;

    protected $fillable = [
        'TomboSmarapd', 'NumPatrimonio', 'NumerodePatAnterior', 'NumerodeSerie',
        'Produto', 'ContaContabil', 'DescricaoResumidadoBem', 'Descricao',
        'Marca', 'Modelo', 'TipodoBem', 'EstadodeConservacao', 'Voltagem',
        'SituacaoBem', 'ClassificacaoInservivel', 'UnidadeJudiciaria', 'Setor',
        'ComplementoSetor', 'AndarSetor', 'Unidade', 'DataCadastro', 'Valor',
        'DatadeIncorporacao', 'Lote', 'DatadeVencimento', 'NumeracaoInicial',
        'NumeracaoFinal', 'MesdeReferencia', 'Fornecedor', 'NotaFiscal',
        'FormaAquisicao', 'SiglaUnidadeGestora', 'Categoria', 'Combustivel',
        'Placa', 'Chassi', 'Renavam', 'VidaUtil', 'DatadaReavaliacao',
        'ValordaReavaliacao', 'ValordeMercado', 'AcertoContabil', 'UtilizacaodoBem',
        'VidaUtilEstimada', 'EC', 'PUB1', 'PUB2', 'PUV', 'FR', 'ValorReavaliado',
        'VidaUtilSIAFi', 'UtilizacaodoBemMeses', 'DepreciacaoMensal',
        'DepreciacaoAcumulada', 'TestedeImpairment', 'DataDisponibilizacao',
        'Observacao', 'date_time', 'Usuario', 'NumTomboSmarapd', 'ProcessoBaixa',
        'DataBaixa', 'ValorAquisicao', 'AnoFabricacao', 'AnoModelo', 'ValorResidual',
        'Garantia', 'numero_processo', 'nota_empenho', 'nota_liquidacao',
        'data_liquidacao', 'VidaUtilReavaliacao', 'id_descricaodetalhada',
        'grupo', 'acuracia', 'unidade_gestora', 'sit_inventario', 'id_inventario',
        'situacao_contabil', 'data_situacao_contabil',
    ];

    protected $casts = [
        'DatadeIncorporacao' => 'datetime',
        'ValorAquisicao' => 'decimal:2'
    ];

    public function usuarioRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    public function descricaoDetalhadaRef(): BelongsTo
    {
        return $this->belongsTo(DescricaoDetalhada::class, 'id_descricaodetalhada', 'id');
    }

    public function setorRef(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'Setor', 'id');
    }

    public function complementoSetorRef(): BelongsTo
    {
        return $this->belongsTo(ComplementoSetor::class, 'ComplementoSetor', 'id');
    }

    public function unidadeMedidaRef(): BelongsTo
    {
        return $this->belongsTo(UnidadesDeMedida::class, 'Unidade', 'id');
    }

    public function produtoRef(): BelongsTo
    {
        return $this->belongsTo(ElementoDespesa::class, 'Produto', 'id');
    }

    public function contaContabilRef(): BelongsTo
    {
        return $this->belongsTo(ContaContabil::class, 'ContaContabil', 'id');
    }

    public function descricaoResumidaBemRef(): BelongsTo
    {
        return $this->belongsTo(DescricaoResumida::class, 'DescricaoResumidadoBem', 'id');
    }

    public function marcaRef(): BelongsTo
    {
        return $this->belongsTo(Marcas::class, 'Marca', 'id');
    }

    public function modeloRef(): BelongsTo
    {
        return $this->belongsTo(Modelos::class, 'Modelo', 'id');
    }

    public function notaFiscalRef(): BelongsTo
    {
        return $this->belongsTo(NotaFiscal::class, 'NotaFiscal', 'id');
    }

    public function fornecedorRef(): BelongsTo
    {
        return $this->belongsTo(Fornecedores::class, 'Fornecedor', 'id');
    }

    public function elementoDespesaRef(): BelongsTo
    {
        return $this->belongsTo(ElementoDespesa::class, 'Produto', 'id');
    }

    public function situacaoBemRef(): BelongsTo
    {
        return $this->belongsTo(SituacaoBemMovel::class, 'SituacaoBem', 'id');
    }

    public function unidadeJudiciariaRef(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'UnidadeJudiciaria', 'id');
    }

    public function descricaoResumidaRel(): BelongsTo { return $this->descricaoResumidaBemRef(); }
    public function marcaRel(): BelongsTo { return $this->marcaRef(); }
    public function unidadeJudiciariaRel(): BelongsTo { return $this->unidadeJudiciariaRef(); }
    public function fornecedorRel(): BelongsTo { return $this->fornecedorRef(); }
    public function contaContabilRel(): BelongsTo { return $this->contaContabilRef(); }
    public function elementodespesaRel(): BelongsTo { return $this->elementoDespesaRef(); }
    public function atualizado_por(): BelongsTo { return $this->usuarioRef(); }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->Usuario = auth()->id();
            $model->date_time = now();
        });
        static::updating(function ($model) {
            $model->Usuario = auth()->id();
            $model->date_time = now();
        });
    }
}
