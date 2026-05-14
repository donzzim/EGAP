<?php

namespace App\Filament\Egap\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Egap\Widgets\PortalTransparencia\BaseChart;
use App\Models\Egap\Patrimonio\BensMoveis\BemMovel;

class InventarioOnlineSituacaoInventario extends BaseChart
{
    protected static ?string $heading = 'Acompanhamento do Invetário Online Anual - Situação do Inventário';

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
        $border_colors = $this->getBorderColors(count($quantidades));

        if ($this->chartType === 'bubble') {
            return [
                'datasets' => [
                    [
                        'label' => 'Quantidade',
                        'data' => collect($quantidades)->map(function ($valor, $index) {
                            $qtde = max((int) $valor, 1);

                            return [
                                'x' => $index + 1,
                                'y' => $qtde,
                                'r' => max(5, min(25, (int) round(sqrt($qtde) * 1.5))),
                            ];
                        })->toArray(),
                        'backgroundColor' => $colors,
                    ],
                ],
                'labels' => $labels,
            ];
        }

        if (in_array($this->chartType, ['pie', 'doughnut', 'polarArea'], true)) {
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

        if ($this->chartType === 'line') {
            return [
                'datasets' => [
                    [
                        'label' => 'Quantidade',
                        'data' => $quantidades,
                        'backgroundColor' => 'rgba(59, 130, 246, 0.12)',
                        'borderColor' => 'rgba(59, 130, 246, 1)',
                        'pointBackgroundColor' => $colors,
                        'tension' => 0.3,
                        'fill' => true,
                    ],
                ],
                'labels' => $labels,
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Quantidade',
                    'data' => $quantidades,
                    'backgroundColor' => $colors,
                    'borderColor' => $border_colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
