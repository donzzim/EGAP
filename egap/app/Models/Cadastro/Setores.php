<?php

namespace App\Models\Cadastro;

use App\Models\Admin\Lotacao;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setores extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_setores';

    protected $fillable = [
        'date_time',
        'CodigoPai',
        'UnidadeOrganizacional',
        'Setor',
        'SetorDescricao',
        'Usuario',
        'CodigodaUO',
        'comarca',
        'vara',
        'ordem',
        'email',
        'cd_orgao',
        'cns',
        'presidencia',
        'centrocusto',
    ];

    public $timestamps = false;

    protected $casts = [
        'CodigoPai' => 'integer',
        'Usuario' => 'integer',
        'CodigodaUO' => 'integer',
        'ordem' => 'integer',
        'cd_orgao' => 'integer',
        'date_time' => 'datetime',
        'presidencia' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    // Usuário que criou/alterou
    public function atualizado_por(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    // Hierarquia (pai)
    public function pai(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'CodigoPai');
    }

    public function centroCustoRef() : BelongsTo
    {
        return $this->belongsTo(CentroCusto::class, 'centrocusto', 'id');
    }

    // Hierarquia (filhos)
    public function filhos(): HasMany
    {
        return $this->hasMany(Setores::class, 'CodigoPai');
    }

    public function lotacoesComoUnidadeJudiciaria(): HasMany
    {
        return $this->hasMany(Lotacao::class, 'unidade_judiciaria', 'id');
    }

    public function lotacoesComoSetor(): HasMany
    {
        return $this->hasMany(Lotacao::class, 'setor', 'id');
    }

    public function scopeUnidadesRaiz(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->whereNull('CodigoPai')
                ->orWhereColumn('CodigoPai', 'id');
        });
    }

    public function scopeUnidadesOrganizacionais(Builder $query): Builder
    {
        return $query->whereColumn('id', 'CodigodaUO');
    }

    public function scopeFilhosDe(Builder $query, int $unidadeId): Builder
    {
        return $query->where('CodigoPai', $unidadeId);
    }

    public function nomeHierarquico(): string
    {
        return collect([
            $this->Setor,
            $this->SetorDescricao,
            $this->UnidadeOrganizacional,
        ])
            ->filter()
            ->join(' - ');
    }

    protected function presidencia(): Attribute
    {
        return Attribute::make(
            set: fn ($value): int => (int) filter_var($value, FILTER_VALIDATE_BOOLEAN),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Auditoria automática
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->Usuario = auth()->id();
        });
    }
}
