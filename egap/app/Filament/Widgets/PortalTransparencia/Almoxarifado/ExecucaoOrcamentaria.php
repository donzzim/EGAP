<?php

namespace App\Filament\Widgets\PortalTransparencia\Almoxarifado;

use App\Filament\Widgets\PortalTransparencia\BaseChart;
use App\Models\Almoxarifado\MovimentacaoEstoque;
use Filament\Support\RawJs;

class ExecucaoOrcamentaria extends BaseChart
{
    protected static ?string $heading = 'Execução Orçamentária';

    protected function getData(): array
    {
        $registros = MovimentacaoEstoque::query()
            ->join('mat_descricaodetalhada as det', 'det.id', '=', 'alm_estoque.material')
            ->join('mat_unidades as un', 'det.unidade_medida', '=', 'un.id')
            ->join('mat_descricaoresumida as res', 'det.descricao_resumida', '=', 'res.id')
            ->join('mat_produtos as prod', 'prod.id', '=', 'res.id_produto')
            ->selectRaw('YEAR(alm_estoque.date_time) as ano, SUM(alm_estoque.quantidade * alm_estoque.preco_unitario) as valor')
            ->where('alm_estoque.tipo_movimentacao', 2)
            ->whereRaw('FLOOR(prod.CodigodaClasse / 1000000) = ?', [33])
            ->groupByRaw('YEAR(alm_estoque.date_time)')
            ->orderBy('ano')
            ->get()
            ->filter(fn ($item) => ! is_null($item->ano))
            ->values();

        $labels = $registros->pluck('ano')
            ->map(fn ($value) => (string) $value)
            ->toArray();

        $valores = $registros->pluck('valor')
            ->map(fn ($value) => (float) $value)
            ->toArray();

        $colors = $this->getColors(count($valores));
        $border_colors = $this->getBorderColors(count($valores));

        if ($this->chartType === 'bubble') {
            return [
                'datasets' => [
                    [
                        'label' => 'Valor movimentado',
                        'data' => $registros->map(function ($item) {
                            $valor = max((float) $item->valor, 1);

                            return [
                                'x' => (int) $item->ano,
                                'y' => $valor,
                                'r' => max(5, min(25, (int) round($valor / 100000))),
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
                        'label' => 'Valor movimentado',
                        'data' => $valores,
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
                        'label' => 'Valor movimentado',
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

        return [
            'datasets' => [
                [
                    'label' => 'Valor movimentado',
                    'data' => $valores,
                    'backgroundColor' => $colors,
                    'borderColor' => $border_colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

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
