<?php

namespace App\Filament\Clusters\ExternoCluster;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Agendamento\SolicitacaoVeiculos;
use App\Filament\Clusters\ExternoCluster\Almoxarifado\AcompanhamentoDePedidosAlmoxarifado;
use App\Filament\Clusters\ExternoCluster\Almoxarifado\MateriaisDeConsumo;
use App\Filament\Clusters\ExternoCluster\Almoxarifado\MateriaisDeConsumoDuraveis;
use App\Filament\Clusters\ExternoCluster\Patrimonio\AcompanhamentoDePedidosPatrimonio;
use App\Filament\Clusters\ExternoCluster\Patrimonio\AtividadeDeCampo;
use App\Filament\Clusters\ExternoCluster\Patrimonio\BensNoSetor;
use App\Filament\Clusters\ExternoCluster\Patrimonio\HistoricoDeInventarioOnline;
use App\Filament\Clusters\ExternoCluster\Patrimonio\LevantamentoComissao;
use App\Filament\Clusters\ExternoCluster\Patrimonio\MovimentacaoDeMateriais;
use App\Filament\Clusters\ExternoCluster\Patrimonio\RequisicaoDeMateriais;
use App\Models\Admin\Lotacao;
use App\Models\UserEgap;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class HomePageExterno extends Page
{
    protected static ?string $cluster = ExternoCluster::class;

    protected static ?int $navigationSort = -1;

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::HomeModern;

    protected static ?string $slug = 'home';

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $title = 'EGAP';

    protected string $view = 'filament.pages.externo.home';

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function usuarioAtual(): ?UserEgap
    {
        return UserEgap::currentAuthenticated();
    }

    public function primeiroNomeUsuario(): string
    {
        $nome = $this->usuarioAtual()?->name;

        return filled($nome) ? (string) str($nome)->before(' ') : 'visitante';
    }

    public function lotacaoAtual(): ?Lotacao
    {
        return $this->usuarioAtual()
            ?->lotacoes()
            ->with(['unidadeJudiciaria', 'setorRef'])
            ->first();
    }

    /**
     * Cartões de acesso rápido às funcionalidades do Ambiente Externo,
     * agrupados pela mesma área usada nos grupos de navegação lateral do
     * cluster (Almoxarifado, Patrimônio e Agendamento de Veículos).
     *
     * @return array<string, array<int, array{label: string, description: string, icon: string, url: string}>>
     */
    public function gruposDeAcesso(): array
    {
        return [
            'Almoxarifado' => [
                [
                    'label' => 'Materiais de Consumo',
                    'description' => 'Requisitar materiais de consumo do almoxarifado.',
                    'icon' => 'heroicon-o-inbox',
                    'url' => MateriaisDeConsumo::getUrl(),
                ],
                [
                    'label' => 'Materiais de Cons. Duráveis',
                    'description' => 'Requisitar materiais de consumo duráveis do almoxarifado.',
                    'icon' => 'heroicon-o-inbox-stack',
                    'url' => MateriaisDeConsumoDuraveis::getUrl(),
                ],
                [
                    'label' => 'Acompanhamento de Pedidos',
                    'description' => 'Consultar o andamento dos pedidos de materiais de consumo.',
                    'icon' => 'heroicon-o-arrow-down-tray',
                    'url' => AcompanhamentoDePedidosAlmoxarifado::getUrl(),
                ],
            ],
            'Patrimônio' => [
                [
                    'label' => 'Bens no Setor (Inventário)',
                    'description' => 'Consultar e conferir os bens patrimoniais do setor.',
                    'icon' => 'heroicon-o-building-office-2',
                    'url' => BensNoSetor::getUrl(),
                ],
                [
                    'label' => 'Movimentação de Materiais',
                    'description' => 'Solicitar a movimentação de bens entre setores.',
                    'icon' => 'heroicon-o-arrows-right-left',
                    'url' => MovimentacaoDeMateriais::getUrl(),
                ],
                [
                    'label' => 'Requisição de Materiais Permanentes',
                    'description' => 'Requisitar materiais permanentes à Seção de Patrimônio.',
                    'icon' => 'heroicon-o-cube',
                    'url' => RequisicaoDeMateriais::getUrl(),
                ],
                [
                    'label' => 'Acompanhamento de Pedidos',
                    'description' => 'Consultar o andamento dos pedidos de materiais permanentes.',
                    'icon' => 'heroicon-o-arrow-down-tray',
                    'url' => AcompanhamentoDePedidosPatrimonio::getUrl(),
                ],
                [
                    'label' => 'Histórico de Inventário Online',
                    'description' => 'Consultar os inventários já realizados no setor.',
                    'icon' => 'heroicon-o-clock',
                    'url' => HistoricoDeInventarioOnline::getUrl(),
                ],
                [
                    'label' => 'Levantamento de Comissão',
                    'description' => 'Registrar o levantamento físico da Comissão de Inventário.',
                    'icon' => 'heroicon-o-users',
                    'url' => LevantamentoComissao::getUrl(),
                ],
                [
                    'label' => 'Atividade de Campo',
                    'description' => 'Acompanhar o progresso da conferência de inventário por setor.',
                    'icon' => 'heroicon-o-briefcase',
                    'url' => AtividadeDeCampo::getUrl(),
                ],
            ],
            'Agendamento de Veículos' => [
                [
                    'label' => 'Solicitação de Veículo',
                    'description' => 'Solicitar veículo para deslocamento oficial.',
                    'icon' => 'heroicon-o-truck',
                    'url' => SolicitacaoVeiculos::getUrl(),
                ],
            ],
        ];
    }
}
