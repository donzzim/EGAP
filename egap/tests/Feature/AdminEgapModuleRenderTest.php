<?php

namespace Tests\Feature;

use App\Filament\Clusters\AdminEgapCluster\AcessosPermissoes;
use App\Filament\Resources\Admin\LotacaoResource\Pages\ListLotacaos;
use App\Filament\Resources\Admin\UsersEgapResource\Pages\ListUsersEgaps;
use App\Models\User;
use App\Models\UserEgap;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Temporary scratch verification for Task 13 (Administracao module).
 * Confirms the module's pages actually render at Livewire/HTTP level,
 * not just pass php -l / route:list, per the Task 12 lesson about
 * v3-only Blade components/methods that only fatal at render time.
 */
class AdminEgapModuleRenderTest extends TestCase
{
    private function adminUser(): User
    {
        return User::query()->whereIn('login', ['admin', 'admin2'])->firstOrFail();
    }

    public function test_acessos_permissoes_page_renders(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('egap'));
        $this->actingAs($this->adminUser(), 'pessoa');

        Livewire::test(AcessosPermissoes::class)->assertOk();
    }

    public function test_acessos_permissoes_page_renders_over_http(): void
    {
        $response = $this->actingAs($this->adminUser(), 'pessoa')
            ->get('/egap/admin-egap/acessos-permissoes');

        $response->assertOk();
    }

    public function test_lotacoes_list_page_renders(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('egap'));
        $this->actingAs($this->adminUser(), 'pessoa');

        Livewire::test(ListLotacaos::class)->assertOk();
    }

    public function test_users_egap_list_page_renders(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('egap'));
        $this->actingAs($this->adminUser(), 'pessoa');

        Livewire::test(ListUsersEgaps::class)->assertOk();
    }

    public function test_users_egap_lotacao_and_dados_pessoais_modal_actions_mount(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('egap'));
        $this->actingAs($this->adminUser(), 'pessoa');

        $record = UserEgap::query()->firstOrFail();

        Livewire::test(ListUsersEgaps::class)
            ->mountTableAction('lotacao', $record)
            ->assertOk()
            ->unmountTableAction();

        Livewire::test(ListUsersEgaps::class)
            ->mountTableAction('dados_pessoais', $record)
            ->assertOk()
            ->unmountTableAction();
    }
}
