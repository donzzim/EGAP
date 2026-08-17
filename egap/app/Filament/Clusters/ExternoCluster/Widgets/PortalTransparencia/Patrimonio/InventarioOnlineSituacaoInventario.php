<?php

namespace App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\BaseChart;
use App\Models\Patrimonio\BensMoveis\BemMovel;

class InventarioOnlineSituacaoInventario extends BaseChart
{
    protected ?string $heading = 'Acompanhamento do Invetário Online Anual - Situação do Inventário';

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
                        WHEN (
                            (
                                sit_inventario LIKE 'LOCALIZADO%'
                                OR sit_inventario IS NULL
                                OR sit_inventario = ''
                            )
                            AND SituacaoBem IN (1, 7)
                        )
                        THEN 1
                        ELSE 0
                    END
                ) as localizados
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN sit_inventario LIKE 'N%'
                             AND SituacaoBem IN (1, 7)
                        THEN 1
                        ELSE 0
                    END
                ) as nao_localizados
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN sit_inventario LIKE 'A INVENTARIAR%'
                             AND SituacaoBem IN (1, 7)
                        THEN 1
                        ELSE 0
                    END
                ) as a_inventariar
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN sit_inventario LIKE 'EM TRANSF%'
                             AND SituacaoBem IN (1, 7)
                        THEN 1
                        ELSE 0
                    END
                ) as em_transferencia
            ")
            ->first();

        $labels = [
            'Localizados',
            'Não localizados',
            'A inventariar',
            'Em transferência',
        ];

        $quantidades = [
            (int) $dados->localizados,
            (int) $dados->nao_localizados,
            (int) $dados->a_inventariar,
            (int) $dados->em_transferencia,
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
