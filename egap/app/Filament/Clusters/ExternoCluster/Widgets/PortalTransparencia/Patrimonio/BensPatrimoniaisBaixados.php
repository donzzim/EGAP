<?php

namespace App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\BaseChart;
use App\Models\Patrimonio\BensMoveis\BemMovel;

class BensPatrimoniaisBaixados extends BaseChart
{
    protected ?string $heading = 'Bens Patrimoniais Baixados por Ano';

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
