<?php

namespace App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Almoxarifado;

use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\BaseChart;
use App\Models\Almoxarifado\MovimentacaoEstoque;

class MateriaisConsumo extends BaseChart
{
    protected ?string $heading = 'Materiais de Consumo Fornecidos por Ano';

    protected function getData(): array
    {
        $registros = MovimentacaoEstoque::query()
            ->selectRaw('YEAR(date_time) as ano, SUM(quantidade) as qtde')
            ->whereYear('date_time', '>', 2017)
            ->where('tipo_movimentacao', 2)
            ->groupBy('ano')
            ->get()
            ->filter(fn ($item) => ! is_null($item->ano))
            ->values();

        $labels = $registros->pluck('ano')
            ->map(fn ($value) => (string) $value)
            ->toArray();

        $quantidades = $registros->pluck('qtde')
            ->map(fn ($value) => (int) $value)
            ->toArray();

        $colors = $this->getColors(count($quantidades));
        $border_colors = $this->getBorderColors(count($quantidades));

        return [
            'datasets' => [
                [
                    'label' => 'Quantidade movimentada',
                    'data' => $quantidades,
                    'backgroundColor' => $colors,
                    'borderColor' => $border_colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
