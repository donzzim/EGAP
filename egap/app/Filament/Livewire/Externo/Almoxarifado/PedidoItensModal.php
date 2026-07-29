<?php

namespace App\Filament\Livewire\Externo\Almoxarifado;

use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Almoxarifado\ItemPedido;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

/**
 * Itens de um pedido de materiais de consumo, exibidos no modal aberto a
 * partir de {@see PedidosAlmoxarifadoTable} (legado: modal_pedidos.api.php,
 * ramo do setor responsável = Almoxarifado).
 */
class PedidoItensModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $pedidoId;

    public function mount(int $pedidoId): void
    {
        $this->pedidoId = $pedidoId;
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('material_nome')
                    ->label('Material')
                    ->wrap(),

                TextColumn::make('quantidades')
                    ->label('Qtde. Solicitada / Atendida')
                    ->alignCenter()
                    ->getStateUsing(fn (ItemPedido $record): string => "{$record->quantidade_solicitada} / {$record->quantidade_atendida}"),

                TableColumns::money('valor_material', 'Preço Unitário Médio'),

                TextColumn::make('subtotal_atendido')
                    ->label('Subtotal Atendido')
                    ->alignCenter()
                    ->money('BRL')
                    ->getStateUsing(fn (ItemPedido $record): float => (float) $record->valor_material * $record->quantidade_atendida),

                TextColumn::make('ObservacaoItem')
                    ->label('Observações')
                    ->default('-')
                    ->wrap()
                    ->description(fn (ItemPedido $record): ?string => $this->observacaoDescricao($record)),

                TableColumns::text('situacaoRef.Descricao', 'Status')
                    ->badge()
                    ->color(fn (ItemPedido $record): string => $this->statusColor((int) $record->situacao)),
            ])
            ->actions([])
            ->bulkActions([])
            ->paginated(false)
            ->defaultSort('situacao')
            ->emptyStateHeading('Nenhum item neste pedido');
    }

    protected function observacaoDescricao(ItemPedido $item): ?string
    {
        if ($item->validado_por && $item->data_validacao) {
            return "Atendido por: {$item->validadoPor?->name} em {$item->data_validacao->format('d/m/Y')}";
        }

        if ($item->cancelado_por && $item->data_cancelado) {
            return "Cancelado por: {$item->canceladoPor?->name} em {$item->data_cancelado->format('d/m/Y')}";
        }

        return null;
    }

    protected function statusColor(int $status): string
    {
        return match ($status) {
            3, 7 => 'success',
            4 => 'gray',
            5 => 'danger',
            6 => 'warning',
            default => 'gray',
        };
    }

    protected function getQuery(): Builder
    {
        return ItemPedido::query()
            ->where('idPedido', $this->pedidoId)
            ->with(['descricaoDetalhadaRel', 'materialRel', 'situacaoRef', 'validadoPor', 'canceladoPor'])
            ->orderBy('situacao');
    }

    public function render(): View
    {
        return view('livewire.externo.almoxarifado.pedido-itens-modal');
    }
}
