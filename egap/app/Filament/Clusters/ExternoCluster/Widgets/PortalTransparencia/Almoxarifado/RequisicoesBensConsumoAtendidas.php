<?php

namespace App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Almoxarifado;

use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\BaseChart;
use App\Models\Almoxarifado\Pedidos;

class RequisicoesBensConsumoAtendidas extends BaseChart
{
    protected ?string $heading = 'Requisições de Bens de Consumo Atendidas por Ano';

    protected function getData(): array
    {
        $registros = Pedidos::query()
            ->selectRaw('YEAR(date_time) as ano, COUNT(*) as qtde')
            ->where('setor_responsavel', 799)
            ->groupBy('ano')
            ->orderBy('ano')
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

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos por ano',
                    'data' => $quantidades,
                    'backgroundColor' => $colors,
                    'borderColor' => $border_colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
