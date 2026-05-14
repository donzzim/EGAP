<?php

namespace App\Filament\Widgets\PortalTransparencia\Almoxarifado;

use App\Filament\Widgets\PortalTransparencia\BaseChart;
use App\Models\Almoxarifado\MovimentacaoEstoque;

class MateriaisConsumoAlmoxarifado extends BaseChart
{
    protected static ?string $heading = 'Materiais de Consumo Fornecidos pelo Almoxarifado por Ano';

    protected function getData(): array
    {
        $registros = MovimentacaoEstoque::query()
            ->whereYear('date_time', '>', 2017)
            ->where('tipo_movimentacao', 2)
            ->whereHas('materialRel', function ($query) {
                $query->where('item_estoque', 1);
            })
            ->selectRaw('YEAR(date_time) as ano, SUM(quantidade) as qtde')
            ->groupBy('ano')
            ->get()
            ->filter(fn ($item) => ! is_null($item->ano))
            ->values();

        $labels = $registros->pluck('ano')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        $quantidades = $registros->pluck('qtde')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $colors = $this->getColors(count($quantidades));
        $border_colors = $this->getBorderColors(count($quantidades));

        if ($this->chartType === 'bubble') {
            return [
                'datasets' => [
                    [
                        'label' => 'Saída de estoque',
                        'data' => $registros->map(function ($item) {
                            $qtde = max((int) $item->qtde, 1);

                            return [
                                'x' => (int) $item->ano,
                                'y' => $qtde,
                                'r' => max(5, min(25, (int) round(sqrt($qtde) * 1.5))),
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
                        'label' => 'Saída de estoque',
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
                        'label' => 'Saída de estoque',
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
                    'label' => 'Saída de estoque',
                    'data' => $quantidades,
                    'backgroundColor' => $colors,
                    'borderColor' => $border_colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
