<?php

namespace App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio;

use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\AtividadeInventario;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Progresso do inventário online por Unidade/Setor/Complemento (legado:
 * atividades.php, seção "Bens" / #tbdesc-body), com totais colapsáveis por
 * setor via agrupamento nativo do Filament.
 *
 * Reflete a situação atual dos bens (mat_patrimonio.sit_inventario) para o
 * inventário selecionado. Para inventários já finalizados o legado recalculava
 * os números a partir do snapshot histórico em mat_itensinventario (o dado
 * "vivo" em mat_patrimonio é reciclado no ciclo seguinte); isso não foi
 * replicado aqui — para números definitivos de um inventário concluído,
 * consulte o Histórico de Inventário Online.
 */
class AtividadeDeCampoResumoTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    private const SITUACOES_ATIVAS = [1, 7, 8];

    public ?int $inventarioId = null;

    private ?Collection $atividadesPorSetor = null;

    public function mount(?int $inventarioId = null): void
    {
        $this->inventarioId = $inventarioId;
    }

    #[On('inventario-selecionado')]
    public function atualizarInventarioSelecionado(?int $inventario): void
    {
        $this->inventarioId = $inventario;
        $this->atividadesPorSetor = null;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('Setor')
                    ->label('Unidade / Setor')
                    ->formatStateUsing(fn (BemMovel $record): string => $record->setorRef?->rotuloInventario() ?? "Setor {$record->Setor}")
                    ->wrap(),

                TextColumn::make('ComplementoSetor')
                    ->label('Complemento')
                    ->formatStateUsing(fn (BemMovel $record): string => $record->complementoSetorRef?->descricao ?? '-')
                    ->wrap(),

                TextColumn::make('a_inventariar')
                    ->label('A Inventariar')
                    ->alignCenter()
                    ->numeric()
                    ->summarize(Sum::make()->label('Total')),

                TextColumn::make('localizado')
                    ->label('Localizado')
                    ->alignCenter()
                    ->numeric()
                    ->summarize(Sum::make()->label('Total')),

                TextColumn::make('nao_localizado')
                    ->label('Não Localizado')
                    ->alignCenter()
                    ->numeric()
                    ->summarize(Sum::make()->label('Total')),

                TextColumn::make('em_transferencia')
                    ->label('Em Transferência')
                    ->alignCenter()
                    ->numeric()
                    ->summarize(Sum::make()->label('Total')),

                TextColumn::make('inventariado')
                    ->label('Inventariado')
                    ->alignCenter()
                    ->numeric()
                    ->summarize(Sum::make()->label('Total')),

                TextColumn::make('total')
                    ->label('Total')
                    ->alignCenter()
                    ->numeric()
                    ->summarize(Sum::make()->label('Total')),

                TextColumn::make('inicio_atividade')
                    ->label('Início')
                    ->alignCenter()
                    ->formatStateUsing(fn (BemMovel $record): string => optional($this->atividadeDoSetor($record->Setor)?->inicio)->format('d/m/Y') ?? '-'),

                TextColumn::make('termino_atividade')
                    ->label('Término')
                    ->alignCenter()
                    ->formatStateUsing(fn (BemMovel $record): string => optional($this->atividadeDoSetor($record->Setor)?->termino)->format('d/m/Y') ?? '-'),

                TextColumn::make('situacao_atividade')
                    ->label('Situação')
                    ->alignCenter()
                    ->formatStateUsing(fn (BemMovel $record): string => $this->atividadeDoSetor($record->Setor)?->situacao ?: 'A inventariar')
                    ->badge()
                    ->color(fn (BemMovel $record): string => match (strtolower(trim((string) $this->atividadeDoSetor($record->Setor)?->situacao))) {
                        'finalizado', 'carga efetuada' => 'success',
                        'aberto', 'em andamento' => 'info',
                        default => 'warning',
                    }),
            ])
            ->groups([
                Group::make('Setor')
                    ->label('Unidade / Setor')
                    ->getTitleFromRecordUsing(fn (BemMovel $record): string => $record->setorRef?->rotuloInventario() ?? "Setor {$record->Setor}")
                    ->collapsible(),
            ])
            ->defaultGroup('Setor')
            ->defaultSort('Setor')
            ->defaultKeySort(false)
            ->paginated(false)
            ->emptyStateHeading(
                blank($this->inventarioId)
                    ? 'Selecione um inventário para visualizar o progresso.'
                    : 'Nenhum bem encontrado para este inventário.'
            );
    }

    private function atividadeDoSetor(int $setor): ?AtividadeInventario
    {
        if ($this->atividadesPorSetor === null) {
            $this->atividadesPorSetor = blank($this->inventarioId)
                ? collect()
                : AtividadeInventario::query()
                    ->doInventario($this->inventarioId)
                    ->get()
                    ->keyBy('setor');
        }

        return $this->atividadesPorSetor->get($setor);
    }

    private function getQuery(): Builder
    {
        return BemMovel::query()
            ->when(
                blank($this->inventarioId),
                fn (Builder $query) => $query->whereRaw('1 = 0'),
                fn (Builder $query) => $query->where('id_inventario', $this->inventarioId)
            )
            ->whereIn('SituacaoBem', self::SITUACOES_ATIVAS)
            ->selectRaw("CONCAT(Setor, '-', IFNULL(ComplementoSetor, 0)) AS id, Setor, ComplementoSetor,
                SUM(CASE WHEN sit_inventario = 'A INVENTARIAR' OR sit_inventario IS NULL OR sit_inventario = '' THEN 1 ELSE 0 END) AS a_inventariar,
                SUM(CASE WHEN sit_inventario = 'LOCALIZADO' THEN 1 ELSE 0 END) AS localizado,
                SUM(CASE WHEN sit_inventario IN ('NAO LOCALIZADO', 'NÃO LOCALIZADO') THEN 1 ELSE 0 END) AS nao_localizado,
                SUM(CASE WHEN sit_inventario = 'EM TRANSFERENCIA' THEN 1 ELSE 0 END) AS em_transferencia,
                SUM(CASE WHEN sit_inventario IN ('LOCALIZADO', 'EM TRANSFERENCIA') THEN 1 ELSE 0 END) AS inventariado,
                COUNT(*) AS total")
            ->groupBy('Setor', 'ComplementoSetor');
    }

    public function render(): View
    {
        return view('livewire.support.table');
    }
}
