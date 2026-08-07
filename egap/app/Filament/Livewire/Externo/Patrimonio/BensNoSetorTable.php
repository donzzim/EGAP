<?php

namespace App\Filament\Livewire\Externo\Patrimonio;

use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource;
use App\Filament\Support\SetorSelecionado;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Cadastro\ComplementoSetor;
use App\Models\Cadastro\Setores;
use App\Models\Patrimonio\BensMoveis\ArquivoDigital;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\Termo;
use App\Models\Patrimonio\BensMoveis\TransferenciaBemMovel;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

/**
 * Lista os bens patrimoniais lotados no setor do usuário autenticado do
 * Ambiente Externo (legado: bens.php + transferencia.api.php), permitindo
 * editar dados básicos do bem e solicitar a transferência para outro setor.
 *
 * A transferência gera um Termo de Responsabilidade pendente (mesmo fluxo
 * usado em {@see ValidarTermoResource}):
 * a localização do bem só é efetivada quando o termo é validado pela Seção
 * de Patrimônio, então esta tela não altera Setor/Unidade do bem diretamente.
 */
class BensNoSetorTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    private const SITUACOES_ATIVAS = [1, 7, 8];

    private const SITUACAO_EM_TRANSFERENCIA = 7;

    public ?int $setorAtual = null;

    public function mount(): void
    {
        $this->setorAtual = SetorSelecionado::resolverAtual();
    }

    #[On('setor-selecionado')]
    public function atualizarSetorSelecionado(int $setor): void
    {
        $this->setorAtual = $setor;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getQuery())
            ->columns([
                TableColumns::text('NumPatrimonio', 'Patrimônio', isFirstColumn: true)
                    ->badge()
                    ->color('primary'),

                TableColumns::text('NumerodePatAnterior', 'Patrimônio Anterior')
                    ->badge()
                    ->color('gray'),

                TableColumns::text('Descricao', 'Material')
                    ->wrap()
                    ->description(fn (BemMovel $record): ?string => collect([
                        $record->marcaRef?->Descricao,
                        $record->modeloRef?->descricao,
                    ])->filter()->implode(' / ') ?: null),

                TableColumns::text('complementoSetorRef.descricao', 'Complemento do Setor')
                    ->wrap(),

                TableColumns::text('EstadodeConservacao', 'Estado de Conservação')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'ÓTIMO', 'BOM' => 'success',
                        'REGULAR' => 'warning',
                        'RUIM', 'SUCATA' => 'danger',
                        default => 'gray',
                    }),

                TableColumns::text('situacaoBemRef.descricao', 'Situação')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('termo_pendente')
                    ->label('Termo')
                    ->getStateUsing(fn (BemMovel $record): ?string => $this->termoPendente($record))
                    ->badge()
                    ->color('warning')
                    ->default('-')
                    ->url(fn (BemMovel $record): ?string => $this->termoPendente($record)
                        ? route('termo.imprimir', ['id' => $record->ultimaTransferencia->Termo])
                        : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                SelectFilter::make('ComplementoSetor')
                    ->label('Complemento do Setor')
                    ->options(fn (): array => ComplementoSetor::query()
                        ->whereIn('id', BemMovel::query()
                            ->where('Setor', $this->setorAtual)
                            ->whereNotNull('ComplementoSetor')
                            ->distinct()
                            ->pluck('ComplementoSetor'))
                        ->orderBy('descricao')
                        ->pluck('descricao', 'id')
                        ->toArray()),
            ], FiltersLayout::AboveContent)
            ->recordActions([
                $this->editarAction(),
            ])
            ->toolbarActions([
                $this->transferirBulkAction(),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (BemMovel $record): bool => (int) $record->SituacaoBem !== self::SITUACAO_EM_TRANSFERENCIA
            )
            ->defaultSort('Descricao')
            ->emptyStateHeading(
                blank($this->setorAtual)
                    ? 'Não foi possível identificar o setor do usuário atual.'
                    : 'Nenhum bem encontrado no setor'
            );
    }

    private function editarAction(): Action
    {
        return Action::make('editar')
            ->label('Editar')
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->modalHeading(fn (BemMovel $record): string => "Editar bem - Patrimônio {$record->NumPatrimonio}")
            ->disabled(fn (BemMovel $record): bool => (int) $record->SituacaoBem === self::SITUACAO_EM_TRANSFERENCIA)
            ->tooltip(fn (BemMovel $record): ?string => (int) $record->SituacaoBem === self::SITUACAO_EM_TRANSFERENCIA
                ? 'Não é possível editar um patrimônio em transferência.'
                : null)
            ->fillForm(fn (BemMovel $record): array => [
                'NumerodeSerie' => $record->NumerodeSerie,
                'ComplementoSetor' => $record->ComplementoSetor,
                'EstadodeConservacao' => $record->EstadodeConservacao,
            ])
            ->schema([
                TextInput::make('NumerodeSerie')
                    ->label('Número de série')
                    ->maxLength(50),

                Select::make('ComplementoSetor')
                    ->label('Complemento do setor')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->options(fn (): array => ComplementoSetor::query()
                        ->orderBy('descricao')
                        ->pluck('descricao', 'id')
                        ->toArray()),

                Select::make('EstadodeConservacao')
                    ->label('Estado de conservação')
                    ->required()
                    ->native(false)
                    ->options([
                        'ÓTIMO' => 'ÓTIMO',
                        'BOM' => 'BOM',
                        'REGULAR' => 'REGULAR',
                        'RUIM' => 'RUIM',
                    ]),
            ])
            ->action(function (BemMovel $record, array $data): void {
                $record->update([
                    'NumerodeSerie' => $data['NumerodeSerie'],
                    'ComplementoSetor' => $data['ComplementoSetor'],
                    'EstadodeConservacao' => $data['EstadodeConservacao'],
                ]);

                Notification::make()
                    ->title('Bem atualizado com sucesso.')
                    ->success()
                    ->send();
            });
    }

    private function transferirBulkAction(): BulkAction
    {
        return BulkAction::make('transferir_bens')
            ->label('Transferir Bens')
            ->icon('heroicon-o-arrows-right-left')
            ->color('primary')
            ->modalHeading('Transferir bens selecionados')
            ->modalDescription('Um Termo de Responsabilidade será gerado e ficará pendente até a assinatura eletrônica do setor de destino.')
            ->modalSubmitActionLabel('Confirmar transferência')
            ->form([
                Select::make('UnidadeJudiciaria')
                    ->label('Unidade Judiciária de destino')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->native(false)
                    ->options(fn (): array => Setores::query()
                        ->whereColumn('id', 'CodigoPai')
                        ->orderBy('UnidadeOrganizacional')
                        ->pluck('UnidadeOrganizacional', 'CodigoPai')
                        ->toArray())
                    ->afterStateUpdated(fn (Set $set) => $set('Setor', null)),

                Select::make('Setor')
                    ->label('Setor de destino')
                    ->placeholder(fn (Get $get) => blank($get('UnidadeJudiciaria'))
                        ? 'Selecione primeiro a unidade judiciária'
                        : 'Selecione o setor')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->native(false)
                    ->options(fn (Get $get): array => Setores::query()
                        ->when(
                            $get('UnidadeJudiciaria'),
                            fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                        )
                        ->orderBy('Setor')
                        ->pluck('Setor', 'id')
                        ->toArray())
                    ->disabled(fn (Get $get): bool => blank($get('UnidadeJudiciaria')))
                    ->afterStateUpdated(fn (Set $set) => $set('ComplementoSetor', null)),

                Select::make('ComplementoSetor')
                    ->label('Complemento do setor de destino')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->options(function (Get $get): array {
                        $setorId = $get('Setor');

                        $usados = filled($setorId)
                            ? BemMovel::query()
                                ->where('Setor', $setorId)
                                ->whereNotNull('ComplementoSetor')
                                ->distinct()
                                ->pluck('ComplementoSetor')
                            : collect();

                        return ComplementoSetor::query()
                            ->when($usados->isNotEmpty(), fn ($query) => $query->whereIn('id', $usados))
                            ->orderBy('descricao')
                            ->pluck('descricao', 'id')
                            ->toArray();
                    })
                    ->disabled(fn (Get $get): bool => blank($get('Setor'))),

                Textarea::make('justificativa')
                    ->label('Justificativa da transferência')
                    ->required()
                    ->rows(4)
                    ->maxLength(300)
                    ->placeholder('Descreva o motivo da transferência dos bens selecionados.'),
            ])
            ->deselectRecordsAfterCompletion()
            ->action(fn (EloquentCollection $records, array $data) => $this->transferirBens($records, $data));
    }

    private function transferirBens(EloquentCollection $records, array $data): void
    {
        $bens = $records->filter(
            fn (BemMovel $bem): bool => (int) $bem->SituacaoBem !== self::SITUACAO_EM_TRANSFERENCIA
        );

        if ($bens->isEmpty()) {
            Notification::make()
                ->title('Os bens selecionados já estão em transferência.')
                ->warning()
                ->send();

            return;
        }

        try {
            $termo = DB::transaction(function () use ($bens, $data): Termo {
                $ano = (int) now()->year;

                $ultimoTermo = Termo::query()
                    ->where('ano_termo', $ano)
                    ->orderByDesc('num_termo')
                    ->lockForUpdate()
                    ->first(['num_termo']);

                $termo = Termo::query()->create([
                    'num_termo' => ((int) $ultimoTermo?->num_termo) + 1,
                    'ano_termo' => $ano,
                    'situacao_entrega' => 'Reservado',
                ]);

                $termo->arquivoDigital()->create([
                    'situacao' => ArquivoDigital::SITUACAO_PENDENTE,
                    'web' => 1,
                    'observacao' => "Justificativa do solicitante: {$data['justificativa']}",
                ]);

                $unidadeAtual = Setores::query()->find($data['Setor'])?->CodigodaUO ?? $data['UnidadeJudiciaria'];

                foreach ($bens as $bem) {
                    TransferenciaBemMovel::query()->create([
                        'NumPatrimonio' => $bem->NumPatrimonio,
                        'UnidadeAnterior' => $bem->UnidadeJudiciaria,
                        'SetorAnterior' => $bem->Setor,
                        'ComplementoAnterior' => $bem->ComplementoSetor,
                        'AndarAnterior' => $bem->AndarSetor,
                        'UnidadeAtual' => $unidadeAtual,
                        'SetorAtual' => $data['Setor'],
                        'ComplementoAtual' => $data['ComplementoSetor'],
                        'Termo' => $termo->id,
                    ]);
                }

                return $termo;
            });
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Não foi possível transferir os bens.')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title("Transferência registrada no termo {$termo->num_termo}/{$termo->ano_termo}.")
            ->body('A transferência ficará pendente até a assinatura eletrônica do setor de destino.')
            ->success()
            ->send();
    }

    private function termoPendente(BemMovel $record): ?string
    {
        $transferencia = $record->ultimaTransferencia;
        $termo = $transferencia?->termoRel;
        $arquivoDigital = $termo?->arquivoDigital;

        if (! $termo || (int) ($arquivoDigital?->situacao ?? -1) !== ArquivoDigital::SITUACAO_PENDENTE) {
            return null;
        }

        return "{$termo->num_termo}/{$termo->ano_termo}";
    }

    private function getQuery(): Builder
    {
        return BemMovel::query()
            ->when(
                blank($this->setorAtual),
                fn (Builder $query) => $query->whereRaw('1 = 0'),
                fn (Builder $query) => $query->where('Setor', $this->setorAtual)
            )
            ->whereIn('SituacaoBem', self::SITUACOES_ATIVAS)
            ->with([
                'complementoSetorRef',
                'situacaoBemRef',
                'marcaRef',
                'modeloRef',
                'ultimaTransferencia.termoRel.arquivoDigital',
            ]);
    }

    public function render(): View
    {
        return view('livewire.externo.table');
    }
}
