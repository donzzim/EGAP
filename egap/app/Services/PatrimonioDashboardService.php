<?php

namespace App\Services;

use App\Models\Patrimonio\BensImoveis\BemImovel;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class PatrimonioDashboardService
{
    public function getSummary(?array $filters = null): array
    {
        $normalizedFilters = $this->normalizeFilters($filters);

        $movableQuery = $this->movableQuery($normalizedFilters);
        $immovableQuery = $this->immovableQuery($normalizedFilters);

        return [
            'moveis_total' => (clone $movableQuery)->count(),
            'imoveis_total' => (clone $immovableQuery)->count(),
            'moveis_ativos_total' => (clone $movableQuery)
                ->whereIn('mat_patrimonio.SituacaoBem', [1, 7, 8])
                ->count(),
            'moveis_valor_aquisicao' => $this->sum(
                $movableQuery,
                'COALESCE(mat_patrimonio.ValorAquisicao, 0)'
            ),
        ];
    }

    public function getMoveisPorSituacao(?array $filters = null): array
    {
        $rows = $this->movableQuery($this->normalizeFilters($filters))
            ->leftJoin('mat_situacao as situacoes', 'mat_patrimonio.SituacaoBem', '=', 'situacoes.id')
            ->selectRaw("COALESCE(situacoes.descricao, 'Sem situacao') as label")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('situacoes.descricao')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
        ];
    }

    public function getMoveisPorAno(?array $filters = null): array
    {
        $rows = $this->movableQuery($this->normalizeFilters($filters))
            ->whereNotNull('mat_patrimonio.DatadeIncorporacao')
            ->selectRaw('YEAR(mat_patrimonio.DatadeIncorporacao) as ano')
            ->selectRaw('COUNT(*) as total')
            ->groupByRaw('YEAR(mat_patrimonio.DatadeIncorporacao)')
            ->orderBy('ano')
            ->get()
            ->filter(fn ($row) => filled($row->ano));

        return [
            'labels' => $rows->pluck('ano')->map(fn ($value) => (string) $value)->all(),
            'values' => $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
        ];
    }

    public function getImoveisPorContaContabil(?array $filters = null, int $limit = 10): array
    {
        $rows = $this->immovableQuery($this->normalizeFilters($filters))
            ->leftJoin('mat_planocontas as contas', 'imo_imovel.id_planocontas', '=', 'contas.id')
            ->selectRaw("COALESCE(contas.titulo, 'Sem conta contabil') as label")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('contas.titulo')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck('label')->map(fn ($value) => mb_strimwidth((string) $value, 0, 40, '...'))->all(),
            'values' => $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
        ];
    }

    public function topMovableMaterialsQuery(?array $filters = null, int $limit = 10): Builder
    {
        return $this->movableQuery($this->normalizeFilters($filters))
            ->whereIn('mat_patrimonio.SituacaoBem', [1, 7, 8])
            ->leftJoin('mat_descricaoresumida as descricoes_resumidas', 'mat_patrimonio.DescricaoResumidadoBem', '=', 'descricoes_resumidas.id')
            ->leftJoin('mat_descricaodetalhada as descricoes_detalhadas', 'mat_patrimonio.id_descricaodetalhada', '=', 'descricoes_detalhadas.id')
            ->selectRaw('MIN(mat_patrimonio.id) as id')
            ->selectRaw("COALESCE(descricoes_resumidas.Descricao, 'Sem descricao') as descricao_resumida")
            ->selectRaw("COALESCE(descricoes_detalhadas.descricao_detalhada, 'Sem descricao detalhada') as descricao_detalhada")
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(COALESCE(mat_patrimonio.ValorAquisicao, 0)), 0) as valor_aquisicao')
            ->selectRaw('COALESCE(SUM(COALESCE(mat_patrimonio.ValordaReavaliacao, mat_patrimonio.ValorAquisicao, 0)), 0) as valor_atual')
            ->groupBy('descricoes_resumidas.Descricao', 'descricoes_detalhadas.descricao_detalhada')
            ->orderByDesc('valor_atual')
            ->limit($limit);
    }

    public function getPeriodoDescricao(?array $filters = null): string
    {
        $normalizedFilters = $this->normalizeFilters($filters);
        $startDate = $normalizedFilters['start_date'];
        $endDate = $normalizedFilters['end_date'];

        if (! $startDate && ! $endDate) {
            return 'Base consolidada';
        }

        if ($startDate && $endDate) {
            return sprintf(
                'Incorporados entre %s e %s',
                $startDate->format('d/m/Y'),
                $endDate->format('d/m/Y')
            );
        }

        if ($startDate) {
            return 'Incorporados a partir de ' . $startDate->format('d/m/Y');
        }

        return 'Incorporados ate ' . $endDate->format('d/m/Y');
    }

    protected function movableQuery(array $filters): Builder
    {
        return BemMovel::query()
            ->when(
                $filters['start_date'],
                fn (Builder $query, CarbonImmutable $date) => $query->whereDate('mat_patrimonio.DatadeIncorporacao', '>=', $date->toDateString())
            )
            ->when(
                $filters['end_date'],
                fn (Builder $query, CarbonImmutable $date) => $query->whereDate('mat_patrimonio.DatadeIncorporacao', '<=', $date->toDateString())
            );
    }

    protected function immovableQuery(array $filters): Builder
    {
        return BemImovel::query()
            ->when(
                $filters['start_date'],
                fn (Builder $query, CarbonImmutable $date) => $query->whereDate('imo_imovel.data_incorporacao', '>=', $date->toDateString())
            )
            ->when(
                $filters['end_date'],
                fn (Builder $query, CarbonImmutable $date) => $query->whereDate('imo_imovel.data_incorporacao', '<=', $date->toDateString())
            );
    }

    protected function normalizeFilters(?array $filters): array
    {
        $startDate = $this->parseDate($filters['start_date'] ?? null);
        $endDate = $this->parseDate($filters['end_date'] ?? null);

        if ($startDate && $endDate && $startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    protected function parseDate(mixed $value): ?CarbonImmutable
    {
        if (blank($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function sum(Builder $query, string $expression): float
    {
        return (float) (clone $query)
            ->selectRaw("COALESCE(SUM({$expression}), 0) as total")
            ->value('total');
    }
}
