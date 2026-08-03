<?php

namespace App\Filament\Clusters\ExternoCluster\Concerns;

use App\Filament\Support\SetorSelecionado;
use App\Models\Cadastro\Setores;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

/**
 * Compartilha, entre as páginas do cluster de Patrimônio do Ambiente Externo, a
 * identificação manual do setor (ver {@see SetorSelecionado}).
 */
trait SelecionaSetorAtual
{
    public function mount(): void
    {
        $setorSessao = session(SetorSelecionado::SESSION_KEY);

        if (filled($setorSessao['setor'] ?? null)) {
            $this->notificarSetorAtual((int) $setorSessao['unidade'], (int) $setorSessao['setor']);

            return;
        }

        if (blank(SetorSelecionado::resolverAtual())) {
            $this->mountAction('selecionarSetor');
        }
    }

    public function selecionarSetorHeaderAction(): Action
    {
        return $this->selecionarSetorAction()
            ->label('Trocar Setor')
            ->icon('heroicon-o-arrow-path')
            ->color('gray');
    }

    public function selecionarSetorAction(): Action
    {
        return Action::make('selecionarSetor')
            ->modalHeading('Identifique seu setor')
            ->modalDescription('Selecione a Unidade Judiciária e o Setor para carregar os dados. Esta etapa é temporária, até a migração dos dados de usuário concluir.')
            ->modalSubmitActionLabel('Carregar dados do setor')
            ->modalCancelAction(false)
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->fillForm(fn (): array => [
                'UnidadeJudiciaria' => session(SetorSelecionado::SESSION_KEY.'.unidade'),
                'Setor' => session(SetorSelecionado::SESSION_KEY.'.setor'),
            ])
            ->form([
                Select::make('UnidadeJudiciaria')
                    ->label('Unidade Judiciária')
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
                    ->label('Setor')
                    ->placeholder(fn (Get $get) => blank($get('UnidadeJudiciaria'))
                        ? 'Selecione primeiro a unidade judiciária'
                        : 'Selecione o setor')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->options(fn (Get $get): array => Setores::query()
                        ->when(
                            $get('UnidadeJudiciaria'),
                            fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                        )
                        ->orderBy('Setor')
                        ->pluck('Setor', 'id')
                        ->toArray())
                    ->disabled(fn (Get $get): bool => blank($get('UnidadeJudiciaria'))),
            ])
            ->action(function (array $data): void {
                session([
                    SetorSelecionado::SESSION_KEY => [
                        'unidade' => (int) $data['UnidadeJudiciaria'],
                        'setor' => (int) $data['Setor'],
                    ],
                ]);

                $this->dispatch('setor-selecionado', setor: (int) $data['Setor']);

                $this->notificarSetorAtual((int) $data['UnidadeJudiciaria'], (int) $data['Setor']);
            });
    }

    private function notificarSetorAtual(int $unidadeId, int $setorId): void
    {
        $unidade = Setores::query()->find($unidadeId)?->UnidadeOrganizacional ?? "Unidade {$unidadeId}";
        $setor = Setores::query()->find($setorId)?->Setor ?? "Setor {$setorId}";

        Notification::make()
            ->title('Visualizando dados de:')
            ->body("{$unidade} - {$setor}")
            ->info()
            ->send();
    }
}
