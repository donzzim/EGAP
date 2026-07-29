<?php

namespace App\Services;

use App\Filament\Livewire\Externo\Almoxarifado\CarrinhoPedidoForm;
use App\Filament\Livewire\Externo\Almoxarifado\MateriaisDisponiveisTable;
use App\Filament\Livewire\Externo\Almoxarifado\PedidoItensModal;
use App\Filament\Livewire\Externo\Almoxarifado\PedidosAlmoxarifadoTable;
use App\Filament\Livewire\Patrimonio\ComissoesModal;
use App\Filament\Livewire\Patrimonio\EquipesModal;
use App\Filament\Livewire\Patrimonio\HistoricoMovimentacoesModal;
use App\Filament\Livewire\Patrimonio\MateriaisBaixaModal;
use App\Filament\Livewire\Patrimonio\MateriaisTermoModal;
use App\Filament\Livewire\Patrimonio\TransferirBensAdmModal;
use App\Filament\Livewire\Patrimonio\UnidadesModal;
use App\Filament\Livewire\Patrimonio\ValidarTermoModal;
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
        Livewire::component('portal-transparencia.patrimonio-charts', PatrimonioCharts::class);
        Livewire::component('portal-transparencia.almoxarifado-charts', AlmoxarifadoCharts::class);
        Livewire::component('externo-almoxarifado.materiais-disponiveis-table', MateriaisDisponiveisTable::class);
        Livewire::component('externo-almoxarifado.carrinho-pedido-form', CarrinhoPedidoForm::class);
        Livewire::component('externo-almoxarifado.pedidos-almoxarifado-table', PedidosAlmoxarifadoTable::class);
        Livewire::component('externo-almoxarifado.pedido-itens-modal', PedidoItensModal::class);
    }
}
