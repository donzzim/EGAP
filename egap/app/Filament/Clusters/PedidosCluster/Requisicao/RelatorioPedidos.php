<?php

namespace App\Filament\Egap\Clusters\PedidosCluster\Requisicao;

use App\Filament\Egap\Clusters\PedidosCluster;
use App\Models\Egap\Almoxarifado\Pedidos as PedidoModel;
use App\Models\Egap\Almoxarifado\SituacaoPedido;
use App\Models\Egap\Cadastro\DescricaoResumida;
use App\Models\Egap\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatorioPedidos extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $cluster = PedidosCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Requisição';

    protected static ?string $title = 'Relatório de Pedidos';

    protected static ?string $slug = 'relatorio-pedidos';

    protected static ?string $navigationLabel = 'Relatório de Pedidos';

    protected static string $view = 'egap.filament.pages.pedidos.requisicao.relatorio-pedidos';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PedidoModel::query()
                    ->with([
                        'situacao',
                        'solicitante_get',
                        'responsavel_atendimento',
                        'setor_get',
                        'setorResponsavel',
                        'complementoSetor',
                        'itens.situacaoRef',
                        'itens.materialRel',
                        'itens.descricaoDetalhadaRel',
                        'itens.validadoPor',
                        'itens.canceladoPor',
                        'fases.situacaoRef',
                        'fases.usuarioRef',
                        'fases.descricaoResumidaRef',
                        'fases.descricaoDetalhadaRef',
                    ])
                    ->whereHas('itens')
            )
            ->defaultSort('id', 'desc')
            ->paginated([25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25)
            ->striped()
            ->columns([
                TextColumn::make('id')
                    ->label('Pedido')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date_time')
                    ->label('Data do pedido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('situacao.Descricao')
                    ->label('Situação do pedido')
                    ->default('-')
                    ->badge()
                    ->sortable(),

                TextColumn::make('solicitante_get.name')
                    ->label('Solicitante')
                    ->default('-')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('responsavel_atendimento.name')
                    ->label('Responsável pelo atendimento')
                    ->default('-')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('destino_resumo')
                    ->label('Destino')
                    ->wrap()
                    ->getStateUsing(function (PedidoModel $record): string {
                        $partes = collect([
                            $record->setor_get?->UnidadeOrganizacional,
                            $record->setor_get?->Setor,
                            $record->complementoSetor?->descricao,
                        ])->filter();

                        return $partes->implode(' / ') ?: '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $subQuery) use ($search): void {
                            $subQuery
                                ->whereHas('setor_get', function (Builder $setorQuery) use ($search): void {
                                    $setorQuery
                                        ->where('Setor', 'like', "%{$search}%")
                                        ->orWhere('UnidadeOrganizacional', 'like', "%{$search}%");
                                })
                                ->orWhereHas('complementoSetor', function (Builder $complementoQuery) use ($search): void {
                                    $complementoQuery->where('descricao', 'like', "%{$search}%");
                                });
                        });
                    }),

                TextColumn::make('setorResponsavel.Setor')
                    ->label('Setor responsável')
                    ->default('-')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('materiais_resumo')
                    ->label('Material')
                    ->html()
                    ->wrap()
                    ->getStateUsing(fn (PedidoModel $record): string => $this->formatarMateriaisResumo($record))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('itens', function (Builder $itensQuery) use ($search): void {
                            $itensQuery
                                ->whereHas('materialRel', function (Builder $materialQuery) use ($search): void {
                                    $materialQuery->where('Descricao', 'like', "%{$search}%");
                                })
                                ->orWhereHas('descricaoDetalhadaRel', function (Builder $detalhadaQuery) use ($search): void {
                                    $detalhadaQuery->where('descricao_detalhada', 'like', "%{$search}%");
                                });
                        });
                    }),

                TextColumn::make('fluxo_resumo')
                    ->label('Fluxo')
                    ->html()
                    ->wrap()
                    ->getStateUsing(fn (PedidoModel $record): string => $this->formatarFluxoResumo($record)),

                TextColumn::make('qtde_solicitada')
                    ->label('Solicitada')
                    ->alignCenter()
                    ->badge()
                    ->getStateUsing(fn (PedidoModel $record): int => (int) $record->itens->sum('QuantidadeMaterial'))
                    ->toggleable(),

                TextColumn::make('qtde_validada')
                    ->label('Validada')
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn (PedidoModel $record): int => (int) $record->itens->sum(fn ($item): int => (int) ($item->quantidade_validada ?? 0)))
                    ->toggleable(),

                TextColumn::make('qtde_atendida')
                    ->label('Atendida')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->getStateUsing(fn (PedidoModel $record): int => (int) $record->itens->sum('QuantidadeMaterialAtendida'))
                    ->toggleable(),

                TextColumn::make('qtde_pendente')
                    ->label('Pendente')
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn (PedidoModel $record): int => (int) $record->itens->sum(fn ($item): int => $item->quantidade_pendente))
                    ->toggleable(),

                TextColumn::make('DataTermino')
                    ->label('Previsão / termino')
                    ->date('d/m/Y')
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('ResponsavelAtendimento')
                    ->label('Responsável pelo atendimento')
                    ->options(fn (): array => UserEgap::query()
                        ->whereIn(
                            'id',
                            PedidoModel::query()
                                ->whereNotNull('ResponsavelAtendimento')
                                ->distinct()
                                ->pluck('ResponsavelAtendimento')
                        )
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $builder) => $builder->where('ResponsavelAtendimento', $data['value'])
                        );
                    })
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('Solicitante')
                    ->label('Solicitante')
                    ->options(fn (): array => UserEgap::query()
                        ->whereIn(
                            'id',
                            PedidoModel::query()
                                ->whereNotNull('Solicitante')
                                ->distinct()
                                ->pluck('Solicitante')
                        )
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $builder) => $builder->where('Solicitante', $data['value'])
                        );
                    })
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('idSituacao')
                    ->label('Situação do pedido')
                    ->options(fn (): array => SituacaoPedido::query()
                        ->orderBy('Descricao')
                        ->pluck('Descricao', 'id')
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),

                Filter::make('localizacao')
                    ->label('Localização')
                    ->form([
                        Grid::make(12)
                            ->schema([
                                Select::make('unidade_judiciaria')
                                    ->label('Unidade judiciaria')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->native(false)
                                    ->options(fn (): array => Setores::query()
                                        ->whereColumn('id', 'CodigodaUO')
                                        ->orderBy('UnidadeOrganizacional')
                                        ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                        ->toArray()
                                    )
                                    ->afterStateUpdated(fn (Set $set) => $set('setor', null))
                                    ->columnSpan(7),
                                Select::make('setor')
                                    ->label('Setor')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->disabled(fn (Get $get): bool => blank($get('unidade_judiciaria')))
                                    ->options(fn (Get $get): array => Setores::query()
                                        ->when(
                                            $get('unidade_judiciaria'),
                                            fn (Builder $query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                                        )
                                        ->orderBy('Setor')
                                        ->pluck('Setor', 'id')
                                        ->toArray()
                                    )
                                    ->columnSpan(5),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['unidade_judiciaria'] ?? null),
                                fn (Builder $builder) => $builder->where('UnidadeJudiciaria', $data['unidade_judiciaria'])
                            )
                            ->when(
                                filled($data['setor'] ?? null),
                                fn (Builder $builder) => $builder->where('Setor', $data['setor'])
                            );
                    }),

                Filter::make('mes_referencia')
                    ->label('Mes de referencia')
                    ->form([
                        Select::make('mes')
                            ->label('Mes')
                            ->options($this->getMesesOptions())
                            ->placeholder('Todos')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['mes'] ?? null),
                            fn (Builder $builder) => $builder->whereMonth('date_time', $data['mes'])
                        );
                    }),

                SelectFilter::make('material')
                    ->label('Material')
                    ->options(fn (): array => DescricaoResumida::query()
                        ->where('id_tipo_material', 'P')
                        ->orderBy('Descricao')
                        ->pluck('Descricao', 'id')
                        ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $builder) => $builder->whereHas('itens', function (Builder $itensQuery) use ($data): void {
                                $itensQuery
                                    ->where('material', $data['value'])
                                    ->orWhereHas('descricaoDetalhadaRel', fn (Builder $detalhadaQuery) => $detalhadaQuery->where('descricao_resumida', $data['value']));
                            })
                        );
                    })
                    ->searchable()
                    ->preload()
                    ->native(false),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->headerActions([
                Action::make('gerar_relatorio')
                    ->label('Gerar Relatorio')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn (): StreamedResponse => $this->exportCsv()),
            ])
            ->actions([
                Action::make('detalhes')
                    ->label('Detalhes')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (PedidoModel $record): string => "Relatorio do pedido {$record->id}")
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn (PedidoModel $record) => view(
                        'egap.filament.pages.partials.relatorio-pedidos-modal',
                        [
                            'pedido' => $record,
                            'itens' => $record->itens->sortBy('id')->values(),
                            'fases' => $record->fases->sortByDesc('date_time')->values(),
                        ],
                    )),
            ])
            ->emptyStateHeading('Nenhum pedido encontrado')
            ->emptyStateDescription('Ajuste os filtros para consultar o fluxo completo dos pedidos.');
    }

    protected function formatarMateriaisResumo(PedidoModel $record): string
    {
        $materiais = $this->getMateriaisCollection($record);

        if ($materiais->isEmpty()) {
            return '-';
        }

        $visiveis = $materiais->take(3)->map(fn (string $material): string => e($material));
        $restantes = $materiais->count() - $visiveis->count();

        if ($restantes > 0) {
            $visiveis->push('+' . $restantes . ' material(is)');
        }

        return $visiveis->implode('<br>');
    }

    protected function formatarFluxoResumo(PedidoModel $record): string
    {
        $ultimaFase = $record->fases
            ->sortByDesc(fn ($fase) => $fase->date_time?->getTimestamp() ?? 0)
            ->first();

        $linhas = [
            'Itens: ' . $record->itens->count(),
            'Pendentes: ' . $record->itens->filter(fn ($item): bool => $item->quantidade_pendente > 0)->count(),
            'Ultima fase: ' . ($ultimaFase?->Descricao ?? 'Sem historico registrado'),
        ];

        if ($ultimaFase?->date_time) {
            $linhas[] = 'Atualizado em: ' . $ultimaFase->date_time->format('d/m/Y H:i');
        }

        return collect($linhas)
            ->map(fn (string $linha): string => e($linha))
            ->implode('<br>');
    }

    protected function getMateriaisCollection(PedidoModel $record): Collection
    {
        return $record->itens
            ->map(fn ($item): string => $item->material_nome)
            ->filter()
            ->unique()
            ->values();
    }

    protected function getMesesOptions(): array
    {
        return [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Marco',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $columns = $this->getVisibleColumnsForExport();
        $records = $this->getFilteredSortedTableQuery()->get();
        $filename = 'relatorio_pedidos_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($columns, $records): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv(
                $handle,
                array_map(fn (Column $column): string => $this->getColumnExportLabel($column), $columns),
                ';'
            );

            foreach ($records as $record) {
                $row = [];

                foreach ($columns as $column) {
                    $row[] = $this->getColumnExportState($column, $record);
                }

                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<int, Column>
     */
    protected function getVisibleColumnsForExport(): array
    {
        return array_values(array_filter(
            $this->getTable()->getColumns(),
            fn (Column $column): bool => $column->isVisible() && ! $column->isToggledHidden()
        ));
    }

    protected function getColumnExportLabel(Column $column): string
    {
        $label = $column->getLabel();

        if ($label instanceof Htmlable) {
            $label = $label->toHtml();
        }

        return html_entity_decode(trim(strip_tags((string) $label)));
    }

    protected function getColumnExportState(Column $column, PedidoModel $record): string
    {
        $state = $column
            ->record($record)
            ->formatState($column->record($record)->getState());

        return $this->normalizeExportValue($state);
    }

    protected function normalizeExportValue(mixed $value): string
    {
        if ($value instanceof Htmlable) {
            $value = $value->toHtml();
        }

        if (is_array($value)) {
            $value = implode(' | ', array_map(
                fn (mixed $item): string => $this->normalizeExportValue($item),
                $value
            ));
        }

        if ($value === null) {
            return '';
        }

        $value = (string) $value;
        $value = preg_replace('/<br\s*\/?>/i', ' | ', $value) ?? $value;
        $value = html_entity_decode(strip_tags($value));
        $value = preg_replace("/\r\n|\r|\n/", ' ', $value) ?? $value;

        return trim($value);
    }
}
