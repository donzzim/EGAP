<?php

namespace App\Filament\Clusters\ExternoCluster\Pages;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Almoxarifado\ExecucaoOrcamentaria as ExecucaoOrcamentariaAlmoxarifadoGeral;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Almoxarifado\ExecucaoOrcamentariaAlmoxarifado;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Almoxarifado\MateriaisConsumo;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Almoxarifado\MateriaisConsumoAlmoxarifado;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Almoxarifado\RequisicoesBensConsumoAtendidas;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio\BensPatrimoniaisBaixados;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio\BensPatrimoniaisMovimentados;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio\BensPermanentesMoveis;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio\BensPermanentesMoveisPatrimonio;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio\BensSolicitados;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio\ExecucaoOrcamentaria as ExecucaoOrcamentariaPatrimonioGeral;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio\ExecucaoOrcamentariaPatrimonio;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio\InventarioOnlineSituacaoContabil;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio\InventarioOnlineSituacaoInventario;
use App\Filament\Clusters\ExternoCluster\Widgets\PortalTransparencia\Patrimonio\SituacaoBensPatrimoniaisEgap;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class PortalTransparencia extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Portal Transparência';

    protected static ?int $navigationSort = 100;

    protected static ?string $cluster = ExternoCluster::class;

    protected static ?string $title = 'Portal Transparência';

    public function getSubheading(): string|Htmlable|null
    {
        return 'Indicadores públicos de patrimônio e almoxarifado, atualizados automaticamente a partir dos dados do sistema.';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Patrimônio')
                    ->description('Aquisição, movimentação, baixa e situação atual dos bens patrimoniais.')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                    ->schema($this->getWidgetsSchemaComponents([
                        BensPermanentesMoveis::class,
                        BensPermanentesMoveisPatrimonio::class,
                        BensPatrimoniaisMovimentados::class,
                        BensPatrimoniaisBaixados::class,
                        SituacaoBensPatrimoniaisEgap::class,
                        BensSolicitados::class,
                        ExecucaoOrcamentariaPatrimonioGeral::class,
                        ExecucaoOrcamentariaPatrimonio::class,
                        InventarioOnlineSituacaoContabil::class,
                        InventarioOnlineSituacaoInventario::class,
                    ])),

                Section::make('Almoxarifado')
                    ->description('Materiais de consumo fornecidos, requisições atendidas e execução orçamentária.')
                    ->icon('heroicon-o-archive-box')
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                    ->schema($this->getWidgetsSchemaComponents([
                        MateriaisConsumo::class,
                        MateriaisConsumoAlmoxarifado::class,
                        RequisicoesBensConsumoAtendidas::class,
                        ExecucaoOrcamentariaAlmoxarifadoGeral::class,
                        ExecucaoOrcamentariaAlmoxarifado::class,
                    ])),
            ]);
    }
}
