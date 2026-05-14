<?php

namespace App\Filament\Egap\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Egap\Widgets\PortalTransparencia\BaseChart;
use App\Models\Egap\Patrimonio\BensMoveis\BemMovel;

class BensPermanentesMoveisPatrimonio extends BaseChart
{
    protected static ?string $heading = 'Bens Permanentes Móveis Adquiridos por Ano do Patrimônio';

    protected function getData(): array
    {
        $registros = BemMovel::query()
            ->selectRaw('YEAR(DatadeIncorporacao) as ano, COUNT(*) as qtde, SUM(ValorAquisicao) as valor')
            ->whereYear('DatadeIncorporacao', '>', 2006)
            ->where('UnidadeJudiciaria', 766)
            ->groupByRaw('YEAR(DatadeIncorporacao) WITH ROLLUP')
            ->get()
            ->filter(fn ($item) => ! is_null($item->ano))
            ->values();

        $labels = $registros->pluck('ano')->map(fn ($value) => (string) $value)->toArray();
        $quantidades = $registros->pluck('qtde')->map(fn ($value) => (int) $value)->toArray();
        $colors = $this->getColors(count($quantidades));
        $border_colors = $this->getBorderColors(count($quantidades));

        if ($this->chartType === 'bubble') {
            return [
                'datasets' => [
                    [
                        'label' => 'Quantidade',
                        'data' => $registros->map(function ($item) {
                            $qtde = max((int) $item->qtde, 1);
                            $valor = (float) $item->valor;

                            return [
                                'x' => (int) $item->ano,
                                'y' => $qtde,
                                'r' => max(5, min(25, (int) round($valor / 10000))),
                            ];
                        })->toArray(),
                        'backgroundColor' => $this->getColors($registros->count()),
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
