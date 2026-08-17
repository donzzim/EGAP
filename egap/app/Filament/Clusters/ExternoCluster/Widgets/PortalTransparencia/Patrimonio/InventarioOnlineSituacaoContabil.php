<?php

namespace App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\BaseChart;
use App\Models\Patrimonio\BensMoveis\BemMovel;

class InventarioOnlineSituacaoContabil extends BaseChart
{
    protected ?string $heading = 'Acompanhamento do Invetário Online Anual - Situação Contábil';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $dados = BemMovel::query()
            ->selectRaw("
            SUM(
                CASE
                    WHEN situacao_contabil LIKE 'LOCALIZADO%'
                         AND SituacaoBem IN (1, 7)
                    THEN 1 ELSE 0
                END
            ) as localizados
        ")
            ->selectRaw("
            SUM(
                CASE
                    WHEN situacao_contabil LIKE 'N%'
                         AND SituacaoBem IN (1, 7)
                    THEN 1 ELSE 0
                END
            ) as nao_localizados
        ")
            ->first();

        $labels = [
            'Localizados',
            'Não localizados',
        ];

        $quantidades = [
            (int) $dados->localizados,
            (int) $dados->nao_localizados,
        ];

        $colors = $this->getColors(count($quantidades));

        return [
            'datasets' => [
                [
                    'label' => 'Quantidade',
                    'data' => $quantidades,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
