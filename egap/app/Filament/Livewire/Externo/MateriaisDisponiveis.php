<?php

namespace App\Filament\Livewire\Externo;

use App\Filament\Livewire\Externo\Almoxarifado\MateriaisConsumoTable;
use App\Filament\Livewire\Externo\Patrimonio\MateriaisPermanentesTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Base da tabela de materiais disponíveis para requisição do Ambiente
 * Externo, compartilhada entre
 * {@see MateriaisConsumoTable} e
 * {@see MateriaisPermanentesTable}.
 *
 * Guarda o que é idêntico nos dois: a quantidade digitada direto na tabela
 * (limpa quando o pedido é enviado) e a validação dessa quantidade antes de
 * adicionar ao carrinho. Query, colunas de listagem e a action "Adicionar"
 * ficam a cargo de cada subclasse, pois a origem dos materiais e os dados
 * pedidos ao adicionar divergem de verdade (ver docblocks das subclasses).
 */
abstract class MateriaisDisponiveis extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /** @var array<int, int|null> quantidade digitada por material, indexada pelo id do material */
    public array $quantidades = [];

    #[On('pedido-enviado')]
    public function limparQuantidades(): void
    {
        $this->quantidades = [];
    }

    protected function quantidadeColumn(): TextInputColumn
    {
        return TextInputColumn::make('quantidade')
            ->label('Quantidade')
            ->alignCenter()
            ->type('number')
            ->rules(['nullable', 'integer', 'min:0'])
            ->state(fn ($record): ?int => $this->quantidades[$record->id] ?? null)
            ->updateStateUsing(function ($record, $state): void {
                $this->quantidades[$record->id] = filled($state) ? (int) $state : null;
            });
    }

    /**
     * Valida a quantidade digitada para o material antes de adicionar ao
     * carrinho. Notifica e retorna null quando inválida.
     */
    protected function validarQuantidade(int $materialId): ?int
    {
        $quantidade = (int) ($this->quantidades[$materialId] ?? 0);

        if ($quantidade < 1) {
            Notification::make()
                ->title('Informe uma quantidade válida antes de adicionar.')
                ->warning()
                ->send();

            return null;
        }

        return $quantidade;
    }
}
