<?php

namespace App\Services;

use App\Filament\Livewire\AtendimentoPedidos\MateriaisDisponiveisTable;
use App\Filament\Livewire\AtendimentoPedidos\PedidosEmAbertoTable;
use App\Filament\Livewire\Externo\Almoxarifado\CarrinhoMateriaisConsumoForm;
use App\Filament\Livewire\Externo\Almoxarifado\MateriaisConsumoTable;
use App\Filament\Livewire\Externo\Almoxarifado\PedidoItensModal;
use App\Filament\Livewire\Externo\Almoxarifado\PedidosAlmoxarifadoTable;
use App\Filament\Livewire\Externo\Patrimonio\AtividadeDeCampoEmTransferenciaTable;
use App\Filament\Livewire\Externo\Patrimonio\AtividadeDeCampoNaoLocalizadosTable;
use App\Filament\Livewire\Externo\Patrimonio\AtividadeDeCampoResumoTable;
use App\Filament\Livewire\Externo\Patrimonio\BensNoSetorTable;
use App\Filament\Livewire\Externo\Patrimonio\CarrinhoMateriaisPermanentesForm;
use App\Filament\Livewire\Externo\Patrimonio\HistoricoDeInventarioOnlineTable;
use App\Filament\Livewire\Externo\Patrimonio\LevantamentoComissaoTable;
use App\Filament\Livewire\Externo\Patrimonio\MateriaisPermanentesTable;
use App\Filament\Livewire\Externo\Patrimonio\MovimentacaoDeMateriaisTable;
use App\Filament\Livewire\Externo\Patrimonio\PedidoItensModal as PedidoItensModalPatrimonio;
use App\Filament\Livewire\Externo\Patrimonio\PedidosPatrimonioTable;
use App\Filament\Livewire\Patrimonio\ComissoesModal;
use App\Filament\Livewire\Patrimonio\EquipesModal;
use App\Filament\Livewire\Patrimonio\HistoricoMovimentacoesModal;
use App\Filament\Livewire\Patrimonio\MateriaisBaixaModal;
use App\Filament\Livewire\Patrimonio\MateriaisTermoModal;
use App\Filament\Livewire\Patrimonio\TransferirBensAdmModal;
use App\Filament\Livewire\Patrimonio\UnidadesModal;
use App\Filament\Livewire\Patrimonio\ValidarTermoModal;
use App\Filament\Livewire\Patrimonio\VincularBemModal;
use App\Filament\Livewire\PortalTransparencia\AlmoxarifadoCharts;
use App\Filament\Livewire\PortalTransparencia\PatrimonioCharts;
use Livewire\Livewire;

class LivewireComponentsService
{
    public static function getLivewireComponents(): void
    {
        Livewire::component('patrimonio.transferir-bem-adm-modal', TransferirBensAdmModal::class);
        Livewire::component('patrimonio.materiais-baixa-modal', MateriaisBaixaModal::class);
        Livewire::component('patrimonio.materiais-termo-modal', MateriaisTermoModal::class);
        Livewire::component('patrimonio.inventario-comissoes-modal', ComissoesModal::class);
        Livewire::component('patrimonio.inventario-equipes-modal', EquipesModal::class);
        Livewire::component('patrimonio.inventario-unidades-modal', UnidadesModal::class);
        Livewire::component('patrimonio.historico-movimentacoes-modal', HistoricoMovimentacoesModal::class);
        Livewire::component('patrimonio.validar-termo-modal', ValidarTermoModal::class);
        Livewire::component('patrimonio.vincular-bem-modal', VincularBemModal::class);
        Livewire::component('portal-transparencia.patrimonio-charts', PatrimonioCharts::class);
        Livewire::component('portal-transparencia.almoxarifado-charts', AlmoxarifadoCharts::class);
        Livewire::component('externo-almoxarifado.materiais-disponiveis-table', MateriaisConsumoTable::class);
        Livewire::component('externo-almoxarifado.carrinho-pedido-form', CarrinhoMateriaisConsumoForm::class);
        Livewire::component('externo-almoxarifado.pedidos-almoxarifado-table', PedidosAlmoxarifadoTable::class);
        Livewire::component('externo-almoxarifado.pedido-itens-modal', PedidoItensModal::class);
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
