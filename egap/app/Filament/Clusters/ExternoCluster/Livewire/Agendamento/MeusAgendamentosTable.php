<?php

namespace App\Filament\Clusters\ExternoCluster\Livewire\Agendamento;

use App\Filament\Support\SetorSelecionado;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\UsuarioSelecionado;
use App\Models\Agendamento\Solicitacao;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Lista as solicitações de agendamento de veículos feitas pelo usuário e setor
 * atualmente identificados no Ambiente Externo (legado: consultar_agendamento.php
 * + api/agendamento.api.php), permitindo cancelar, editar ou avaliar (finalizar)
 * cada solicitação conforme a situação atual.
 *
 * O usuário e o setor solicitante ainda não têm um vínculo confiável com a sessão
 * autenticada (ver {@see UsuarioSelecionado} e {@see SetorSelecionado}), por isso
 * a página os identifica manualmente antes de carregar esta tabela.
 */
class MeusAgendamentosTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    private const TIPO_AGENDAMENTO_VEICULOS = 1;

    private const SITUACAO_EM_ANALISE = 6;

    private const SITUACAO_AGENDADO = 8;

    private const SITUACAO_CONCLUIDO = 3;

    private const SITUACAO_CANCELADO = 4;

    private const SITUACAO_INVALIDADO = 5;

    #[On('usuario-selecionado')]
    #[On('setor-selecionado')]
    public function atualizarSelecaoAtual(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('agendamento')
                    ->label('Agendamento')
                    ->state(fn (Solicitacao $record): string => $this->agendamentoTexto($record))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('id', $direction)),

                TextColumn::make('solicitante')
                    ->label('Solicitação')
                    ->state(fn (Solicitacao $record): string => $record->idSolicitanteRef?->name ?? '-')
                    ->description(fn (Solicitacao $record): ?string => collect([
                        filled($record->date_time) ? 'Solicitado em '.Carbon::parse($record->date_time)->format('d/m/Y') : null,
                        filled($passageiro = $this->passageiroPrincipal($record)) ? "Para: {$passageiro}" : null,
                    ])->filter()->implode(' • ') ?: null)
                    ->tooltip(fn (Solicitacao $record): ?string => $this->contatos($record))
                    ->wrap(),

                TextColumn::make('trajeto')
                    ->label('Trajeto')
                    ->state(fn (Solicitacao $record): string => $this->rotaTexto($record))
                    ->description(fn (Solicitacao $record): ?string => collect([
                        filled($saida = $this->formatDataHora($record->data_inicio, $record->hora_inicio)) ? "Saída: {$saida}" : null,
                        filled($volta = $this->formatDataHora($record->data_termino, $record->hora_termino)) ? "Volta: {$volta}" : null,
                    ])->filter()->implode(' • ') ?: null)
                    ->wrap(),

                TextColumn::make('justificativa_texto')
                    ->label('Justificativa')
                    ->state(fn (Solicitacao $record): string => $this->justificativaTexto($record))
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? $state : '-')
                    ->limit(60)
                    ->tooltip(fn (?string $state): ?string => filled($state) ? $state : null)
                    ->description(fn (Solicitacao $record): ?string => collect([
                        $this->motoristaTexto($record),
                        'Tipo: '.$this->tipoTexto($record),
                    ])->filter()->implode(' • '))
                    ->wrap(),

                TextColumn::make('idSituacaoRef.Descricao')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Solicitacao $record): string => $this->statusColor((int) $record->id_situacao))
                    ->description(fn (Solicitacao $record): ?string => $this->statusDescricao($record))
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('id_situacao')
                    ->label('Situação')
                    ->options([
                        self::SITUACAO_EM_ANALISE => 'Em Análise',
                        self::SITUACAO_AGENDADO => 'Agendados',
                        self::SITUACAO_CONCLUIDO => 'Concluído',
                        self::SITUACAO_CANCELADO => 'Cancelado',
                        self::SITUACAO_INVALIDADO => 'Inválidado',
                    ])
                    ->default(self::SITUACAO_EM_ANALISE)
                    ->native(false)
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $builder): Builder => $builder->where('id_situacao', $data['value'])
                    )),
            ], FiltersLayout::AboveContent)
            ->recordActions([
                $this->verAgendamentoAction(),
                $this->avaliarAction(),
                $this->editarAction(),
                $this->cancelarAction(),
            ])
            ->toolbarActions([])
            ->defaultSort('id', 'desc')
            ->emptyStateHeading('Nenhuma solicitação encontrada');
    }

    private function cancelarAction(): Action
    {
        return Action::make('cancelar')
            ->label('Cancelar')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Solicitacao $record): bool => in_array((int) $record->id_situacao, [self::SITUACAO_EM_ANALISE, self::SITUACAO_AGENDADO], true))
            ->modalHeading(fn (Solicitacao $record): string => "Cancelar solicitação nº {$record->id}")
            ->modalDescription('Informe o motivo do cancelamento. Essa ação não pode ser desfeita.')
            ->modalSubmitActionLabel('Confirmar cancelamento')
            ->schema([
                Textarea::make('justificativa')
                    ->label('Motivo do cancelamento')
                    ->required()
                    ->rows(4)
                    ->maxLength(300),
            ])
            ->action(function (Solicitacao $record, array $data): void {
                $record->update([
                    'id_situacao' => self::SITUACAO_CANCELADO,
                    'motivo_cancelamento' => $data['justificativa'],
                ]);

                Notification::make()
                    ->title("Solicitação nº {$record->id} cancelada.")
                    ->success()
                    ->send();
            });
    }

    /**
     * Reabre a solicitação para análise (id_situacao volta a 6), disponível tanto
     * para quem está em análise quanto para quem foi invalidado — mesmo
     * comportamento do api/edit.api.php do legado, que sempre grava
     * `id_situacao = 6` após a edição.
     */
    private function editarAction(): Action
    {
        return Action::make('editar')
            ->label('Editar')
            ->icon('heroicon-o-pencil-square')
            ->color('warning')
            ->visible(fn (Solicitacao $record): bool => in_array((int) $record->id_situacao, [self::SITUACAO_EM_ANALISE, self::SITUACAO_INVALIDADO], true))
            ->modalHeading(fn (Solicitacao $record): string => "Editar solicitação nº {$record->id}")
            ->fillForm(fn (Solicitacao $record): array => [
                'local_saida' => $record->local_saida,
                'local_destino' => $record->local_destino,
                'data_inicio' => $record->data_inicio,
                'hora_inicio' => $record->hora_inicio,
                'data_termino' => $record->data_termino,
                'hora_termino' => $record->hora_termino,
            ])
            ->schema([
                TextInput::make('local_saida')
                    ->label('Local de saída')
                    ->required()
                    ->maxLength(255),
                TextInput::make('local_destino')
                    ->label('Local de destino')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('data_inicio')
                    ->label('Data início')
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                TimePicker::make('hora_inicio')
                    ->label('Hora início')
                    ->seconds(false),
                DatePicker::make('data_termino')
                    ->label('Data término (viagens de ida e volta)')
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                TimePicker::make('hora_termino')
                    ->label('Hora término')
                    ->seconds(false),
            ])
            ->action(function (Solicitacao $record, array $data): void {
                $payload = [
                    'id_situacao' => self::SITUACAO_EM_ANALISE,
                    'local_saida' => $data['local_saida'],
                    'local_destino' => $data['local_destino'],
                    'data_inicio' => $data['data_inicio'],
                    'hora_inicio' => $data['hora_inicio'],
                ];

                if (filled($data['data_termino'] ?? null)) {
                    $payload['data_termino'] = $data['data_termino'];
                    $payload['hora_termino'] = $data['hora_termino'];
                }

                $justificativa = $record->justificativa ?? [];
                $justificativa['destino'] = $data['local_destino'];
                $payload['justificativa'] = $justificativa;

                $record->update($payload);

                Notification::make()
                    ->title("Solicitação nº {$record->id} editada.")
                    ->success()
                    ->send();
            });
    }

    private function avaliarAction(): Action
    {
        return Action::make('avaliar')
            ->label('Avaliar')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Solicitacao $record): bool => (int) $record->id_situacao === self::SITUACAO_AGENDADO)
            ->modalHeading(fn (Solicitacao $record): string => "Avaliar solicitação nº {$record->id}")
            ->modalDescription('Avalie o atendimento recebido e, se desejar, deixe uma observação.')
            ->schema([
                Select::make('rating')
                    ->label('Avaliação')
                    ->options([
                        5 => '★★★★★ Excelente',
                        4 => '★★★★ Bom',
                        3 => '★★★ Regular',
                        2 => '★★ Ruim',
                        1 => '★ Péssimo',
                    ])
                    ->native(false)
                    ->required(),
                Textarea::make('obs')
                    ->label('Observações')
                    ->rows(3)
                    ->maxLength(300),
            ])
            ->action(function (Solicitacao $record, array $data): void {
                $record->update([
                    'id_situacao' => self::SITUACAO_CONCLUIDO,
                    'finalizar' => json_encode([
                        'data' => now()->toDateString(),
                        'hora' => now()->format('H:i'),
                        'obs' => $data['obs'] ?? '',
                        'rating' => $data['rating'],
                    ], JSON_UNESCAPED_UNICODE),
                ]);

                Notification::make()
                    ->title("Solicitação nº {$record->id} avaliada com sucesso!")
                    ->success()
                    ->send();
            });
    }

    private function verAgendamentoAction(): Action
    {
        return Action::make('ver_agendamento')
            ->label('Ver Agendamento')
            ->icon('heroicon-o-truck')
            ->color('gray')
            ->visible(fn (Solicitacao $record): bool => (int) $record->id_situacao === self::SITUACAO_AGENDADO)
            ->modalHeading(fn (Solicitacao $record): string => "Agendamento da solicitação nº {$record->id}")
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (Solicitacao $record): View => view(
                'livewire.externo.detalhes-agendamento',
                ['recursos' => $record->recursosRef()->with(['condutorRef.idPessoaRef', 'veiculoRef'])->get()],
            ));
    }

    private function agendamentoTexto(Solicitacao $record): string
    {
        if (blank($record->date_time)) {
            return (string) $record->id;
        }

        return $record->id.'/'.Carbon::parse($record->date_time)->format('Y');
    }

    private function rotaTexto(Solicitacao $record): string
    {
        $destino = $record->local_destino;

        if (blank($destino)) {
            $destino = collect($this->justificativaDados($record)['destino'] ?? [])->implode(', ');
        }

        return collect([$record->local_saida, $destino])
            ->filter(fn (?string $valor): bool => filled($valor))
            ->implode(' → ') ?: '-';
    }

    private function justificativaDados(Solicitacao $record): array
    {
        $valor = $record->justificativa;

        return is_array($valor) ? $valor : [];
    }

    private function justificativaTexto(Solicitacao $record): string
    {
        return trim((string) ($this->justificativaDados($record)['justificativa'] ?? ''));
    }

    private function passageiroPrincipal(Solicitacao $record): ?string
    {
        return collect($this->justificativaDados($record)['passageiros'] ?? [])->first();
    }

    private function contatos(Solicitacao $record): ?string
    {
        $dados = $this->justificativaDados($record);

        return collect([$dados['telefone'] ?? null, $dados['celular'] ?? null])
            ->filter(fn (?string $valor): bool => filled($valor))
            ->implode(' / ') ?: null;
    }

    private function motoristaTexto(Solicitacao $record): string
    {
        $dados = $this->justificativaDados($record);

        return collect([
            filled($dados['veiculo_motorista'] ?? null) ? "Motorista: {$dados['veiculo_motorista']}" : null,
            filled($dados['aguardar_destino'] ?? null) ? "Aguarda no destino: {$dados['aguardar_destino']}" : null,
        ])->filter()->implode(' • ');
    }

    private function tipoTexto(Solicitacao $record): string
    {
        return (int) $record->tipo === self::TIPO_AGENDAMENTO_VEICULOS ? 'Veículo' : 'Carga';
    }

    private function intempestividadeTexto(Solicitacao $record): ?string
    {
        $dados = $this->justificativaDados($record);

        return $dados['justificativa_intempestividade'] ?? $dados['justifique'] ?? null;
    }

    private function motivoTexto(Solicitacao $record): ?string
    {
        if (blank($record->motivo_cancelamento)) {
            return null;
        }

        $rotulo = (int) $record->id_situacao === self::SITUACAO_INVALIDADO
            ? 'Motivo da invalidação'
            : 'Motivo do cancelamento';

        return "{$rotulo}: {$record->motivo_cancelamento}";
    }

    private function statusDescricao(Solicitacao $record): ?string
    {
        $situacao = (int) $record->id_situacao;

        if (in_array($situacao, [self::SITUACAO_CANCELADO, self::SITUACAO_INVALIDADO], true)) {
            return $this->motivoTexto($record);
        }

        if ($situacao !== self::SITUACAO_CONCLUIDO) {
            return $this->intempestividadeTexto($record);
        }

        return null;
    }

    private function formatDataHora(?string $data, ?string $hora): ?string
    {
        if (blank($data)) {
            return null;
        }

        return filled($hora)
            ? Carbon::parse("{$data} {$hora}")->format('d/m/Y H:i')
            : Carbon::parse($data)->format('d/m/Y');
    }

    private function statusColor(int $status): string
    {
        return match ($status) {
            self::SITUACAO_CONCLUIDO => 'success',
            self::SITUACAO_AGENDADO => 'info',
            self::SITUACAO_EM_ANALISE => 'warning',
            self::SITUACAO_CANCELADO, self::SITUACAO_INVALIDADO => 'danger',
            default => 'gray',
        };
    }

    private function getQuery(): Builder
    {
        $usuarioId = UsuarioSelecionado::resolverAtual();
        $setorId = SetorSelecionado::resolverAtual();

        return Solicitacao::query()
            ->where('tipo', self::TIPO_AGENDAMENTO_VEICULOS)
            ->when(
                blank($usuarioId) || blank($setorId),
                fn (Builder $query) => $query->whereRaw('1 = 0'),
                fn (Builder $query) => $query
                    ->where('id_solicitante', $usuarioId)
                    ->where('setor_solicitante', $setorId)
            )
            ->with(['idSituacaoRef', 'idSolicitanteRef']);
    }

    public function render(): View
    {
        return view('livewire.support.table');
    }
}
