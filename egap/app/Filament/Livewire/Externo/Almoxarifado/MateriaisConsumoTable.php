<?php

namespace App\Filament\Livewire\Externo\Almoxarifado;

use App\Filament\Livewire\Externo\MateriaisDisponiveis;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Almoxarifado\MovimentacaoEstoque;
use App\Models\Cadastro\DescricaoDetalhada;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class MateriaisConsumoTable extends MateriaisDisponiveis
{
    public string $tipoMaterial;

    public function mount(string $tipoMaterial): void
    {
        $this->tipoMaterial = $tipoMaterial;
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getQuery())
            ->columns([
                TableColumns::text('descricao_detalhada', 'Material', isFirstColumn: true)
                    ->wrap(),

                TableColumns::text('unidadeMedida.Sigla', 'Unidade de Medida')
                    ->sortable(false),

                TableColumns::money('preco_medio_estoque_atual', 'Preço médio')
                    ->searchable(false)
                    ->sortable(false),

                TableColumns::text('quantidade_estoque_atual', 'Estoque')
                    ->searchable(false)
                    ->sortable(false)
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => number_format((float) $state, 0, ',', '.'))
                    ->color('success'),

                $this->quantidadeColumn(),
            ])
            ->actions([
                Action::make('adicionar')
                    ->label('Adicionar')
                    ->button()
                    ->icon('heroicon-m-plus')
                    ->action(fn (DescricaoDetalhada $record) => $this->adicionarAoCarrinho($record)),
            ])
            ->bulkActions([])
            ->defaultSort('descricao_detalhada')
            ->emptyStateHeading('Nenhum material disponível no momento');
    }

    protected function adicionarAoCarrinho(DescricaoDetalhada $record): void
    {
        $quantidade = $this->validarQuantidade($record->id);

        if ($quantidade === null) {
            return;
        }

        $this->dispatch(
            'item-adicionado-ao-carrinho',
            materialId: $record->id,
            descricaoResumidaId: (int) $record->descricao_resumida,
            descricao: $record->descricao_detalhada,
            quantidade: $quantidade,
            precoUnitario: (float) $record->preco_medio_estoque_atual,
        );

        Notification::make()
            ->title('Item(ns) adicionado(s) ao carrinho.')
            ->success()
            ->send();

        unset($this->quantidades[$record->id]);
    }

    protected function getQuery(): Builder
    {
        return DescricaoDetalhada::query()
            ->with('unidadeMedida')
            ->whereHas('descricao_resumida_text', fn (Builder $query) => $query->where('id_tipo_material', $this->tipoMaterial))
            ->leftJoinSub(
                MovimentacaoEstoque::query()->selectRaw('material, MAX(id) as ultimo_id')->groupBy('material'),
                'ultimo_estoque',
                'ultimo_estoque.material',
                '=',
                'mat_descricaodetalhada.id',
            )
            ->leftJoin('alm_estoque as estoque_atual', 'estoque_atual.id', '=', 'ultimo_estoque.ultimo_id')
            ->whereRaw('COALESCE(estoque_atual.quantidade_estoque, 0) > 0')
            ->select('mat_descricaodetalhada.*')
            ->selectRaw('COALESCE(estoque_atual.quantidade_estoque, 0) as quantidade_estoque_atual')
            ->selectRaw('ROUND(COALESCE(estoque_atual.preco_medio_estoque, 0), 4) as preco_medio_estoque_atual');
    }

    public function render(): View
    {
        return view('livewire.externo.table');
    }
}
