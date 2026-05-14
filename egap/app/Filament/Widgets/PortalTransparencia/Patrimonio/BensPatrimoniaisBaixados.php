<?php

namespace App\Filament\Egap\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Egap\Widgets\PortalTransparencia\BaseChart;
use App\Models\Egap\Patrimonio\BensMoveis\BemMovel;

class BensPatrimoniaisBaixados extends BaseChart
{
    protected static ?string $heading = 'Bens Patrimoniais Baixados por Ano';

    protected function getData(): array
    {
        $registros = BemMovel::query()
            ->selectRaw('YEAR(DataBaixa) as ano, COUNT(*) as qtde')
            ->whereIn('SituacaoBem', [2, 3, 4, 5, 6, 11])
            ->groupBy('ano')
            ->orderBy('ano')
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
                        'label' => 'Baixas por ano',
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
                        'label' => 'Baixas por ano',
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
                        'label' => 'Baixas por ano',
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
                    'label' => 'Baixas por ano',
                    'data' => $quantidades,
                    'backgroundColor' => $colors,
                    'borderColor' => $border_colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
