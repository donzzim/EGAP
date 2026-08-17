<?php

namespace App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio;

use App\Filament\Support\SetorSelecionado;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\ArquivoDigital;
use App\Models\Patrimonio\BensMoveis\Inventario;
use App\Models\Patrimonio\BensMoveis\ItemInventario;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Lista o histórico de itens já conferidos nos inventários online do setor do
 * usuário autenticado do Ambiente Externo (legado: bensainventariar.php),
 * exibindo a situação de cada patrimônio e, quando já assinado eletronicamente,
 * o Termo de Responsabilidade correspondente.
 *
 * Somente leitura: a conferência física dos bens (marcar localizado/não
 * localizado, incluir/editar itens) é feita na Atividade de Campo; esta tela é
 * o registro histórico do que já foi apurado.
 */
class HistoricoDeInventarioOnlineTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    private const SITUACAO_A_INVENTARIAR = 'A INVENTARIAR';

    public ?int $setorAtual = null;

    public function mount(): void
    {
        $this->setorAtual = SetorSelecionado::resolverAtual();
    }

    #[On('setor-selecionado')]
    public function atualizarSetorSelecionado(int $setor): void
    {
        $this->setorAtual = $setor;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getQuery())
            ->columns([
                TableColumns::text('id_inventario', 'Inventário', isFirstColumn: true)
                    ->formatStateUsing(fn ($state, ItemInventario $record): string => $record->idInventarioRef
                        ? "{$record->idInventarioRef->num_inventario}/{$record->idInventarioRef->ano_inventario}"
                        : '-')
                    ->badge()
                    ->color('primary'),

                TableColumns::text('num_patrimonio', 'Patrimônio')
                    ->badge()
                    ->color('gray'),

                TableColumns::text('num_patrimonioantigo', 'Patrimônio Antigo')
                    ->badge()
                    ->color('gray'),

                TableColumns::text('descricao_detalhada', 'Descrição')
                    ->wrap()
                    ->description(fn (ItemInventario $record): ?string => collect([
                        filled($record->marca) ? "Marca: {$record->marca}" : null,
                        filled($record->modelo) ? "Modelo: {$record->modelo}" : null,
                        filled($record->num_serie) ? "Nº Série: {$record->num_serie}" : null,
                    ])->filter()->implode(' • ') ?: null),

                TableColumns::text('estado_conservacao', 'Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match (strtoupper(trim((string) $state))) {
                        'ÓTIMO', 'BOM' => 'success',
                        'REGULAR' => 'warning',
                        'RUIM', 'SUCATA' => 'danger',
                        default => 'gray',
                    }),

                TableColumns::text('id_complementosetor', 'Complemento do Setor')
                    ->wrap()
                    ->formatStateUsing(fn (?string $state, ItemInventario $record): string => strtoupper(trim((string) ($record->complementoSetorRef?->descricao ?? $state))) ?: '-'),

                TableColumns::text('situacao', 'Situação')
                    ->formatStateUsing(fn (?string $state): string => match (strtoupper(trim((string) $state))) {
                        'LOCALIZADO/AJUSTES' => 'AJUSTES',
                        '' => '-',
                        default => strtoupper(trim((string) $state)),
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match (strtoupper(trim((string) $state))) {
                        'LOCALIZADO/AJUSTES' => 'warning',
                        'LOCALIZADO' => 'success',
                        'LOCALIZADO/NOVO' => 'info',
                        'EM TRANSFERENCIA', 'EM TRANSFERÊNCIA' => 'warning',
                        'NÃO LOCALIZADO', 'NAO LOCALIZADO' => 'danger',
                        default => 'gray',
                    }),

                TableColumns::text('termo', 'Termo')
                    ->tooltip('Clique para visualizar o termo')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state, ItemInventario $record): string => $record->termoRef?->termo_completo ?? '-')
                    ->description(fn (ItemInventario $record): ?string => $this->assinaturaTermo($record))
                    //->color(fn ($state, ItemInventario $record): string => ArquivoDigital::situacaoColor($record->termoRef?->arquivoDigital?->situacao))
                    ->color('primary')
                    ->url(fn (ItemInventario $record): ?string => filled($record->termoRef?->arquivoDigital?->arquivo_digital)
                        ? config('app.egap').$record->termoRef->arquivoDigital->arquivo_digital
                        : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                SelectFilter::make('id_inventario')
                    ->label('Inventário')
                    ->options(fn (): array => Inventario::query()
                        ->whereIn('id', ItemInventario::query()
                            ->where('setor', $this->setorAtual)
                            ->where('situacao', '<>', self::SITUACAO_A_INVENTARIAR)
                            ->distinct()
                            ->pluck('id_inventario'))
                        ->orderByDesc('ano_inventario')
                        ->get()
                        ->mapWithKeys(fn (Inventario $inventario): array => [
                            $inventario->id => "{$inventario->num_inventario}/{$inventario->ano_inventario}",
                        ])
                        ->toArray()),
            ], FiltersLayout::AboveContent)
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('num_patrimonio')
            ->emptyStateHeading(
                blank($this->setorAtual)
                    ? 'Não foi possível identificar o setor do usuário atual.'
                    : 'Nenhum registro encontrado no histórico'
            );
    }

    private function assinaturaTermo(ItemInventario $record): ?string
    {
        $arquivoDigital = $record->termoRef?->arquivoDigital;

        if ((int) ($arquivoDigital?->situacao ?? -1) !== ArquivoDigital::SITUACAO_VALIDADO) {
            return null;
        }

        return collect([
            $arquivoDigital->validadoPor?->name,
            $arquivoDigital->data_validacao?->format('d/m/Y'),
        ])->filter()->implode(' em ') ?: null;
    }

    private function getQuery(): Builder
    {
        return ItemInventario::query()
            ->when(
                blank($this->setorAtual),
                fn (Builder $query) => $query->whereRaw('1 = 0'),
                fn (Builder $query) => $query->where('setor', $this->setorAtual)
            )
            ->where('situacao', '<>', self::SITUACAO_A_INVENTARIAR)
            ->with([
                'idInventarioRef',
                'complementoSetorRef',
                'termoRef.arquivoDigital.validadoPor',
            ]);
    }

    public function render(): View
    {
        return view('livewire.support.table');
    }
}
