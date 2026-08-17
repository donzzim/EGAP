<?php

namespace App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\BaseChart;
use App\Models\Patrimonio\BensMoveis\BemMovel;

class BensPermanentesMoveisPatrimonio extends BaseChart
{
    protected ?string $heading = 'Bens Permanentes Móveis Adquiridos por Ano do Patrimônio';

    protected function getData(): array
    {
        $registros = BemMovel::query()
            ->selectRaw('YEAR(DatadeIncorporacao) as ano, COUNT(*) as qtde')
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
