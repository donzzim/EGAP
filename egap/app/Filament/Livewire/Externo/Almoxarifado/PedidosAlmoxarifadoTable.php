<?php

namespace App\Filament\Livewire\Externo\Almoxarifado;

use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Almoxarifado\Pedidos;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Livewire\Livewire;

/**
 * Lista os pedidos de materiais de consumo do Ambiente Externo cujo setor
 * responsável pelo atendimento é o Almoxarifado (legado: consultar_pedidos.php
 * + status_pedidos.api.php, com setorresponsavel = 799).
 *
 * O detalhe dos itens de cada pedido é aberto em modal, delegado ao
 * {@see PedidoItensModal} (legado: modal_pedidos.api.php).
 */
class PedidosAlmoxarifadoTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected const SETOR_ALMOXARIFADO = 799;

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getQuery())
            ->columns([
                TableColumns::text('id', 'Nº Pedido', isFirstColumn: true)
                    ->formatStateUsing(fn (Pedidos $record): string => "{$record->id}/{$record->date_time?->format('Y')}"),

                TableColumns::date('date_time', 'Data do Pedido'),

                TableColumns::text('solicitante_get.name', 'Solicitante')
                    ->wrap(),

                TableColumns::text('Observacao', 'Justificativa da Necessidade')
                    ->wrap()
                    ->limit(80),

                TextColumn::make('complemento_setor')
                    ->label('Complemento do Setor')
                    ->alignCenter()
                    ->default('-')
                    ->wrap()
                    ->getStateUsing(fn (Pedidos $record): ?string => $record->complementoSetor?->descricao),

                TextColumn::make('itens_count')
                    ->label('Materiais')
                    ->counts('itens')
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? '1 item' : "{$state} itens"),

                TableColumns::text('situacao.Descricao', 'Status')
                    ->badge()
                    ->color(fn (Pedidos $record): string => $this->statusColor((int) $record->idSituacao)),
            ])
            ->filters([
                SelectFilter::make('situacao_grupo')
                    ->label('Situação')
                    ->options([
                        'pendente' => 'Pendentes',
                        'atendido' => 'Atendidos',
                        'cancelado' => 'Cancelados',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'pendente' => $query->where('idSituacao', 6),
                            'atendido' => $query->whereIn('idSituacao', [3, 7]),
                            'cancelado' => $query->where('idSituacao', 4),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Action::make('ver_itens')
                    ->label('Ver itens')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (Pedidos $record): string => "Pedido Nº {$record->id}/{$record->date_time?->format('Y')}")
                    ->modalWidth('4xl')
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn (Pedidos $record): HtmlString => new HtmlString(
                        Livewire::mount(
                            'externo-almoxarifado.pedido-itens-modal',
                            ['pedidoId' => (int) $record->getKey()],
                            "pedido-itens-{$record->getKey()}",
                        )
                    )),
            ])
            ->bulkActions([])
            ->defaultSort('date_time', 'desc')
            ->emptyStateHeading('Nenhum pedido encontrado');
    }

    protected function statusColor(int $status): string
    {
        return match ($status) {
            3, 7 => 'success',
            4 => 'gray',
            5 => 'danger',
            6 => 'warning',
            default => 'gray',
        };
    }

    protected function getQuery(): Builder
    {
        return Pedidos::query()
            ->with(['solicitante_get', 'complementoSetor', 'situacao'])
            ->whereRaw('IFNULL(setor_responsavel, 1239) = ?', [self::SETOR_ALMOXARIFADO]);
    }

    public function render(): View
    {
        return view('livewire.externo.almoxarifado.pedidos-almoxarifado-table');
    }
}
