<?php

namespace App\Filament\Livewire\Externo;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Livewire\Externo\Almoxarifado\CarrinhoMateriaisConsumoForm;
use App\Filament\Livewire\Externo\Patrimonio\CarrinhoMateriaisPermanentesForm;
use App\Models\Almoxarifado\Pedidos;
use App\Models\Cadastro\ComplementoSetor;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Component;
use Throwable;

/**
 * Base do carrinho/formulário de envio de pedido do Ambiente Externo,
 * compartilhada entre {@see CarrinhoMateriaisConsumoForm}
 * e {@see CarrinhoMateriaisPermanentesForm}.
 *
 * Guarda o que é idêntico nos dois: os campos "Dados do pedido"
 * (Solicitante/Unidade Judiciária/Setor/Complemento do Setor), o subtotal e a
 * notificação/limpeza ao final do envio. Como o pedido é efetivamente
 * enviado — e como o item é removido do carrinho — fica a cargo de cada
 * subclasse, pois divergem de verdade (ver docblocks das subclasses).
 */
abstract class Carrinho extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public array $carrinho = [];

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getDefaultFormState());
    }

    abstract public function removerItem(int $chave): void;

    abstract public function enviarPedido(): void;

    public function limparCarrinho(): void
    {
        $this->carrinho = [];
    }

    public function getSubtotalCarrinhoProperty(): float
    {
        return collect($this->carrinho)
            ->sum(fn (array $item): float => $item['quantidade'] * $item['preco_unitario']);
    }

    /**
     * Campos comuns do destino do pedido: Solicitante, Unidade Judiciária,
     * Setor e Complemento do Setor de destino.
     */
    protected function camposDestinoSchema(): array
    {
        return [
            Select::make('Solicitante')
                ->label('Solicitante')
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                ->options(fn (): array => UserEgap::query()
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray())
                ->columnSpan(12),

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
                ->afterStateUpdated(fn (Set $set) => $set('Setor', null))
                ->columnSpan(12),

            Select::make('Setor')
                ->label('Setor')
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
                ->afterStateUpdated(fn (Set $set) => $set('ComplementoSetor', null))
                ->columnSpan(6),

            Select::make('ComplementoSetor')
                ->label('Complemento do setor de destino')
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                // Prioriza os complementos já usados em pedidos anteriores desse Setor
                // (mesma ideia do legado, que restringia a lista pelo setor selecionado).
                // Sem histórico para o Setor, cai para a lista completa.
                ->options(function (Get $get): array {
                    $setorId = $get('Setor');

                    $usados = filled($setorId)
                        ? Pedidos::query()
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
                ->disabled(fn (Get $get): bool => blank($get('Setor')))
                ->columnSpan(6),
        ];
    }

    protected function getDefaultFormState(): array
    {
        return [
            'Solicitante' => null,
            'UnidadeJudiciaria' => null,
            'Setor' => null,
            'ComplementoSetor' => null,
        ];
    }

    protected function finalizarEnvioComSucesso(int $pedidoId, string $destinatario): void
    {
        Notification::make()
            ->title("Pedido #{$pedidoId} criado com sucesso.")
            ->body("O pedido e os itens foram enviados {$destinatario}.")
            ->success()
            ->send();

        $this->limparCarrinho();
        $this->form->fill($this->getDefaultFormState());

        $this->dispatch('pedido-enviado');
    }

    protected function notificarErro(Throwable $exception): void
    {
        report($exception);

        Notification::make()
            ->title('Erro ao enviar pedido.')
            ->body($exception->getMessage())
            ->danger()
            ->send();
    }
}
