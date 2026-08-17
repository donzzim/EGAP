<?php

namespace App\Filament\Clusters\ExternoCluster\Concerns;

use App\Filament\Support\UsuarioSelecionado;
use App\Models\UserEgap;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

/**
 * Compartilha, entre as páginas do cluster de Agendamento do Ambiente Externo, a
 * identificação manual do usuário solicitante (ver {@see UsuarioSelecionado}).
 * Mesmo padrão de {@see SelecionaSetorAtual}.
 */
trait SelecionaUsuarioAtual
{
    public function mount(): void
    {
        if (blank(UsuarioSelecionado::resolverAtual())) {
            $this->mountAction('selecionarUsuario');
        }
    }

    public function selecionarUsuarioHeaderAction(): Action
    {
        return $this->selecionarUsuarioAction()
            ->label('Trocar Usuário')
            ->icon('heroicon-o-arrow-path')
            ->color('gray');
    }

    public function selecionarUsuarioAction(): Action
    {
        return Action::make('selecionarUsuario')
            ->modalHeading('Identifique seu usuário')
            ->modalDescription('Selecione o usuário correspondente para carregar suas solicitações. Esta etapa é temporária, até a migração dos dados de sessão concluir.')
            ->modalSubmitActionLabel('Continuar')
            ->modalCancelAction(false)
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->fillForm(fn (): array => [
                'usuario' => session(UsuarioSelecionado::SESSION_KEY),
            ])
            ->schema([
                Select::make('usuario')
                    ->label('Usuário')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->getSearchResultsUsing(fn (string $search): array => UserEgap::query()
                        ->where('name', 'like', "%{$search}%")
                        ->orderBy('name')
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => filled($value)
                        ? UserEgap::query()->whereKey($value)->value('name')
                        : null),
            ])
            ->action(function (array $data): void {
                session([UsuarioSelecionado::SESSION_KEY => (int) $data['usuario']]);

                $this->dispatch('usuario-selecionado', usuario: (int) $data['usuario']);

                $this->notificarUsuarioAtual((int) $data['usuario']);
            });
    }

    private function notificarUsuarioAtual(int $usuarioId): void
    {
        $usuario = UserEgap::query()->find($usuarioId)?->name ?? "Usuário {$usuarioId}";

        Notification::make()
            ->title('Visualizando dados de:')
            ->body($usuario)
            ->info()
            ->send();
    }
}
