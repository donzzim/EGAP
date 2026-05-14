<?php

namespace App\Filament\Clusters\PedidosCluster\Requisicao;

use App\Filament\Clusters\PedidosCluster;
use App\Models\Almoxarifado\Pedidos as PedidoModel;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Pedidos extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $cluster = PedidosCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Requisição';
    protected static ?string $title = 'Pedido - Materiais Permanentes';
    protected static ?string $slug = 'pedidos-materiais-permanentes';
    protected static ?string $navigationLabel = 'Pedidos';
    protected static string $view = 'filament.pages.pedidos.requisicao.pedidos';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PedidoModel::query()
                    ->with([
                        'solicitante_get',
                        'setor_get',
                        'unidade_judiciaria',
                        'situacao',
                        'itens.situacaoRef',
                        'itens.materialRel',
                        'itens.descricaoDetalhadaRel',
                    ])
            )
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date_time')
                    ->label('Data Pedido')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('solicitante_get.name')
                    ->label('Solicitante')
                    ->default('-')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('setor_get.Setor')
                    ->label('Setor')
                    ->default('-')
                    ->searchable()
                    ->wrap()
                    ->description(fn (PedidoModel $record): ?string => $record->unidade_judiciaria?->UnidadeOrganizacional),

                TextColumn::make('materiais_resumo')
                    ->label('Material')
                    ->wrap()
                    ->html()
                    ->getStateUsing(fn (PedidoModel $record): string => $record->itens
                        ->map(fn ($item) => $item->material_nome)
                        ->filter()
                        ->unique()
                        ->take(2)
                        ->implode('<br>')
                    ?: '-'),

                TextColumn::make('justificativa')
                    ->label('Justificativa')
                    ->alignCenter()
                    ->default('-')
                    ->wrap(),

                TextColumn::make('qtde_solicitada')
                    ->label('Qtde Solicitada')
                    ->alignCenter()
                    ->getStateUsing(fn (PedidoModel $record): int => (int) $record->itens->sum('QuantidadeMaterial')),

                TextColumn::make('qtde_atendida')
                    ->label('Qtde Atendida')
                    ->alignCenter()
                    ->getStateUsing(fn (PedidoModel $record): int => (int) $record->itens->sum('QuantidadeMaterialAtendida')),

                TextColumn::make('qtde_validada')
                    ->label('Qtde Validada')
                    ->alignCenter()
                    ->getStateUsing(fn (PedidoModel $record): int => (int) $record->itens->sum('quantidade_validada')),

                TextColumn::make('situacao.Descricao')
                    ->label('Situação Material')
                    ->default('-')
                    ->badge(),

                TextColumn::make('Observacao')
                    ->label('Observação')
                    ->default('-')
                    ->wrap(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Action::make('itens')
                    ->label('Itens')
                    ->icon('heroicon-o-list-bullet')
                    ->color('gray')
                    ->modalHeading(fn (PedidoModel $record): string => "Itens do pedido {$record->id}")
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn (PedidoModel $record) => view(
                        'egap.filament.pages.partials.pedidos-itens-modal',
                        [
                            'pedido' => $record,
                            'itens' => $record->itens,
                        ],
                    )),
            ]);
    }

}
