<?php

namespace App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\BaseChart;
use App\Models\Patrimonio\BensMoveis\BemMovel;

class SituacaoBensPatrimoniaisEgap extends BaseChart
{
    protected ?string $heading = 'Situação Atual dos Bens Patrimoniais no Sistema E-Gap';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $registros = BemMovel::query()
            ->join('mat_situacao as sit', 'mat_patrimonio.SituacaoBem', '=', 'sit.id')
            ->selectRaw('sit.descricao as situacao, COUNT(*) as qtde, sit.id')
            ->groupBy('sit.id', 'sit.descricao')
            ->orderBy('sit.descricao')
            ->get();

        $labels = $registros->pluck('situacao')->toArray();
        $quantidades = $registros->pluck('qtde')->map(fn ($value) => (int) $value)->toArray();
        $colors = $this->getColors(count($quantidades));

        return [
            'datasets' => [
                [
                    'label' => 'Quantidade de bens por situação',
                    'data' => $quantidades,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
