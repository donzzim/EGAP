<?php

namespace App\Services;

use App\Filament\Clusters\ExternoCluster\Livewire\Agendamento\SolicitacaoVeiculoForm;
use App\Filament\Clusters\ExternoCluster\Livewire\Almoxarifado\CarrinhoMateriaisConsumoForm;
use App\Filament\Clusters\ExternoCluster\Livewire\Almoxarifado\MateriaisConsumoTable;
use App\Filament\Clusters\ExternoCluster\Livewire\Almoxarifado\PedidoItensModal;
use App\Filament\Clusters\ExternoCluster\Livewire\Almoxarifado\PedidosAlmoxarifadoTable;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\AtividadeDeCampoEmTransferenciaTable;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\AtividadeDeCampoNaoLocalizadosTable;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\AtividadeDeCampoResumoTable;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\BensNoSetorTable;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\CarrinhoMateriaisPermanentesForm;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\HistoricoDeInventarioOnlineTable;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\LevantamentoComissaoTable;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\MateriaisPermanentesTable;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\MovimentacaoDeMateriaisTable;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\PedidoItensModal as PedidoItensModalPatrimonio;
use App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\PedidosPatrimonioTable;
use App\Filament\Livewire\AtendimentoPedidos\MateriaisDisponiveisTable;
use App\Filament\Livewire\AtendimentoPedidos\PedidosEmAbertoTable;
use App\Filament\Livewire\Patrimonio\BensImoveis\DepreciacaoImovelModal;
use App\Filament\Livewire\Patrimonio\BensImoveis\ObrasModal;
use App\Filament\Livewire\Patrimonio\BensImoveis\OcupacoesModal;
use App\Filament\Livewire\Patrimonio\BensImoveis\ReavaliacoesModal;
use App\Filament\Livewire\Patrimonio\BensImoveis\TributosModal;
use App\Filament\Livewire\Patrimonio\BensMoveis\ComissoesModal;
use App\Filament\Livewire\Patrimonio\BensMoveis\DepreciacaoModal;
use App\Filament\Livewire\Patrimonio\BensMoveis\EquipesModal;
use App\Filament\Livewire\Patrimonio\BensMoveis\HistoricoMovimentacoesModal;
use App\Filament\Livewire\Patrimonio\BensMoveis\MateriaisBaixaModal;
use App\Filament\Livewire\Patrimonio\BensMoveis\MateriaisTermoModal;
use App\Filament\Livewire\Patrimonio\BensMoveis\UnidadesModal;
use App\Filament\Livewire\Patrimonio\BensMoveis\ValidarTermoModal;
use App\Filament\Livewire\PortalTransparencia\AlmoxarifadoCharts;
use App\Filament\Livewire\PortalTransparencia\PatrimonioCharts;
use Livewire\Livewire;

class LivewireComponentsService
{
    public static function getLivewireComponents(): void
    {
        //PATRIMONIO
        Livewire::component('patrimonio.materiais-baixa-modal', MateriaisBaixaModal::class);
        Livewire::component('patrimonio.materiais-termo-modal', MateriaisTermoModal::class);
        Livewire::component('patrimonio.inventario-comissoes-modal', ComissoesModal::class);
        Livewire::component('patrimonio.inventario-equipes-modal', EquipesModal::class);
        Livewire::component('patrimonio.inventario-unidades-modal', UnidadesModal::class);
        Livewire::component('patrimonio.historico-movimentacoes-modal', HistoricoMovimentacoesModal::class);
        Livewire::component('patrimonio.depreciacao-modal', DepreciacaoModal::class);
        Livewire::component('patrimonio.depreciacao-imovel-modal', DepreciacaoImovelModal::class);
        Livewire::component('patrimonio.validar-termo-modal', ValidarTermoModal::class);
        Livewire::component('patrimonio.tributos-modal', TributosModal::class);
        Livewire::component('patrimonio.ocupacoes-modal', OcupacoesModal::class);
        Livewire::component('patrimonio.reavaliacoes-modal', ReavaliacoesModal::class);
        Livewire::component('patrimonio.obras-modal', ObrasModal::class);

        // PORTAL TRANSPARÊNCIA
        Livewire::component('portal-transparencia.patrimonio-charts', PatrimonioCharts::class);
        Livewire::component('portal-transparencia.almoxarifado-charts', AlmoxarifadoCharts::class);

        //EXTERNO AGENDAMENTO
        Livewire::component('externo-agendamento.solicitacao-veiculo-form', SolicitacaoVeiculoForm::class);

        //EXTERNO ALMOXARIFADO
        Livewire::component('externo-almoxarifado.materiais-disponiveis-table', MateriaisConsumoTable::class);
        Livewire::component('externo-almoxarifado.carrinho-pedido-form', CarrinhoMateriaisConsumoForm::class);
        Livewire::component('externo-almoxarifado.pedidos-almoxarifado-table', PedidosAlmoxarifadoTable::class);
        Livewire::component('externo-almoxarifado.pedido-itens-modal', PedidoItensModal::class);

        //EXTERNO PATRIMONIO
        Livewire::component('externo-patrimonio.bens-no-setor-table', BensNoSetorTable::class);
        Livewire::component('externo-patrimonio.movimentacao-de-materiais-table', MovimentacaoDeMateriaisTable::class);
        Livewire::component('externo-patrimonio.historico-de-inventario-online-table', HistoricoDeInventarioOnlineTable::class);
        Livewire::component('externo-patrimonio.levantamento-comissao-table', LevantamentoComissaoTable::class);
        Livewire::component('externo-patrimonio.atividade-de-campo-resumo-table', AtividadeDeCampoResumoTable::class);
        Livewire::component('externo-patrimonio.atividade-de-campo-nao-localizados-table', AtividadeDeCampoNaoLocalizadosTable::class);
        Livewire::component('externo-patrimonio.atividade-de-campo-em-transferencia-table', AtividadeDeCampoEmTransferenciaTable::class);
        Livewire::component('externo-patrimonio.carrinho-pedido-form', CarrinhoMateriaisPermanentesForm::class);
        Livewire::component('externo-patrimonio.materiais-permanentes-disponiveis-table', MateriaisPermanentesTable::class);
        Livewire::component('externo-patrimonio.pedidos-patrimonio-table', PedidosPatrimonioTable::class);
        Livewire::component('externo-patrimonio.pedido-itens-modal', PedidoItensModalPatrimonio::class);
        Livewire::component('atendimento-pedidos.pedidos-em-aberto-table', PedidosEmAbertoTable::class);
        Livewire::component('atendimento-pedidos.materiais-disponiveis-table', MateriaisDisponiveisTable::class);
    }
}
