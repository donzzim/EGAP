<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Inventario extends Model
{
    public const SITUACAO_EM_ANDAMENTO = '0';
    public const SITUACAO_A_INVENTARIAR = '1';
    public const SITUACAO_FINALIZADO = '2';

    //protected $connection = 'egap';

    protected $table = 'mat_inventario';

    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        'date_time', 'num_inventario', 'ano_inventario', 'inicio_inventario',
        'termino_inventario', 'atualizado_em', 'atualizado_por', 'situacao', 'dias'
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'inicio_inventario' => 'datetime',
        'termino_inventario' => 'datetime',
        'atualizado_em' => 'datetime',
        'dias' => 'integer',
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(ItemInventario::class, 'id_inventario', 'id');
    }

    public function unidadesInventariadas(): HasMany
    {
        return $this->hasMany(InventarioUnidade::class, 'id_inventario', 'id');
    }

    public function atividades(): HasMany
    {
        return $this->hasMany(AtividadeInventario::class, 'id_inventario', 'id');
    }

    public function comissoes(): HasMany
    {
        return $this->hasMany(InventarioComissao::class, 'id_inventario', 'id');
    }

    public function equipes(): HasMany
    {
        return $this->hasMany(InventarioEquipe::class, 'id_inventario', 'id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->where('situacao', self::SITUACAO_EM_ANDAMENTO)
                ->orWhere('situacao', 'Em andamento')
                ->orWhere('situacao', self::SITUACAO_A_INVENTARIAR)
                ->orWhere('situacao', 'A inventariar');
        });
    }

    public function scopeFinalizados(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->where('situacao', self::SITUACAO_FINALIZADO)
                ->orWhere('situacao', 'Finalizado');
        });
    }

    public function estaAberto(): bool
    {
        return in_array($this->situacaoNormalizada(), [
            self::SITUACAO_EM_ANDAMENTO,
            'em andamento',
        ], true);
    }

    public function estaAInventariar(): bool
    {
        return in_array($this->situacaoNormalizada(), [
            self::SITUACAO_A_INVENTARIAR,
            'a inventariar',
            '',
        ], true);
    }

    public function estaAtivo(): bool
    {
        return $this->estaAberto() || $this->estaAInventariar();
    }

    public function estaFinalizado(): bool
    {
        return in_array($this->situacaoNormalizada(), [
            self::SITUACAO_FINALIZADO,
            'finalizado',
        ], true);
    }

    public function podeAbrir(): bool
    {
        return $this->estaAInventariar();
    }

    public function podeFechar(): bool
    {
        return $this->estaAtivo();
    }

    private function situacaoNormalizada(): string
    {
        return strtolower(trim((string) $this->situacao));
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->atualizado_por = Auth::id();
            $model->date_time = now();
            $model->atualizado_em = now();
        });
    }
}
