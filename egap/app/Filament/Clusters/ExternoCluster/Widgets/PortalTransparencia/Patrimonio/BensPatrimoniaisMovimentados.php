<?php

namespace App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\BaseChart;
use App\Models\Patrimonio\BensMoveis\TransferenciaBemMovel;

class BensPatrimoniaisMovimentados extends BaseChart
{
    protected ?string $heading = 'Bens Patrimoniais Movimentados por Ano';

    protected function getData(): array
    {
        $registros = TransferenciaBemMovel::query()
            ->selectRaw('YEAR(date_time) as ano, COUNT(*) as qtde')
            ->whereYear('date_time', '>', 2016)
            ->groupByRaw('YEAR(date_time) WITH ROLLUP')
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
                    'label' => 'Quantidade de transferências',
                    'data' => $quantidades,
                    'backgroundColor' => $colors,
                    'borderColor' => $border_colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
