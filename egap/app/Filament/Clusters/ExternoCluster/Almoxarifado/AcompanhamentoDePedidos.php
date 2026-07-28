<?php

namespace App\Filament\Clusters\ExternoCluster\Almoxarifado;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Concerns\ResolveUsuarioExterno;
use App\Models\Almoxarifado\Pedidos;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Consulta, no Ambiente Externo, os pedidos de materiais de consumo/duráveis
 * (setor_responsavel = Almoxarifado) enviados pelo setor do usuário logado.
 * Equivalente moderno do legado consultar_pedidos.php?setor=799.
 */
class AcompanhamentoDePedidos extends Page implements HasTable
{
    use InteractsWithTable;
    use ResolveUsuarioExterno;

    protected const STATUS_ATENDIDO = 3;

    protected const STATUS_CANCELADO = 4;

    protected const STATUS_EM_ANALISE = 6;

    protected const STATUS_VALIDADO = 7;

    protected const SETOR_ALMOXARIFADO = 799;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $cluster = ExternoCluster::class;

    protected static ?string $navigationGroup = 'Almoxarifado';

    protected static ?string $navigationLabel = 'Acompanhamento de Pedidos';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Acompanhamento de Pedidos';

    protected static string $view = 'filament.pages.externo.almoxarifado.acompanhamento-de-pedidos';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(): void
    {
        if (! $this->lotacaoAtual()) {
            Notification::make()
                ->title('Lotação não encontrada')
                ->body('Não foi possível identificar o setor do seu usuário. Contate o suporte.')
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getPedidosQuery())
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Nº Pedido')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date_time')
                    ->label('Data do Pedido')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('solicitante_get.name')
                    ->label('Solicitante')
                    ->default('-')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('complemento_setor_descricao')
                    ->label('Complemento do Setor')
                    ->getStateUsing(fn (Pedidos $record): string => $record->complementoSetor?->descricao ?? '-')
                    ->wrap(),

                TextColumn::make('materiais_resumo')
                    ->label('Materiais')
                    ->wrap()
                    ->html()
                    ->getStateUsing(fn (Pedidos $record): string => $record->itens
                        ->map(fn ($item) => $item->material_nome)
                        ->filter()
                        ->unique()
                        ->take(3)
                        ->implode('<br>')
                    ?: '-'),

                TextColumn::make('Observacao')
                    ->label('Justificativa da Necessidade')
                    ->default('-')
                    ->wrap(),

                TextColumn::make('situacao.Descricao')
                    ->label('Status')
                    ->default('-')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status_grupo')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendentes',
                        'atendido' => 'Atendidos',
                        'cancelado' => 'Cancelados',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'pendente' => $query->where('idSituacao', self::STATUS_EM_ANALISE),
                            'atendido' => $query->whereIn('idSituacao', [self::STATUS_ATENDIDO, self::STATUS_VALIDADO]),
                            'cancelado' => $query->where('idSituacao', self::STATUS_CANCELADO),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Action::make('itens')
                    ->label('Itens')
                    ->icon('heroicon-o-list-bullet')
                    ->color('gray')
                    ->modalHeading(fn (Pedidos $record): string => "Itens do pedido {$record->id}")
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn (Pedidos $record) => view(
                        'filament.pages.partials.pedidos-itens-modal',
                        [
                            'pedido' => $record,
                            'itens' => $record->itens,
                        ],
                    )),
            ])
            ->emptyStateHeading('Nenhum pedido encontrado');
    }

    protected function getPedidosQuery(): Builder
    {
        $lotacao = $this->lotacaoAtual();

        $query = Pedidos::query()
            ->with([
                'solicitante_get',
                'complementoSetor',
                'situacao',
                'itens.materialRel',
                'itens.descricaoDetalhadaRel',
            ])
            ->where('setor_responsavel', self::SETOR_ALMOXARIFADO);

        if (! $lotacao) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('Setor', $lotacao->setor);
    }
}
