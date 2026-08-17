<?php

namespace App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\BaseChart;
use App\Models\Almoxarifado\Pedidos;

class BensSolicitados extends BaseChart
{
    protected ?string $heading = 'Bens Solicitados por Ano';

    protected function getData(): array
    {
        $registros = Pedidos::query()
            ->join('ped_itempedido as item', 'item.idPedido', '=', 'ped_pedidos.id')
            ->selectRaw('YEAR(ped_pedidos.date_time) as ano, SUM(item.QuantidadeMaterial) as qtde')
            ->where('ped_pedidos.setor_responsavel', 1239)
            ->groupByRaw('YEAR(ped_pedidos.date_time) WITH ROLLUP')
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
                    'label' => 'Quantidade de materiais solicitados',
                    'data' => $quantidades,
                    'backgroundColor' => $colors,
                    'borderColor' => $border_colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
