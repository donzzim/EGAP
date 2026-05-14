<?php

namespace App\Filament\Widgets\EgapDashboard;

use App\Services\PatrimonioDashboardService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PatrimonioOverviewStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $service = app(PatrimonioDashboardService::class);
        $summary = $service->getSummary($this->filters);
        $movableByYear = $service->getMoveisPorAno($this->filters);
        $trend = $movableByYear['values'] !== [] ? $movableByYear['values'] : [0];
        $periodo = $service->getPeriodoDescricao($this->filters);

        return [
            Stat::make('Bens móveis', Number::format($summary['moveis_total'], locale: 'pt_BR'))
                ->description($periodo)
                ->descriptionIcon('heroicon-o-cube')
                ->chart($trend)
                ->color('info')
                ->extraAttributes(['class' => 'bg-gradient-to-r from-sky-100 to-white dark:from-sky-950 dark:to-slate-900']),

            Stat::make('Bens imóveis', Number::format($summary['imoveis_total'], locale: 'pt_BR'))
                ->description($periodo)
                ->descriptionIcon('heroicon-o-home-modern')
                ->chart($trend)
                ->color('success')
                ->extraAttributes(['class' => 'bg-gradient-to-r from-emerald-100 to-white dark:from-emerald-950 dark:to-slate-900']),

            Stat::make('Móveis ativos', Number::format($summary['moveis_ativos_total'], locale: 'pt_BR'))
                ->description('Situações operacionais 1, 7 e 8')
                ->descriptionIcon('heroicon-o-check-badge')
                ->chart($trend)
                ->color('warning')
                ->extraAttributes(['class' => 'bg-gradient-to-r from-amber-100 to-white dark:from-amber-950 dark:to-slate-900']),

            Stat::make('Aquisição de moveis', Number::currency($summary['moveis_valor_aquisicao'], 'BRL', locale: 'pt_BR'))
                ->description('Somatório do valor de aquisição')
                ->descriptionIcon('heroicon-o-banknotes')
                ->chart($trend)
                ->color('primary')
                ->extraAttributes(['class' => 'bg-gradient-to-r from-indigo-100 to-white dark:from-indigo-950 dark:to-slate-900']),
        ];
    }
}
