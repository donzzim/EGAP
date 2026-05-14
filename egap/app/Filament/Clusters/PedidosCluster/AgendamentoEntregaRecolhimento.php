<?php

namespace App\Filament\Egap\Clusters\PedidosCluster;

use App\Filament\Egap\Clusters\PedidosCluster;
use App\Models\Egap\Agendamento\Materiais;
use App\Models\Egap\Agendamento\Regiao;
use App\Models\Egap\Agendamento\Solicitacao;
use App\Models\Egap\Almoxarifado\Pedidos;
use App\Models\Egap\Almoxarifado\SituacaoPedido;
use App\Models\Egap\Cadastro\Setores;
use App\Models\Egap\Patrimonio\BensMoveis\Termo;
use App\Models\Egap\Patrimonio\BensMoveis\TransferenciaBemMovel;
use App\Models\UserEgap;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AgendamentoEntregaRecolhimento extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $cluster = PedidosCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    private const SETOR_PATRIMONIO_ID = 1239;
    private const TIPO_TRANSPORTE_CARGA = '2';
    private const SITUACAO_EM_ANALISE = 6;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'egap.filament.pages.pedidos.agendamento-entrega-recolhimento';

    protected static ?string $navigationLabel = 'Agendamento da Entrega/Recolhimento';

    protected static ?string $title = 'Agendamento da Entrega/Recolhimento';


    protected static ?string $slug = 'agendamento-entrega-recolhimento';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('tipo_fluxo')
                    ->label('Entrega/Recolhimento')
                    ->state(fn (Termo $record): string => $this->inferFluxo($record))
                    ->badge()
                    ->color(fn (Termo $record): string => $this->inferFluxo($record) === 'Entrega' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('setor_anterior')
                    ->label('Setor anterior')
                    ->state(fn (Termo $record): string => $this->getSetorAnterior($record))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $this->applySetorAnteriorSearch($query, $search))
                    ->wrap(),
                Tables\Columns\TextColumn::make('complemento_anterior')
                    ->label('Complemento Anterior')
                    ->state(fn (Termo $record): ?string => $this->getComplementoAnterior($record))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $this->applyComplementoAnteriorSearch($query, $search))
                    ->toggleable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('setor_atual')
                    ->label('Setor Atual')
                    ->state(fn (Termo $record): string => $this->getSetorAtual($record))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $this->applySetorAtualSearch($query, $search))
                    ->wrap(),
                Tables\Columns\TextColumn::make('complemento_atual')
                    ->label('Complemento Atual')
                    ->state(fn (Termo $record): ?string => $this->getComplementoAtual($record))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $this->applyComplementoAtualSearch($query, $search))
                    ->toggleable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('transferencia_atualizada_em')
                    ->label('Atualizado em')
                    ->state(fn (Termo $record): mixed => $this->getTransferenciaAtualizadaEm($record))
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('transferencia_atualizada_por')
                    ->label('Atualizado por')
                    ->state(fn (Termo $record): ?string => $this->getTransferenciaAtualizadaPor($record))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $this->applyTransferenciaAtualizadaPorSearch($query, $search))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('termo_numero')
                    ->label('Termo')
                    ->state(fn (Termo $record): string => "{$record->num_termo}/{$record->ano_termo}")
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy($this->qualifyTermoColumn('ano_termo'), $direction)
                        ->orderBy($this->qualifyTermoColumn('num_termo'), $direction))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where(function (Builder $builder) use ($search): void {
                            $builder
                                ->where($this->qualifyTermoColumn('num_termo'), 'like', "%{$search}%")
                                ->orWhere($this->qualifyTermoColumn('ano_termo'), 'like', "%{$search}%");
                        })),
                Tables\Columns\TextColumn::make('situacao_termo')
                    ->label('Situacao Termo')
                    ->state(fn (Termo $record): string => $this->getArquivoSituacao($record) === 0 ? 'Pendente' : 'Invalidado')
                    ->badge()
                    ->color(fn (Termo $record): string => $this->getArquivoSituacao($record) === 0 ? 'warning' : 'danger'),
                Tables\Columns\TextColumn::make('arquivo_observacao')
                    ->label('Observacao')
                    ->state(fn (Termo $record): ?string => $this->getArquivoObservacao($record))
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => filled($state) ? $state : null)
                    ->wrap()
                    ->toggleable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('pedido_no')
                    ->label('Pedido')
                    ->state(fn (Termo $record): mixed => $this->getPedidoNo($record))
                    ->alignCenter()
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy($this->getTransferenciaPedidoNoSubquery(), $direction)
                        ->orderBy($this->qualifyTermoColumn('pedido_no'), $direction))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $this->applyPedidoSearch($query, $search)),
                Tables\Columns\TextColumn::make('id_solicitacao')
                    ->label('Id Solicitacao')
                    ->state(fn (Termo $record): ?int => $this->getSolicitacaoId($record))
                    ->alignCenter()
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy($this->getSolicitacaoIdSubquery(), $direction))
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('situacao_solicitacao')
                    ->label('Situacao da Solicitacao')
                    ->state(fn (Termo $record): ?string => $this->getSituacaoSolicitacaoDescricao($record))
                    ->badge()
                    ->color(fn (Termo $record): string => $this->getSolicitacaoColor($this->getSituacaoSolicitacaoId($record) ?? 0))
                    ->formatStateUsing(fn (?string $state): string => $state ?: '-')
                    ->description(fn (Termo $record): ?string => filled($this->getSolicitacaoId($record))
                        ? $this->limitText($this->extractJustificativaText($this->getSolicitacao($record)?->justificativa), 90)
                        : null)
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('arquivo_situacao')
                    ->label('Situação do termo')
                    ->options([
                        '0' => 'Pendente',
                        '2' => 'Invalidado',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['value'] ?? null),
                            fn (Builder $builder): Builder => $builder->whereHas('arquivoDigital', function (Builder $arquivoQuery) use ($data): void {
                                $arquivoQuery->where('situacao', $data['value']);
                            })
                        )),
                Tables\Filters\SelectFilter::make('tipo_fluxo')
                    ->label('Fluxo')
                    ->options([
                        'Entrega' => 'Entrega',
                        'Recolhimento' => 'Recolhimento',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['value'] ?? null),
                            fn (Builder $builder): Builder => $this->applyFluxoFilter($builder, $data['value'])
                        )),
                Tables\Filters\SelectFilter::make('situacao_solicitacao_id')
                    ->label('Situação da solicitação')
                    ->options($this->getSituacoesAgendamentoOptions())
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['value'] ?? null),
                            fn (Builder $builder): Builder => $builder->whereHas('ultimoMaterialTransporte.idSolicitacaoRef', function (Builder $solicitacaoQuery) use ($data): void {
                                $solicitacaoQuery->where('id_situacao', $data['value']);
                            })
                        )),
            ])
            ->filtersFormColumns(3)
            ->headerActions([
                Action::make('exportar_csv')
                    ->label('Exportar CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn (): StreamedResponse => $this->exportCsv()),
            ])
            ->actions([
                Action::make('materiais')
                    ->label('Materiais')
                    ->icon('heroicon-o-list-bullet')
                    ->color('gray')
                    ->visible(fn (Termo $record): bool => filled($record->ultimaTransferencia?->id))
                    ->modalHeading(fn (Termo $record): string => "Materiais do termo {$record->num_termo}/{$record->ano_termo}")
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn (Termo $record) => view(
                        'egap.filament.pages.partials.agendamento-entrega-recolhimento-materiais',
                        [
                            'record' => $record,
                            'materiais' => $this->getMateriaisDoTermo($record),
                            'pedidoNo' => $this->getPedidoNo($record),
                            'fluxo' => $this->inferFluxo($record),
                        ],
                    )),
                Action::make('encaminhar')
                    ->label(fn (Termo $record): string => filled($this->getSolicitacaoId($record)) ? 'Editar agendamento' : 'Encaminhar')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->modalWidth(MaxWidth::FiveExtraLarge)
                    ->modalHeading(fn (Termo $record): string => filled($this->getSolicitacaoId($record))
                        ? "Editar agendamento do termo {$record->num_termo}/{$record->ano_termo}"
                        : "Encaminhar termo {$record->num_termo}/{$record->ano_termo}")
                    ->fillForm(fn (Termo $record): array => $this->getAgendamentoFormData(collect([$record])))
                    ->form($this->getAgendamentoFormSchema())
                    ->action(function (Termo $record, array $data): void {
                        $this->salvarAgendamento(collect([$record]), $data);
                    }),
            ])
            ->bulkActions([
                BulkAction::make('encaminharSelecionados')
                    ->label('Encaminhar selecionados')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->modalWidth(MaxWidth::FiveExtraLarge)
                    ->modalHeading('Encaminhar termos selecionados')
                    ->fillForm(fn (Collection $records): array => $this->getAgendamentoFormData($records))
                    ->form($this->getAgendamentoFormSchema())
                    ->action(function (Collection $records, array $data): void {
                        $this->salvarAgendamento($records, $data, true);
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateHeading('Nenhum termo disponivel para agendamento.')
            ->emptyStateDescription('Os termos pendentes ou invalidados vinculados a Patrimonio aparecerao aqui.')
            ->defaultSort('id', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped();
    }

    protected function getTableQuery(): Builder
    {
        return Termo::query()
            ->with([
                'arquivoDigital',
                'responsavelRef',
                'pedidoRef.setor_get',
                'pedidoRef.complementoSetor',
                'ultimaTransferencia.setorAnteriorRel',
                'ultimaTransferencia.complementoAnteriorRel',
                'ultimaTransferencia.setorAtualRel',
                'ultimaTransferencia.complementoAtualRel',
                'ultimaTransferencia.usuarioRef',
                'ultimoMaterialTransporte.idSolicitacaoRef.idSituacaoRef',
                'ultimoMaterialTransporte.idSolicitacaoRef.idUserRef',
                'ultimoMaterialTransporte.idSolicitacaoRef.setorSolicitanteRef',
            ])
            ->whereHas('arquivoDigital', function (Builder $arquivoQuery): void {
                $arquivoQuery->whereIn('situacao', [0, 2]);
            })
            ->whereHas('ultimoMaterialTransporte.idSolicitacaoRef', function (Builder $solicitacaoQuery): void {
                $solicitacaoQuery->where('tipo', self::TIPO_TRANSPORTE_CARGA);
            });
    }

    protected function getAgendamentoFormSchema(): array
    {
        return [
            Forms\Components\Grid::make([
                'default' => 1,
                'lg' => 3,
            ])
                ->schema([
                    Forms\Components\Select::make('id_solicitante')
                        ->label('Solicitante')
                        ->searchable()
                        ->native(false)
                        ->required()
                        ->getSearchResultsUsing(fn (string $search): array => UserEgap::query()
                            ->where('name', 'like', "%{$search}%")
                            ->orderBy('name')
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray())
                        ->getOptionLabelUsing(fn ($value): ?string => filled($value)
                            ? UserEgap::query()->whereKey($value)->value('name')
                            : null),
                    Forms\Components\Select::make('id_situacao')
                        ->label('Situacao')
                        ->native(false)
                        ->required()
                        ->options($this->getSituacoesAgendamentoOptions()),
                    Forms\Components\Select::make('regiao')
                        ->label('Regiao')
                        ->native(false)
                        ->searchable()
                        ->required()
                        ->options($this->getRegioesOptions()),
                    Forms\Components\Select::make('unidade_solicitante')
                        ->label('Unidade solicitante')
                        ->native(false)
                        ->searchable()
                        ->live()
                        ->required()
                        ->options($this->getUnidadesSolicitantesOptions())
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            $set('setor_solicitante', null);
                            $set(
                                'regiao',
                                filled($state)
                                    ? Regiao::query()->where('unidade', $state)->value('id')
                                    : null,
                            );
                        }),
                    Forms\Components\Select::make('setor_solicitante')
                        ->label('Setor solicitante')
                        ->native(false)
                        ->searchable()
                        ->required()
                        ->disabled(fn (Get $get): bool => blank($get('unidade_solicitante')))
                        ->options(fn (Get $get): array => $this->getSetoresDaUnidadeOptions($get('unidade_solicitante'))),
                    Forms\Components\TextInput::make('local_saida')
                        ->label('Local de saida')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('local_destino')
                        ->label('Local de destino')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan([
                            'default' => 1,
                            'lg' => 2,
                        ]),
                    Forms\Components\DatePicker::make('data_inicio')
                        ->label('Data inicio')
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    Forms\Components\TimePicker::make('hora_inicio')
                        ->label('Hora inicio')
                        ->seconds(false),
                    Forms\Components\DatePicker::make('data_termino')
                        ->label('Data termino')
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    Forms\Components\TimePicker::make('hora_termino')
                        ->label('Hora termino')
                        ->seconds(false),
                ]),
            Forms\Components\Textarea::make('justificativa_texto')
                ->label('Justificativa')
                ->required()
                ->rows(5)
                ->autosize()
                ->columnSpanFull(),
        ];
    }

    protected function getAgendamentoFormData(Collection $records): array
    {
        $records = $records->values();
        $record = $records->first();

        if (! $record instanceof Termo) {
            return [];
        }

        $solicitacao = $this->getSolicitacao($record);

        if ($records->count() === 1 && filled($solicitacao?->id)) {
            return [
                'id_solicitante' => $solicitacao->id_solicitante ?: auth()->id(),
                'id_situacao' => $solicitacao->id_situacao ?: self::SITUACAO_EM_ANALISE,
                'regiao' => $solicitacao->regiao ?: $this->inferRegiaoId($record),
                'unidade_solicitante' => $solicitacao->unidade_solicitante ?: $this->inferUnidadeSolicitanteId($record),
                'setor_solicitante' => $solicitacao->setor_solicitante ?: $this->inferSetorSolicitanteId($record),
                'local_saida' => $solicitacao->local_saida ?: $this->inferLocalSaida($record),
                'local_destino' => $solicitacao->local_destino ?: $this->inferLocalDestino($record),
                'data_inicio' => $solicitacao->data_inicio,
                'hora_inicio' => $solicitacao->hora_inicio,
                'data_termino' => $solicitacao->data_termino,
                'hora_termino' => $solicitacao->hora_termino,
                'justificativa_texto' => $this->extractJustificativaText($solicitacao->justificativa)
                    ?: $this->buildDefaultJustificativa($records),
            ];
        }

        return [
            'id_solicitante' => auth()->id(),
            'id_situacao' => self::SITUACAO_EM_ANALISE,
            'regiao' => $this->resolveUniqueValue(
                $records->map(fn (Termo $item): ?int => $this->inferRegiaoId($item))
            ),
            'unidade_solicitante' => $this->resolveUniqueValue(
                $records->map(fn (Termo $item): ?int => $this->inferUnidadeSolicitanteId($item))
            ),
            'setor_solicitante' => $this->resolveUniqueValue(
                $records->map(fn (Termo $item): ?int => $this->inferSetorSolicitanteId($item))
            ),
            'local_saida' => $this->resolveUniqueValue(
                $records->map(fn (Termo $item): string => $this->inferLocalSaida($item))
            ),
            'local_destino' => $this->resolveUniqueValue(
                $records->map(fn (Termo $item): string => $this->inferLocalDestino($item))
            ),
            'data_inicio' => null,
            'hora_inicio' => null,
            'data_termino' => null,
            'hora_termino' => null,
            'justificativa_texto' => $this->buildDefaultJustificativa($records),
        ];
    }

    protected function salvarAgendamento(Collection $records, array $data, bool $isBulk = false): void
    {
        $records = $records->filter(fn ($record): bool => $record instanceof Termo)->values();

        if ($records->isEmpty()) {
            Notification::make()
                ->title('Nenhum termo selecionado.')
                ->warning()
                ->send();

            return;
        }

        if ($isBulk && $records->contains(fn (Termo $record): bool => filled($this->getSolicitacaoId($record)))) {
            Notification::make()
                ->title('Ha termos ja vinculados a uma solicitacao.')
                ->body('Use a acao individual para editar termos que ja possuem agendamento.')
                ->danger()
                ->send();

            return;
        }

        try {
            $resultado = DB::connection('egap')->transaction(function () use ($records, $data, $isBulk): array {
                $usuarioId = auth()->id();

                if (! $usuarioId) {
                    throw new \RuntimeException('Usuario autenticado nao encontrado.');
                }

                $record = $records->first();
                $solicitacao = null;

                if (! $isBulk && $records->count() === 1 && $record instanceof Termo && filled($this->getSolicitacaoId($record))) {
                    $solicitacao = Solicitacao::query()->find($this->getSolicitacaoId($record));
                }

                $payload = [
                    'tipo' => self::TIPO_TRANSPORTE_CARGA,
                    'id_situacao' => (int) $data['id_situacao'],
                    'id_solicitante' => (int) $data['id_solicitante'],
                    'setor_solicitante' => (int) $data['setor_solicitante'],
                    'unidade_solicitante' => (int) $data['unidade_solicitante'],
                    'regiao' => (int) $data['regiao'],
                    'data_inicio' => $data['data_inicio'] ?: null,
                    'hora_inicio' => $data['hora_inicio'] ?: null,
                    'data_termino' => $data['data_termino'] ?: null,
                    'hora_termino' => $data['hora_termino'] ?: null,
                    'local_saida' => trim((string) $data['local_saida']),
                    'local_destino' => trim((string) $data['local_destino']),
                    'justificativa' => [
                        'justificativa' => trim((string) $data['justificativa_texto']),
                    ],
                ];

                if ($solicitacao) {
                    $solicitacao->fill($payload);
                    $solicitacao->id_user = $usuarioId;
                    $solicitacao->data_alteracao = now();
                    $solicitacao->save();
                } else {
                    $solicitacao = Solicitacao::query()->create([
                        ...$payload,
                        'date_time' => now(),
                        'id_user' => $usuarioId,
                    ]);
                }

                foreach ($records as $termo) {
                    $material = Materiais::query()
                        ->where('id_termo', $termo->id)
                        ->latest('id')
                        ->first();

                    if ($material) {
                        $material->fill([
                            'id_solicitacao' => $solicitacao->id,
                            'id_pedido' => $this->getPedidoNo($termo),
                        ]);
                        $material->id_user = $usuarioId;
                        $material->save();
                    } else {
                        Materiais::query()->create([
                            'id_user' => $usuarioId,
                            'id_pedido' => $this->getPedidoNo($termo),
                            'id_termo' => $termo->id,
                            'id_solicitacao' => $solicitacao->id,
                        ]);
                    }
                }

                return [
                    'solicitacao_id' => $solicitacao->id,
                    'total_termos' => $records->count(),
                    'modo' => $solicitacao->wasRecentlyCreated ? 'criada' : 'atualizada',
                ];
            });

            Notification::make()
                ->title("Solicitacao {$resultado['modo']} com sucesso.")
                ->body("Solicitacao #{$resultado['solicitacao_id']} vinculada a {$resultado['total_termos']} termo(s).")
                ->success()
                ->send();

            $this->resetTable();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Erro ao salvar o agendamento.')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getMateriaisDoTermo(Termo $record): Collection
    {
        return TransferenciaBemMovel::query()
            ->with([
                'bem.marcaRel',
                'bem.modeloRef',
            ])
            ->whereBelongsTo($record, 'termoRel')
            ->get()
            ->sortBy(fn (TransferenciaBemMovel $transferencia): mixed => $transferencia->bem?->NumPatrimonio)
            ->map(function (TransferenciaBemMovel $transferencia): object {
                $bem = $transferencia->bem;

                return (object) [
                    'patrimonio_id' => $bem?->id,
                    'numero_patrimonio' => $bem?->NumPatrimonio,
                    'descricao' => $bem?->Descricao,
                    'marca' => $bem?->marcaRel?->Descricao,
                    'modelo' => $bem?->modeloRef?->descricao,
                    'atualizado_em' => $transferencia->date_time,
                ];
            })
            ->values();
    }

    protected function getSituacoesAgendamentoOptions(): array
    {
        return SituacaoPedido::query()
            ->orderBy('Descricao')
            ->pluck('Descricao', 'id')
            ->toArray();
    }

    protected function getRegioesOptions(): array
    {
        return Regiao::query()
            ->orderBy('sigla')
            ->get()
            ->mapWithKeys(fn (Regiao $regiao): array => [
                $regiao->id => "{$regiao->sigla} - Regiao {$regiao->regiao}",
            ])
            ->toArray();
    }

    protected function getUnidadesSolicitantesOptions(): array
    {
        return Setores::query()
            ->whereColumn('id', 'CodigodaUO')
            ->orderBy('UnidadeOrganizacional')
            ->pluck('UnidadeOrganizacional', 'id')
            ->toArray();
    }

    protected function getSetoresDaUnidadeOptions(null|int|string $unidadeId): array
    {
        if (blank($unidadeId)) {
            return [];
        }

        return Setores::query()
            ->where('CodigoPai', $unidadeId)
            ->orderBy('Setor')
            ->pluck('Setor', 'id')
            ->toArray();
    }

    protected function getArquivoSituacao(Termo $record): int
    {
        return (int) ($record->arquivoDigital?->situacao ?? 0);
    }

    protected function getArquivoObservacao(Termo $record): ?string
    {
        return $record->arquivoDigital?->observacao;
    }

    protected function getSolicitacao(Termo $record): ?Solicitacao
    {
        return $record->ultimoMaterialTransporte?->idSolicitacaoRef;
    }

    protected function getPedido(Termo $record): ?Pedidos
    {
        return $record->pedidoRef;
    }

    protected function getSolicitacaoId(Termo $record): ?int
    {
        return $this->getSolicitacao($record)?->id;
    }

    protected function getSituacaoSolicitacaoId(Termo $record): ?int
    {
        return $this->getSolicitacao($record)?->id_situacao;
    }

    protected function getSituacaoSolicitacaoDescricao(Termo $record): ?string
    {
        return $this->getSolicitacao($record)?->idSituacaoRef?->Descricao;
    }

    protected function getSetorAnterior(Termo $record): string
    {
        return (string) ($record->ultimaTransferencia?->setorAnteriorRel?->Setor ?: $this->getSolicitacao($record)?->local_saida ?: '');
    }

    protected function getSetorAnteriorId(Termo $record): ?int
    {
        return filled($record->ultimaTransferencia?->SetorAnterior)
            ? (int) $record->ultimaTransferencia->SetorAnterior
            : null;
    }

    protected function getComplementoAnterior(Termo $record): ?string
    {
        return $record->ultimaTransferencia?->complementoAnteriorRel?->descricao;
    }

    protected function getSetorAtual(Termo $record): string
    {
        $solicitacao = $this->getSolicitacao($record);
        $pedido = $this->getPedido($record);

        return (string) (
            $record->ultimaTransferencia?->setorAtualRel?->Setor
            ?: $solicitacao?->setorSolicitanteRef?->Setor
            ?: $pedido?->setor_get?->Setor
            ?: $solicitacao?->local_destino
            ?: ''
        );
    }

    protected function getSetorAtualId(Termo $record): ?int
    {
        $solicitacao = $this->getSolicitacao($record);
        $pedido = $this->getPedido($record);

        foreach ([
            $record->ultimaTransferencia?->SetorAtual,
            $solicitacao?->setor_solicitante,
            $pedido?->Setor,
        ] as $setorId) {
            if (filled($setorId)) {
                return (int) $setorId;
            }
        }

        return null;
    }

    protected function getComplementoAtual(Termo $record): ?string
    {
        return $record->ultimaTransferencia?->complementoAtualRel?->descricao
            ?: $this->getPedido($record)?->complementoSetor?->descricao;
    }

    protected function getTransferenciaAtualizadaEm(Termo $record): mixed
    {
        $solicitacao = $this->getSolicitacao($record);

        foreach ([
            $record->ultimaTransferencia?->date_time,
            $solicitacao?->data_alteracao,
            $solicitacao?->date_time,
            $record->atualizado_em,
            $record->date_time,
        ] as $value) {
            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    protected function getTransferenciaAtualizadaPor(Termo $record): ?string
    {
        $solicitacao = $this->getSolicitacao($record);

        foreach ([
            $record->ultimaTransferencia?->usuarioRef?->name,
            $solicitacao?->idUserRef?->name,
            $record->responsavelRef?->name,
        ] as $value) {
            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    protected function getPedidoNo(Termo $record): mixed
    {
        return $record->ultimaTransferencia?->pedido_no ?: $record->pedido_no;
    }

    protected function inferFluxo(Termo $record): string
    {
        if (
            $this->getSetorAnteriorId($record) === self::SETOR_PATRIMONIO_ID
            || str_contains($this->normalizeText($this->getSetorAnterior($record)), 'patrim')
        ) {
            return 'Entrega';
        }

        if (
            (int) ($record->ultimaTransferencia?->SetorAtual ?? 0) === self::SETOR_PATRIMONIO_ID
            || (int) ($this->getSolicitacao($record)?->setor_solicitante ?? 0) === self::SETOR_PATRIMONIO_ID
            || (int) ($this->getPedido($record)?->Setor ?? 0) === self::SETOR_PATRIMONIO_ID
            || str_contains($this->normalizeText($this->getSetorAtual($record)), 'patrim')
        ) {
            return 'Recolhimento';
        }

        return 'Entrega';
    }

    protected function inferSetorSolicitanteId(Termo $record): ?int
    {
        $setorId = $this->inferFluxo($record) === 'Entrega'
            ? $this->getSetorAtualId($record)
            : $this->getSetorAnteriorId($record);

        return filled($setorId) ? (int) $setorId : null;
    }

    protected function inferUnidadeSolicitanteId(Termo $record): ?int
    {
        $setorId = $this->inferSetorSolicitanteId($record);

        if (! $setorId) {
            return null;
        }

        return Setores::query()
            ->whereKey($setorId)
            ->value('CodigodaUO');
    }

    protected function inferRegiaoId(Termo $record): ?int
    {
        $unidadeId = $this->inferUnidadeSolicitanteId($record);

        if (! $unidadeId) {
            return null;
        }

        return Regiao::query()
            ->where('unidade', $unidadeId)
            ->value('id');
    }

    protected function inferLocalSaida(Termo $record): string
    {
        $setorAnterior = $this->getSetorAnterior($record);

        if (filled($setorAnterior)) {
            return $setorAnterior;
        }

        return $this->inferFluxo($record) === 'Entrega'
            ? 'Secao de Patrimonio'
            : 'Nao informado';
    }

    protected function inferLocalDestino(Termo $record): string
    {
        $setorAtual = $this->getSetorAtual($record);

        if (filled($setorAtual)) {
            return $setorAtual;
        }

        return $this->inferFluxo($record) === 'Recolhimento'
            ? 'Secao de Patrimonio'
            : 'Nao informado';
    }

    protected function buildDefaultJustificativa(Collection $records): string
    {
        if ($records->count() === 1) {
            $record = $records->first();

            if ($record instanceof Termo && $this->inferFluxo($record) === 'Recolhimento') {
                return 'Solicito o recolhimento dos materiais permanentes conforme Termo de Responsabilidade.';
            }
        }

        return 'Solicito o agendamento de entrega/recolhimento dos materiais permanentes conforme Termo de Responsabilidade.';
    }

    protected function extractJustificativaText(mixed $value): string
    {
        if (blank($value)) {
            return '';
        }

        $decoded = $value;

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return trim($value);
            }
        }

        if (is_array($decoded)) {
            return trim((string) ($decoded['justificativa'] ?? ''));
        }

        return '';
    }

    protected function resolveUniqueValue(Collection $values): mixed
    {
        $values = $values
            ->filter(fn ($value): bool => filled($value))
            ->unique()
            ->values();

        return $values->count() === 1 ? $values->first() : null;
    }

    protected function getSolicitacaoColor(int $statusId): string
    {
        return match ($statusId) {
            3 => 'success',
            6 => 'warning',
            8 => 'info',
            4, 5 => 'danger',
            default => 'gray',
        };
    }

    protected function limitText(?string $text, int $limit): ?string
    {
        if (blank($text)) {
            return null;
        }

        return mb_strlen($text) > $limit
            ? mb_substr($text, 0, $limit) . '...'
            : $text;
    }

    protected function normalizeText(?string $text): string
    {
        return (string) Str::of($text ?? '')
            ->ascii()
            ->lower()
            ->trim();
    }

    protected function applySetorAnteriorSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->whereHas('ultimaTransferencia.setorAnteriorRel', function (Builder $setorQuery) use ($search): void {
                    $setorQuery->where('Setor', 'like', "%{$search}%");
                })
                ->orWhere(function (Builder $fallbackQuery) use ($search): void {
                    $fallbackQuery
                        ->whereDoesntHave('ultimaTransferencia.setorAnteriorRel')
                        ->whereHas('ultimoMaterialTransporte.idSolicitacaoRef', function (Builder $solicitacaoQuery) use ($search): void {
                            $solicitacaoQuery->where('local_saida', 'like', "%{$search}%");
                        });
                });
        });
    }

    protected function applyComplementoAnteriorSearch(Builder $query, string $search): Builder
    {
        return $query->whereHas('ultimaTransferencia.complementoAnteriorRel', function (Builder $complementoQuery) use ($search): void {
            $complementoQuery->where('descricao', 'like', "%{$search}%");
        });
    }

    protected function applySetorAtualSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->whereHas('ultimaTransferencia.setorAtualRel', function (Builder $setorQuery) use ($search): void {
                    $setorQuery->where('Setor', 'like', "%{$search}%");
                })
                ->orWhereHas('ultimoMaterialTransporte.idSolicitacaoRef.setorSolicitanteRef', function (Builder $setorQuery) use ($search): void {
                    $setorQuery->where('Setor', 'like', "%{$search}%");
                })
                ->orWhereHas('pedidoRef.setor_get', function (Builder $setorQuery) use ($search): void {
                    $setorQuery->where('Setor', 'like', "%{$search}%");
                })
                ->orWhereHas('ultimoMaterialTransporte.idSolicitacaoRef', function (Builder $solicitacaoQuery) use ($search): void {
                    $solicitacaoQuery->where('local_destino', 'like', "%{$search}%");
                });
        });
    }

    protected function applyComplementoAtualSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->whereHas('ultimaTransferencia.complementoAtualRel', function (Builder $complementoQuery) use ($search): void {
                    $complementoQuery->where('descricao', 'like', "%{$search}%");
                })
                ->orWhereHas('pedidoRef.complementoSetor', function (Builder $complementoQuery) use ($search): void {
                    $complementoQuery->where('descricao', 'like', "%{$search}%");
                });
        });
    }

    protected function applyTransferenciaAtualizadaPorSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->whereHas('ultimaTransferencia.usuarioRef', function (Builder $usuarioQuery) use ($search): void {
                    $usuarioQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('ultimoMaterialTransporte.idSolicitacaoRef.idUserRef', function (Builder $usuarioQuery) use ($search): void {
                    $usuarioQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('responsavelRef', function (Builder $usuarioQuery) use ($search): void {
                    $usuarioQuery->where('name', 'like', "%{$search}%");
                });
        });
    }

    protected function applyPedidoSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where($this->qualifyTermoColumn('pedido_no'), 'like', "%{$search}%")
                ->orWhereHas('ultimaTransferencia', function (Builder $transferenciaQuery) use ($search): void {
                    $transferenciaQuery->where('pedido_no', 'like', "%{$search}%");
                });
        });
    }

    protected function applyFluxoFilter(Builder $query, string $fluxo): Builder
    {
        if ($fluxo === 'Recolhimento') {
            return $query->where(fn (Builder $builder): Builder => $this->whereRecolhimentoRecord($builder));
        }

        return $query->whereNot(fn (Builder $builder): Builder => $this->whereRecolhimentoRecord($builder));
    }

    protected function whereRecolhimentoRecord(Builder $query): Builder
    {
        return $query
            ->where(fn (Builder $builder): Builder => $this->whereDoesNotMatchEntrega($builder))
            ->where(fn (Builder $builder): Builder => $this->whereMatchesRecolhimento($builder));
    }

    protected function whereDoesNotMatchEntrega(Builder $query): Builder
    {
        return $query
            ->whereDoesntHave('ultimaTransferencia', function (Builder $transferenciaQuery): void {
                $transferenciaQuery->where('SetorAnterior', self::SETOR_PATRIMONIO_ID);
            })
            ->whereDoesntHave('ultimaTransferencia.setorAnteriorRel', function (Builder $setorQuery): void {
                $setorQuery->where('Setor', 'like', '%patrim%');
            })
            ->where(function (Builder $builder): void {
                $builder
                    ->whereHas('ultimaTransferencia.setorAnteriorRel')
                    ->orWhereDoesntHave('ultimoMaterialTransporte.idSolicitacaoRef', function (Builder $solicitacaoQuery): void {
                        $solicitacaoQuery->where('local_saida', 'like', '%patrim%');
                    });
            });
    }

    protected function whereMatchesRecolhimento(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder
                ->whereHas('ultimaTransferencia', function (Builder $transferenciaQuery): void {
                    $transferenciaQuery->where('SetorAtual', self::SETOR_PATRIMONIO_ID);
                })
                ->orWhereHas('ultimoMaterialTransporte.idSolicitacaoRef', function (Builder $solicitacaoQuery): void {
                    $solicitacaoQuery->where('setor_solicitante', self::SETOR_PATRIMONIO_ID);
                })
                ->orWhereHas('pedidoRef', function (Builder $pedidoQuery): void {
                    $pedidoQuery->where('Setor', self::SETOR_PATRIMONIO_ID);
                })
                ->orWhereHas('ultimaTransferencia.setorAtualRel', function (Builder $setorQuery): void {
                    $setorQuery->where('Setor', 'like', '%patrim%');
                })
                ->orWhere(function (Builder $fallbackQuery): void {
                    $fallbackQuery
                        ->whereDoesntHave('ultimaTransferencia.setorAtualRel')
                        ->whereHas('ultimoMaterialTransporte.idSolicitacaoRef.setorSolicitanteRef', function (Builder $setorQuery): void {
                            $setorQuery->where('Setor', 'like', '%patrim%');
                        });
                })
                ->orWhere(function (Builder $fallbackQuery): void {
                    $fallbackQuery
                        ->whereDoesntHave('ultimaTransferencia.setorAtualRel')
                        ->whereDoesntHave('ultimoMaterialTransporte.idSolicitacaoRef.setorSolicitanteRef')
                        ->whereHas('pedidoRef.setor_get', function (Builder $setorQuery): void {
                            $setorQuery->where('Setor', 'like', '%patrim%');
                        });
                })
                ->orWhere(function (Builder $fallbackQuery): void {
                    $fallbackQuery
                        ->whereDoesntHave('ultimaTransferencia.setorAtualRel')
                        ->whereDoesntHave('ultimoMaterialTransporte.idSolicitacaoRef.setorSolicitanteRef')
                        ->whereDoesntHave('pedidoRef.setor_get')
                        ->whereHas('ultimoMaterialTransporte.idSolicitacaoRef', function (Builder $solicitacaoQuery): void {
                            $solicitacaoQuery->where('local_destino', 'like', '%patrim%');
                        });
                });
        });
    }

    protected function qualifyTermoColumn(string $column): string
    {
        return (new Termo())->qualifyColumn($column);
    }

    protected function getSolicitacaoIdSubquery(): Builder
    {
        return Materiais::query()
            ->tipoTransporteCarga()
            ->select('id_solicitacao')
            ->whereColumn('id_termo', $this->qualifyTermoColumn('id'))
            ->latest('id')
            ->limit(1);
    }

    protected function getTransferenciaPedidoNoSubquery(): Builder
    {
        return TransferenciaBemMovel::query()
            ->select('pedido_no')
            ->whereColumn('Termo', $this->qualifyTermoColumn('id'))
            ->latest('id')
            ->limit(1);
    }

    public function exportCsv(): StreamedResponse
    {
        $records = $this->getFilteredSortedTableQuery()->get();
        $filename = 'entrega_recolhimento_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Entrega/Recolhimento',
                'Setor Anterior',
                'Complemento Anterior',
                'Setor Atual',
                'Complemento Atual',
                'Atualizado Em',
                'Atualizado Por',
                'Id Termo',
                'Termo',
                'Situacao Termo',
                'Observacao',
                'Pedido',
                'Id Solicitacao',
                'Situacao Solicitacao',
            ], ';');

            foreach ($records as $record) {
                fputcsv($handle, [
                    $this->inferFluxo($record),
                    $this->getSetorAnterior($record),
                    $this->getComplementoAnterior($record),
                    $this->getSetorAtual($record),
                    $this->getComplementoAtual($record),
                    filled($this->getTransferenciaAtualizadaEm($record))
                        ? date('d/m/Y H:i', strtotime((string) $this->getTransferenciaAtualizadaEm($record)))
                        : '',
                    $this->getTransferenciaAtualizadaPor($record),
                    $record->id,
                    "{$record->num_termo}/{$record->ano_termo}",
                    $this->getArquivoSituacao($record) === 0 ? 'Pendente' : 'Invalidado',
                    $this->getArquivoObservacao($record),
                    $this->getPedidoNo($record),
                    $this->getSolicitacaoId($record),
                    $this->getSituacaoSolicitacaoDescricao($record),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
