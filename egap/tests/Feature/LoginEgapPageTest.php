<?php

namespace Tests\Feature;

use App\Filament\Auth\LoginEgap;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression coverage for the class of bug that has twice slipped past
 * php -l / route:list during the Filament v3 -> v4 migration: a custom
 * Blade view or page method referencing a v3-only component/method that
 * only fatals when the Livewire component actually renders. Neither
 * static linting nor route listing instantiate the component, so this
 * page needs an explicit render smoke test.
 */
class LoginEgapPageTest extends TestCase
{
    public function test_login_page_renders_with_expected_fields_and_submit_button(): void
    {
        $response = $this->get('/egap/login');

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('wire:model="data.login"', $html);
        $this->assertStringContainsString('wire:model="data.password"', $html);
        $this->assertStringContainsString('wire:model="data.remember"', $html);
        $this->assertStringContainsString('wire:submit="authenticate"', $html);
        $this->assertStringContainsString('type="submit"', $html);
    }

    public function test_authenticate_action_runs_without_fataling_on_invalid_credentials(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('egap'));

        Livewire::test(LoginEgap::class)
            ->fillForm([
                'login' => 'usuario-que-nao-existe-'.uniqid(),
                'password' => 'senha-errada',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasErrors(['data.login']);
    }
}
