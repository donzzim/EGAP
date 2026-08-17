<?php

namespace App\Filament\Clusters\ExternoCluster\Livewire\Almoxarifado;

use App\Filament\Clusters\ExternoCluster\Livewire\MateriaisDisponiveis;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Almoxarifado\MovimentacaoEstoque;
use App\Models\Cadastro\DescricaoDetalhada;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class MateriaisConsumoTable extends MateriaisDisponiveis implements HasActions
{
    use InteractsWithActions;
    public string $tipoMaterial;

    public function mount(string $tipoMaterial): void
    {
        $this->tipoMaterial = $tipoMaterial;
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getQuery())
            ->recordClasses(fn (DescricaoDetalhada $record): ?string => $this->emEstoque($record) ? null : 'opacity-50 grayscale')
            ->columns([
                TableColumns::text('descricao_detalhada', 'Material', isFirstColumn: true)
                    ->description(fn (DescricaoDetalhada $record): ?string => $this->emEstoque($record) ? null : 'Material indisponível no momento')
                    ->wrap(),

                TableColumns::text('unidadeMedida.Sigla', 'Unidade de Medida')
                    ->sortable(false),

                TableColumns::money('preco_medio_estoque_atual', 'Preço médio')
                    ->searchable(false)
                    ->badge()
                    ->color('success')
                    ->sortable(false),

                ImageColumn::make('imagem')
                    ->label('Imagem ilustrativa')
                    ->alignCenter()
                    ->tooltip('Clique para ampliar')
                    ->disk('public')
                    ->state(fn (DescricaoDetalhada $record): string => $this->imagemPathComFallback($record))
                    ->circular()
                    ->action($this->ampliarImagemAction()),

                $this->quantidadeColumn(),
            ])
            ->recordActions([
                Action::make('adicionar')
                    ->label('Adicionar')
                    ->button()
                    ->icon('heroicon-m-plus')
                    ->action(fn (DescricaoDetalhada $record) => $this->adicionarAoCarrinho($record)),
            ])
            ->toolbarActions([])
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

    protected function emEstoque(DescricaoDetalhada $record): bool
    {
        return ((float) $record->quantidade_estoque_atual) > 0;
    }

    protected function ampliarImagemAction(): Action
    {
        return Action::make('ampliarImagem')
            ->label('Imagem ampliada')
            ->modalHeading(fn (DescricaoDetalhada $record): string => $record->descricao_detalhada)
            ->modalContent(fn (DescricaoDetalhada $record): HtmlString => new HtmlString(
                '<img src="'.e(Storage::disk('public')->url($this->imagemPathComFallback($record))).'" class="mx-auto max-h-[70vh] w-auto rounded-lg" alt="" />'
            ))
            ->modalWidth('lg')
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }

    protected function imagemPathComFallback(DescricaoDetalhada $record): string
    {
        return $this->imagemPath($record->imagem) ?? 'placeholder.svg';
    }

    /**
     * `imagem` guarda o formato legado do Joomla: um JSON com o caminho do
     * arquivo (ex.: `images/descricaodetalhada/x.jpg`), relativo à raiz do
     * disco `public` (pasta `images/` migrada do legado).
     */
    protected function imagemPath(?string $imagem): ?string
    {
        if (! is_string($imagem) || trim($imagem) === '') {
            return null;
        }

        $decoded = json_decode($imagem);

        if (! is_array($decoded) || empty($decoded[0]->file)) {
            return null;
        }

        $path = ltrim((string) $decoded[0]->file, '/');

        return Storage::disk('public')->exists($path) ? $path : null;
    }

    protected function getQuery(): Builder
    {
        return DescricaoDetalhada::query()
            ->with('unidadeMedida')
            ->whereIn('mat_descricaodetalhada.visibilidade', $this->visibilidadesPermitidas())
            ->whereHas('descricao_resumida_text', fn (Builder $query) => $query->where('id_tipo_material', $this->tipoMaterial))
            ->leftJoinSub(
                MovimentacaoEstoque::query()->selectRaw('material, MAX(id) as ultimo_id')->groupBy('material'),
                'ultimo_estoque',
                'ultimo_estoque.material',
                '=',
                'mat_descricaodetalhada.id',
            )
            ->leftJoin('alm_estoque as estoque_atual', 'estoque_atual.id', '=', 'ultimo_estoque.ultimo_id')
            ->select('mat_descricaodetalhada.*')
            ->selectRaw('COALESCE(estoque_atual.quantidade_estoque, 0) as quantidade_estoque_atual')
            ->selectRaw('ROUND(COALESCE(estoque_atual.preco_medio_estoque, 0), 4) as preco_medio_estoque_atual');
    }

    public function render(): View
    {
        return view('livewire.externo.table');
    }
}
