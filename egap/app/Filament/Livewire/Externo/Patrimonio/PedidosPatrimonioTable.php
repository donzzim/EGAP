<?php

namespace App\Filament\Livewire\Externo\Patrimonio;

use App\Filament\Livewire\Externo\PedidosTable;
use App\Filament\Support\TableDefaults;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

/**
 * Lista os pedidos de materiais permanentes do Ambiente Externo cujo setor
 * responsável pelo atendimento é a Seção de Patrimônio (legado:
 * consultar_pedidos.php + status_pedidos.api.php, com setorresponsavel =
 * 1239).
 *
 * Diferente do Almoxarifado, não exibe coluna de Justificativa no nível do
 * pedido — a justificativa é registrada por item (ver
 * {@see PedidoItensModal}) — e os status são agrupados em só dois filtros
 * (Pendentes/Concluídos), como no legado.
 *
 * O detalhe dos itens de cada pedido é aberto em modal, delegado ao
 * {@see PedidoItensModal} (legado: modal_pedidos.api.php).
 */
class PedidosPatrimonioTable extends PedidosTable implements HasActions
{
    use InteractsWithActions;

    protected const SETOR_PATRIMONIO = 1239;

    #[On('pedido-item-cancelado')]
    public function refreshTable(): void
    {
        // Apenas força o rerender, para atualizar a contagem de itens e o status do pedido.
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getQuery())
            ->columns([
                $this->colunaNumeroPedido(),
                $this->colunaData(),
                $this->colunaSolicitante(),
                $this->colunaComplementoSetor(),
                $this->colunaMateriais(),
                $this->colunaStatus($this->statusColor(...)),
            ])
            ->filters([
                SelectFilter::make('situacao_grupo')
                    ->label('Situação')
                    ->options([
                        'pendente' => 'Pendentes',
                        'concluido' => 'Concluídos',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'pendente' => $query->whereIn('idSituacao', [6, 8, 9, 10]),
                            'concluido' => $query->whereIn('idSituacao', [3, 4, 5, 7]),
                            default => $query,
                        };
                    }),

                $this->filtroLocalizacao(),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->recordActions([
                $this->acaoVerItens('externo-patrimonio.pedido-itens-modal'),
            ])
            ->toolbarActions([])
            ->defaultSort('date_time', 'desc')
            ->emptyStateHeading('Nenhum pedido encontrado');
    }

    protected function statusColor(int $status): string
    {
        return match ($status) {
            3, 7 => 'success',
            4, 10 => 'gray',
            5 => 'danger',
            6, 9 => 'warning',
            8 => 'info',
            default => 'gray',
        };
    }

    protected function setorResponsavel(): int
    {
        return self::SETOR_PATRIMONIO;
    }
}
