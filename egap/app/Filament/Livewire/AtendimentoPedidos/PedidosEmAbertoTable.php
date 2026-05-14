<?php

namespace App\Filament\Egap\Livewire\AtendimentoPedidos;

use App\Models\Egap\Almoxarifado\ItemPedido;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class PedidosEmAbertoTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $selectedItemPedidoId = null;

    #[On('limpar-selecao-pedidos')]
    public function limparSelecaoPedidos(): void
    {
        $this->selectedItemPedidoId = null;
    }

    #[On('refresh-pedidos-table')]
    public function refreshPedidosTable(): void
    {
        // Apenas força o rerender.
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pedidos em aberto')
            ->query($this->getPedidosQuery())
            ->defaultSort('pedido_id', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->recordClasses(function (ItemPedido $record): string {
                return $this->selectedItemPedidoId === (int) $record->item_id
                    ? 'bg-primary-50 dark:bg-primary-900/20'
                    : '';
            })
            ->columns([
                Tables\Columns\TextColumn::make('pedido_id')
                    ->label('Pedido')
                    ->description(fn (ItemPedido $record) => $record->item_id
                        ? "Item: {$record->item_id}"
                        : null
                    )
                    ->badge()
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $subQuery) use ($search): void {
                            $subQuery
                                ->where('ped.id', 'like', "%{$search}%")
                                ->orWhere('ped_itempedido.id', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('unidade')
                    ->label('Unidade/Setor')
                    ->description(fn (ItemPedido $record) => $record->setor ?? null)
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $subQuery) use ($search): void {
                            $subQuery
                                ->where('se.UnidadeOrganizacional', 'like', "%{$search}%")
                                ->orWhere('se.Setor', 'like', "%{$search}%")
                                ->orWhere('com.descricao', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('material')
                    ->label('Material')
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $subQuery) use ($search): void {
                            $subQuery
                                ->where('dd.descricao_detalhada', 'like', "%{$search}%")
                                ->orWhere('dr_item.Descricao', 'like', "%{$search}%")
                                ->orWhere('dr_dd.Descricao', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Solicitada')
                    ->badge()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('quantidade_validada')
                    ->label('Validada')
                    ->badge()
                    ->alignEnd()
                    ->color('info'),

                Tables\Columns\TextColumn::make('quantidade_atendida')
                    ->label('Atendida')
                    ->badge()
                    ->alignEnd()
                    ->color('success'),

                Tables\Columns\TextColumn::make('situacao')
                    ->label('Situação')
                    ->badge(),
            ])
            ->actions([
                Action::make('selecionar')
                    ->label(function (ItemPedido $record): string {
                        return $this->selectedItemPedidoId === (int) $record->item_id
                            ? 'Desmarcar'
                            : 'Selecionar';
                    })
                    ->icon(function (ItemPedido $record): string {
                        return $this->selectedItemPedidoId === (int) $record->item_id
                            ? 'heroicon-o-x-mark'
                            : 'heroicon-o-check-circle';
                    })
                    ->color(function (ItemPedido $record): string {
                        return $this->selectedItemPedidoId === (int) $record->item_id
                            ? 'gray'
                            : 'primary';
                    })
                    ->action(function (ItemPedido $record): void {
                        $itemId = (int) $record->item_id;

                        if ($this->selectedItemPedidoId === $itemId) {
                            $this->selectedItemPedidoId = null;

                            $this->dispatch(
                                'pedido-selecionado',
                                pedidoId: 0,
                                itemPedidoId: 0,
                                protocolo: '-',
                                solicitante: '-',
                                destino: '-',
                                material: '-',
                                materialId: 0,
                                materialResumo: '-',
                                situacao: '-',
                                quantidadeSolicitada: 0,
                                quantidadeValidada: 0,
                                quantidadeAtendida: 0,
                            );

                            return;
                        }

                        $this->selectedItemPedidoId = $itemId;

                        $destino = collect([
                            $record->unidade,
                            $record->setor,
                            $record->complementosetor,
                        ])->filter()->implode(' / ');

                        $this->dispatch(
                            'pedido-selecionado',
                            pedidoId: (int) $record->pedido_id,
                            itemPedidoId: $itemId,
                            protocolo: '-',
                            solicitante: '-',
                            destino: $destino ?: '-',
                            material: (string) ($record->material ?? '-'),
                            materialId: (int) ($record->material_id ?? 0),
                            materialResumo: (string) ($record->descricao_resumida ?? '-'),
                            situacao: (string) ($record->situacao ?? '-'),
                            quantidadeSolicitada: (int) ($record->quantidade ?? 0),
                            quantidadeValidada: (int) ($record->quantidade_validada ?? 0),
                            quantidadeAtendida: (int) ($record->quantidade_atendida ?? 0),
                        );
                    }),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Nenhum pedido disponível para atendimento')
            ->emptyStateDescription('A consulta não retornou itens pendentes neste momento.');
    }

    protected function getPedidosQuery(): Builder
    {
        return ItemPedido::query()
            ->join('ped_pedidos as ped', 'ped.id', '=', 'ped_itempedido.idPedido')
            ->join('mat_setores as se', 'ped.Setor', '=', 'se.id')
            ->join('mat_complementosetor as com', 'ped.ComplementoSetor', '=', 'com.id')
            ->leftJoin('mat_descricaoresumida as dr_item', 'ped_itempedido.material', '=', 'dr_item.id')
            ->leftJoin('mat_descricaodetalhada as dd', 'ped_itempedido.DescricaoDetalhada', '=', 'dd.id')
            ->leftJoin('mat_descricaoresumida as dr_dd', 'dd.descricao_resumida', '=', 'dr_dd.id')
            ->join('ped_situacao as sit', 'ped_itempedido.situacao', '=', 'sit.id')
            ->whereIn('ped_itempedido.situacao', [6, 7])
            ->where('ped.setor_responsavel', 1239)
            ->where(function (Builder $query): void {
                $query
                    ->where(function (Builder $subQuery): void {
                        $subQuery
                            ->whereNotNull('ped_itempedido.material')
                            ->where('ped_itempedido.material', '<>', 0);
                    })
                    ->orWhere(function (Builder $subQuery): void {
                        $subQuery
                            ->whereNotNull('dd.descricao_resumida')
                            ->where('dd.descricao_resumida', '<>', 0);
                    });
            })
            ->select([
                'ped_itempedido.id',
                'ped.id as pedido_id',
                'ped_itempedido.id as item_id',
                'se.CodigodaUO as unidade_id',
                'se.UnidadeOrganizacional as unidade',
                'se.id as setor_id',
                'se.Setor as setor',
                'com.id as complementosetor_id',
                'com.descricao as complementosetor',
                DB::raw('COALESCE(NULLIF(dd.descricao_resumida, 0), NULLIF(ped_itempedido.material, 0)) as material_id'),
                DB::raw("COALESCE(NULLIF(TRIM(dd.descricao_detalhada), ''), dr_item.Descricao, dr_dd.Descricao) as material"),
                DB::raw('COALESCE(dr_dd.Descricao, dr_item.Descricao) as descricao_resumida'),
                'ped_itempedido.QuantidadeMaterial as quantidade',
                DB::raw('IFNULL(ped_itempedido.quantidade_validada, 0) as quantidade_validada'),
                'ped_itempedido.QuantidadeMaterialAtendida as quantidade_atendida',
                'sit.id as situacao_id',
                'sit.Descricao as situacao',
            ]);
    }

    public function render(): View
    {
        return view('livewire.atendimento-pedidos.pedidos-em-aberto-table');
    }
}
