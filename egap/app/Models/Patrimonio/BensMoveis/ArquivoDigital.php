<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArquivoDigital extends Model
{
    public const SITUACAO_PENDENTE = 0;

    public const SITUACAO_VALIDADO = 1;

    public const SITUACAO_INVALIDADO = 2;

    public const SITUACAO_CANCELADO = 3;

    // protected $connection = 'egap';
    protected $table = 'mat_arquivodigital';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'termo',
        'arquivo_digital',
        'atualizado_em',
        'atualizado_por',
        'data_validacao',
        'validado_por',
        'observacao',
        'situacao',
        'web',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'atualizado_em' => 'datetime',
        'data_validacao' => 'datetime',
        'situacao' => 'integer',
        'web' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function termoRel(): BelongsTo
    {
        return $this->belongsTo(Termo::class, 'termo', 'id');
    }

    public function atualizadoPor(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'validado_por', 'id');
    }

    public static function caminhoArquivoDigitalNoDisco(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return ltrim(str_replace('\\', '/', $path), '/');
    }

    public static function normalizarCaminhoArquivoDigital(?string $path): ?string
    {
        $path = self::caminhoArquivoDigitalNoDisco($path);

        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'images/termos/')) {
            return '/'.$path;
        }

        return '/images/termos/'.ltrim($path, '/');
    }

    public function setArquivoDigitalAttribute(?string $path): void
    {
        $this->attributes['arquivo_digital'] = self::normalizarCaminhoArquivoDigital($path);
    }

    public static function situacaoOptions(): array
    {
        return [
            self::SITUACAO_PENDENTE => 'Pendente',
            self::SITUACAO_VALIDADO => 'Validado',
            self::SITUACAO_INVALIDADO => 'Invalidado',
            self::SITUACAO_CANCELADO => 'Cancelado',
        ];
    }

    public static function situacaoLabel(int|string|null $situacao): string
    {
        if ($situacao === null || $situacao === '') {
            return 'Indefinido';
        }

        return self::situacaoOptions()[(int) $situacao] ?? 'Indefinido';
    }

    public static function situacaoColor(int|string|null $situacao): string
    {
        if ($situacao === null || $situacao === '') {
            return 'gray';
        }

        return match ((int) $situacao) {
            self::SITUACAO_VALIDADO => 'success',
            self::SITUACAO_INVALIDADO, self::SITUACAO_CANCELADO => 'danger',
            self::SITUACAO_PENDENTE => 'warning',
            default => 'gray',
        };
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->atualizado_em = now();
            $model->atualizado_por = auth()->id();
        });
    }
}
