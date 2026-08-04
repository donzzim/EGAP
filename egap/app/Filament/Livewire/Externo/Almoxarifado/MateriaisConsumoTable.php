<?php

namespace App\Filament\Livewire\Externo\Almoxarifado;

use App\Filament\Livewire\Externo\MateriaisDisponiveis;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Almoxarifado\MovimentacaoEstoque;
use App\Models\Cadastro\DescricaoDetalhada;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

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
                    ->disk('public')
                    ->state(fn (): string => 'descricao/1.jpg')
                    ->circular(),

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

    protected function emEstoque(DescricaoDetalhada $record): bool
    {
        return ((float) $record->quantidade_estoque_atual) > 0;
    }

    /**
     * `imagem` guarda o formato legado do Joomla: um JSON com o caminho do
     * arquivo prefixado por `/images/` (ex.: `/images/descricaodetalhada/x.jpg`),
     * que corresponde ao mesmo arquivo dentro do disco `public`.
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

        $path = preg_replace('#^/?images/#', '', (string) $decoded[0]->file);

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
