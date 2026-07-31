<?php

namespace App\Filament\Support;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Base para os modais que exibem um formulário próprio, abertos a partir de
 * uma Action criada com {@see FormModalAction::make()}. Cada subclasse é
 * livre para desenhar o form() como preferir (schema, validações, seções
 * etc.) e para nomear seu próprio método de submit; esta classe só
 * padroniza o envelope do modal (wrapper, botões Cancelar/Confirmar no
 * rodapé) e o fechamento via evento Livewire "close-modal".
 *
 * Subclasses tipicamente sobrescrevem submitMethod(), submitLabel() e,
 * opcionalmente, submitIcon() para apontar para seu próprio método de
 * gravação (ex.: "transferir", "salvar").
 */
abstract class FormModalComponent extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public string $parentLivewireId = '';

    abstract public function form(Form $form): Form;

    protected function submitMethod(): string
    {
        return 'submit';
    }

    protected function submitLabel(): string
    {
        return 'Confirmar';
    }

    protected function submitIcon(): ?string
    {
        return 'heroicon-m-check-circle';
    }

    public function cancelar(): void
    {
        $this->fecharModal();
    }

    protected function fecharModal(): void
    {
        $this->dispatch('close-modal', id: "{$this->parentLivewireId}-action");
    }

    public function render(): View
    {
        return view(FormModalAction::VIEW, [
            'submitMethod' => $this->submitMethod(),
            'submitLabel' => $this->submitLabel(),
            'submitIcon' => $this->submitIcon(),
        ]);
    }
}
