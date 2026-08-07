<?php

namespace App\Filament\Livewire\Externo\Almoxarifado;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Livewire\Externo\PedidosTable;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

/**
 * Lista os pedidos de materiais de consumo do Ambiente Externo cujo setor
 * responsável pelo atendimento é o Almoxarifado (legado: consultar_pedidos.php
 * + status_pedidos.api.php, com setorresponsavel = 799).
 *
 * O detalhe dos itens de cada pedido é aberto em modal, delegado ao
 * {@see PedidoItensModal} (legado: modal_pedidos.api.php).
 */
class PedidosAlmoxarifadoTable extends PedidosTable implements HasActions
{
    use InteractsWithActions;
    protected const SETOR_ALMOXARIFADO = 799;

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

                TableColumns::text('Observacao', 'Justificativa da Necessidade')
                    ->wrap()
                    ->limit(80),

                $this->colunaComplementoSetor(),
                $this->colunaMateriais(),
                $this->colunaStatus($this->statusColor(...)),
            ])
            ->filters([
                SelectFilter::make('situacao_grupo')
                    ->label('Situação')
                    ->options([
                        'pendente' => 'Em análise',
                        'atendido' => 'Atendidos',
                        'cancelado' => 'Cancelados',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'pendente' => $query->where('idSituacao', 6),
                            'atendido' => $query->whereIn('idSituacao', [3, 7]),
                            'cancelado' => $query->where('idSituacao', 4),
                            default => $query,
                        };
                    }),

                $this->filtroLocalizacao(),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->recordActions([
                $this->acaoVerItens('externo-almoxarifado.pedido-itens-modal'),
            ])
            ->toolbarActions([])
            ->defaultSort('date_time', 'desc')
            ->emptyStateHeading('Nenhum pedido encontrado');
    }

    protected function statusColor(int $status): string
    {
        return match ($status) {
            3, 7 => 'success',
            4, 5 => 'danger',
            6 => 'warning',
            default => 'gray',
        };
    }

    protected function setorResponsavel(): int
    {
        return self::SETOR_ALMOXARIFADO;
    }
}
