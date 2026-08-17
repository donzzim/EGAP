<?php

namespace App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio;

use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\BaseChart;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use Filament\Support\RawJs;

class ExecucaoOrcamentaria extends BaseChart
{
    protected ?string $heading = 'Execução Orçamentária';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $registros = BemMovel::query()
            ->selectRaw('YEAR(DatadeIncorporacao) as ano, SUM(ValorAquisicao) as valor')
            ->whereYear('DatadeIncorporacao', '>', 2006)
            ->groupByRaw('YEAR(DatadeIncorporacao) WITH ROLLUP')
            ->get()
            ->filter(fn ($item) => ! is_null($item->ano))
            ->values();

        $labels = $registros->pluck('ano')->map(fn ($value) => (string) $value)->toArray();
        $valores = $registros->pluck('valor')->map(fn ($value) => (float) $value)->toArray();
        $colors = $this->getColors(count($valores));

        return [
            'datasets' => [
                [
                    'label' => 'Valor incorporado por ano',
                    'data' => $valores,
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

    // Formatacao para valor monetario R$
    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
    {
        scales: {
            y: {
                ticks: {
                    callback: function(value) {

                        function formatMilhao(valor) {
                            if (valor >= 1000000) {
                                return 'R$ ' + (valor / 1000000).toFixed(1).replace('.', ',') + ' mi';
                            }

                            if (valor >= 1000) {
                                return 'R$ ' + (valor / 1000).toFixed(1).replace('.', ',') + ' mil';
                            }

                            return new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL'
                            }).format(valor);
                        }

                        return formatMilhao(value);
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }

                        const parsed = context.parsed;
                        const value = typeof parsed === 'number'
                            ? parsed
                            : (parsed?.y ?? 0);

                        return label + new Intl.NumberFormat('pt-BR', {
                            style: 'currency',
                            currency: 'BRL'
                        }).format(value);
                    }
                }
            }
        }
    }
    JS);
    }
}
