<?php

namespace App\Filament\Livewire\Externo\Patrimonio;

use App\Filament\Livewire\Externo\Almoxarifado\MateriaisConsumoTable;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Cadastro\DescricaoResumida;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Lista os materiais permanentes (mat_descricaoresumida.id_tipo_material = 'P')
 * disponíveis para requisição (legado: pedidos.php), companheiro de
 * {@see CarrinhoMateriaisPermanentesForm}, que guarda o carrinho e envia o pedido.
 *
 * Segue o mesmo padrão de
 * {@see MateriaisConsumoTable}:
 * a quantidade é digitada direto na tabela; o botão "Adicionar" só abre um
 * modal para os dados que não cabem em coluna (Tipo de solicitação e
 * Justificativa — e o nº do patrimônio, quando for Substituição).
 */
class MateriaisPermanentesTable extends Component implements HasForms, HasTable
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

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getQuery())
            ->columns([
                TableColumns::text('Descricao', 'Material', isFirstColumn: true)
                    ->wrap(),

                TableColumns::money('valor', 'Valor estimado')
                    ->searchable(false)
                    ->sortable(false),

                TextInputColumn::make('quantidade')
                    ->label('Quantidade')
                    ->alignCenter()
                    ->type('number')
                    ->rules(['nullable', 'integer', 'min:0'])
                    ->state(fn (DescricaoResumida $record): ?int => $this->quantidades[$record->id] ?? null)
                    ->updateStateUsing(function (DescricaoResumida $record, $state): void {
                        $this->quantidades[$record->id] = filled($state) ? (int) $state : null;
                    }),
            ])
            ->actions([
                $this->adicionarAction(),
            ])
            ->bulkActions([])
            ->defaultSort('Descricao')
            ->emptyStateHeading('Nenhum material disponível no momento');
    }

    private function adicionarAction(): Action
    {
        return Action::make('adicionar')
            ->label('Adicionar')
            ->button()
            ->icon('heroicon-m-plus')
            ->modalHeading(fn (DescricaoResumida $record): string => "Adicionar - {$record->Descricao}")
            ->modalSubmitActionLabel('Adicionar ao carrinho')
            ->form([
                Radio::make('tipo_atendimento')
                    ->label('Tipo de solicitação')
                    ->options([
                        'adicao' => 'Adição',
                        'substituicao' => 'Substituição',
                    ])
                    ->inline()
                    ->required()
                    ->live()
                    ->default('adicao'),

                TextInput::make('patrimonio_substituido')
                    ->label('Nº do(s) patrimônio(s) a substituir')
                    ->placeholder('Ex: 12345, 12346')
                    ->maxLength(120)
                    //->options(fn() : array => BemMovel::all()->pluck('NumPatrimonio', 'id')->toArray())
                    ->visible(fn (Get $get): bool => $get('tipo_atendimento') === 'substituicao')
                    ->required(fn (Get $get): bool => $get('tipo_atendimento') === 'substituicao'),

                Textarea::make('justificativa')
                    ->label('Justificativa')
                    ->required()
                    ->rows(4)
                    ->maxLength(300)
                    ->placeholder('Descreva a necessidade do material.'),
            ])
            ->action(function (DescricaoResumida $record, array $data): void {
                $quantidade = (int) ($this->quantidades[$record->id] ?? 0);

                if ($quantidade < 1) {
                    Notification::make()
                        ->title('Informe uma quantidade válida antes de adicionar.')
                        ->warning()
                        ->send();

                    return;
                }

                $this->dispatch(
                    'item-adicionado-ao-carrinho-permanente',
                    materialId: $record->id,
                    descricao: $record->Descricao,
                    quantidade: $quantidade,
                    tipoAtendimento: $data['tipo_atendimento'],
                    patrimonioSubstituido: $data['patrimonio_substituido'] ?? null,
                    justificativa: $data['justificativa'],
                    precoUnitario: (float) ($record->valor ?? 0),
                );

                Notification::make()
                    ->title('Item adicionado ao carrinho.')
                    ->success()
                    ->send();

                unset($this->quantidades[$record->id]);
            });
    }

    private function getQuery(): Builder
    {
        return DescricaoResumida::query()
            ->where('id_tipo_material', 'P')
            ->select('mat_descricaoresumida.*')
            ->addSelect(['valor' => BemMovel::query()
                ->selectRaw('IF(DatadeIncorporacao > DatadaReavaliacao, ValorAquisicao, ValordaReavaliacao)')
                ->whereColumn('DescricaoResumidadoBem', 'mat_descricaoresumida.id')
                ->where('ValorAquisicao', '<>', 0)
                ->where('ValordaReavaliacao', '<>', 0)
                ->orderByDesc('DatadaReavaliacao')
                ->limit(1),
            ]);
    }

    public function render(): View
    {
        return view('livewire.externo.patrimonio.materiais-permanentes-table');
    }
}
