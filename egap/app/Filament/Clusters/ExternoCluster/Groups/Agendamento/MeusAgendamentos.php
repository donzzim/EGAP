<?php

namespace App\Filament\Clusters\ExternoCluster\Groups\Agendamento;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Concerns\SelecionaSetorAtual;
use App\Filament\Clusters\ExternoCluster\Concerns\SelecionaUsuarioAtual;
use App\Filament\Support\SetorSelecionado;
use App\Filament\Support\UsuarioSelecionado;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\On;

class MeusAgendamentos extends Page
{
    use SelecionaSetorAtual;
    use SelecionaUsuarioAtual;

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = ExternoCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Clock;

    protected static string|\UnitEnum|null $navigationGroup = 'Agendamento de Veículos';

    protected static ?string $slug = 'agendamento/meus-agendamentos';

    protected static ?string $navigationLabel = 'Meus Agendamentos';

    protected static ?string $title = 'Minhas Solicitações';

    protected string $view = 'livewire.support.table-page';

    /**
     * Encadeia os dois modais provisórios de identificação (ver
     * {@see SelecionaUsuarioAtual} e {@see SelecionaSetorAtual}): primeiro pede o
     * usuário e, só depois de resolvido, pede o setor/unidade — a mesma sequência
     * é retomada em {@see verificarSetorAposUsuario()} quando o modal de usuário é
     * o primeiro a ser exibido.
     */
    public function mount(): void
    {
        if (blank(UsuarioSelecionado::resolverAtual())) {
            $this->mountAction('selecionarUsuario');

            return;
        }

        if (blank(SetorSelecionado::resolverAtual())) {
            $this->mountAction('selecionarSetor');
        }
    }

    #[On('usuario-selecionado')]
    public function verificarSetorAposUsuario(): void
    {
        if (blank(SetorSelecionado::resolverAtual())) {
            $this->mountAction('selecionarSetor');
        }
    }

    public function getHeaderActions(): array
    {
        return [
            $this->selecionarUsuarioHeaderAction(),
            $this->selecionarSetorHeaderAction(),
        ];
    }

    public function component(): string
    {
        return ExternoCluster\Livewire\Agendamento\MeusAgendamentosTable::class;
    }
}
