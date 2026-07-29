<?php

namespace App\Filament\Clusters\ExternoCluster\Almoxarifado;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Livewire\Externo\Almoxarifado\CarrinhoPedidoForm;
use App\Filament\Livewire\Externo\Almoxarifado\MateriaisDisponiveisTable;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;

/**
 * Página "casca" do fluxo de requisição de materiais de consumo do Ambiente
 * Externo (legado: pedidos_consumo.php). Não guarda estado nem regra de
 * negócio — só monta, via blade, os componentes Livewire que fazem o
 * trabalho de verdade:
 *
 * - {@see MateriaisDisponiveisTable}
 *   lista/pagina/pesquisa os materiais do tipo e dispara o evento de
 *   "adicionar ao carrinho".
 * - {@see CarrinhoPedidoForm}
 *   guarda o carrinho, o formulário de destino/justificativa e envia o pedido.
 *
 * As páginas filhas só diferem pelo tipo de material exposto (Consumo x
 * Consumo Durável).
 */
abstract class RequisicaoDeMateriaisPage extends Page
{
    protected static ?string $cluster = ExternoCluster::class;

    abstract public function tipoMaterial(): string;

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
