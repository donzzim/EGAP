<?php

namespace App\Filament\Livewire\Externo\Almoxarifado;

use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Almoxarifado\FasePedido;
use App\Models\Almoxarifado\ItemPedido;
use App\Models\Almoxarifado\Pedidos;
use App\Models\UserEgap;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
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

    protected const STATUS_PENDENTE = 6;

    protected const STATUS_CANCELADO = 4;

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
            ->actions([
                $this->cancelarItemAction(),
            ])
            ->bulkActions([
                $this->cancelarItensBulkAction(),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (ItemPedido $record): bool => (int) $record->situacao === self::STATUS_PENDENTE
            )
            ->paginated(false)
            ->defaultSort('situacao')
            ->emptyStateHeading('Nenhum item neste pedido');
    }

    protected function cancelarItemAction(): Action
    {
        return Action::make('cancelar_item')
            ->label('Cancelar')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (ItemPedido $record): bool => (int) $record->situacao === self::STATUS_PENDENTE)
            ->modalHeading('Cancelar item do pedido')
            ->modalDescription('Informe o motivo do cancelamento. Essa ação não pode ser desfeita.')
            ->modalSubmitActionLabel('Confirmar cancelamento')
            ->form([
                Select::make('cancelado_por')
                    ->label('Responsável pelo cancelamento')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->options(fn (): array => UserEgap::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()),
                Textarea::make('justificativa')
                    ->label('Justificativa do cancelamento')
                    ->required()
                    ->rows(3)
                    ->maxLength(200),
            ])
            ->action(fn (ItemPedido $record, array $data) => $this->cancelarItens(
                collect([$record]),
                $data['justificativa'],
                (int) $data['cancelado_por'],
            ));
    }

    protected function cancelarItensBulkAction(): BulkAction
    {
        return BulkAction::make('cancelar_itens')
            ->label('Cancelar selecionados')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->modalHeading('Cancelar itens do pedido')
            ->modalDescription('Informe o motivo do cancelamento. Essa ação não pode ser desfeita.')
            ->modalSubmitActionLabel('Confirmar cancelamento')
            ->form([
                Select::make('cancelado_por')
                    ->label('Responsável pelo cancelamento')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->options(fn (): array => UserEgap::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()),
                Textarea::make('justificativa')
                    ->label('Justificativa do cancelamento')
                    ->required()
                    ->rows(3)
                    ->maxLength(200),
            ])
            ->deselectRecordsAfterCompletion()
            ->action(fn (EloquentCollection $records, array $data) => $this->cancelarItens(
                $records,
                $data['justificativa'],
                (int) $data['cancelado_por'],
            ));
    }

    protected function cancelarItens(Collection $records, string $justificativa, int $responsavelId): void
    {
        $itens = $records->filter(fn (ItemPedido $item): bool => (int) $item->situacao === self::STATUS_PENDENTE);

        if ($itens->isEmpty()) {
            Notification::make()
                ->title('Nenhum item pendente selecionado para cancelamento.')
                ->warning()
                ->send();

            return;
        }

        foreach ($itens as $item) {
            $item->update([
                'situacao' => self::STATUS_CANCELADO,
                'ObservacaoItem' => $justificativa,
                'data_cancelado' => now(),
                'cancelado_por' => $responsavelId,
            ]);

            FasePedido::query()->create([
                'idSituacao' => self::STATUS_CANCELADO,
                'Descricao' => 'Item cancelado pelo solicitante via portal externo.',
                'id_pedido' => $item->idPedido,
                'id_itempedido' => $item->id,
                'id_descricaoresumida' => $item->material,
                'id_descricaodetalhada' => $item->DescricaoDetalhada,
                'quantidade' => $item->QuantidadeMaterial,
            ]);
        }

        Notification::make()
            ->title($itens->count() === 1 ? 'Item cancelado com sucesso.' : "{$itens->count()} itens cancelados com sucesso.")
            ->success()
            ->send();

        $this->cancelarPedidoSeTodosItensCancelados($responsavelId);

        $this->dispatch('pedido-item-cancelado');
    }

    protected function cancelarPedidoSeTodosItensCancelados(int $responsavelId): void
    {
        $totalItens = ItemPedido::query()->where('idPedido', $this->pedidoId)->count();

        $totalCancelados = ItemPedido::query()
            ->where('idPedido', $this->pedidoId)
            ->where('situacao', self::STATUS_CANCELADO)
            ->count();

        if ($totalItens === 0 || $totalItens !== $totalCancelados) {
            return;
        }

        Pedidos::query()->whereKey($this->pedidoId)->update([
            'idSituacao' => self::STATUS_CANCELADO,
            'ResponsavelAtendimento' => $responsavelId,
        ]);

        FasePedido::query()->create([
            'idSituacao' => self::STATUS_CANCELADO,
            'Descricao' => 'Pedido cancelado automaticamente: todos os itens foram cancelados.',
            'id_pedido' => $this->pedidoId,
        ]);
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
