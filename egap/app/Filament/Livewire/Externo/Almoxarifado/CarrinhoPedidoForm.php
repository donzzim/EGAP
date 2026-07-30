<?php

namespace App\Filament\Livewire\Externo\Almoxarifado;

use App\Models\Almoxarifado\FasePedido;
use App\Models\Almoxarifado\ItemPedido;
use App\Models\Almoxarifado\Pedidos;
use App\Models\Cadastro\ComplementoSetor;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class CarrinhoPedidoForm extends Component implements HasForms
{
    use InteractsWithForms;

    protected const STATUS_EM_ANALISE = 6;

    protected const SETOR_ALMOXARIFADO = 799;

    /** @var array<int, array{material_id: int, descricao_resumida_id: int, descricao: string, quantidade: int, preco_unitario: float}> */
    public array $carrinho = [];

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getDefaultFormState());
    }

    #[On('item-adicionado-ao-carrinho')]
    public function onItemAdicionado(int $materialId, int $descricaoResumidaId, string $descricao, int $quantidade, float $precoUnitario): void
    {
        $this->carrinho[$materialId] = [
            'material_id' => $materialId,
            'descricao_resumida_id' => $descricaoResumidaId,
            'descricao' => $descricao,
            'quantidade' => $quantidade,
            'preco_unitario' => $precoUnitario,
        ];
    }

    public function removerItem(int $materialId): void
    {
        unset($this->carrinho[$materialId]);
    }

    public function limparCarrinho(): void
    {
        $this->carrinho = [];
    }

    public function getSubtotalCarrinhoProperty(): float
    {
        return collect($this->carrinho)
            ->sum(fn (array $item): float => $item['quantidade'] * $item['preco_unitario']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(12)
                    ->schema([
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

                        Textarea::make('justificativa')
                            ->label('Justificativa')
                            ->required()
                            ->rows(4)
                            ->maxLength(300)
                            ->placeholder('Descreva a necessidade do pedido.')
                            ->columnSpan(12),
                    ]),
            ])
            ->statePath('data');
    }

    public function enviarPedido(): void
    {
        if ($this->carrinho === []) {
            Notification::make()
                ->title('Adicione ao menos um material ao carrinho antes de enviar.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        try {
            $pedido = DB::transaction(function () use ($data): Pedidos {
                $pedido = Pedidos::query()->create([
                    'Solicitante' => $data['Solicitante'],
                    'UnidadeJudiciaria' => $data['UnidadeJudiciaria'],
                    'Setor' => $data['Setor'],
                    'idSituacao' => self::STATUS_EM_ANALISE,
                    'setor_responsavel' => self::SETOR_ALMOXARIFADO,
                    'Observacao' => $data['justificativa'],
                    'ComplementoSetor' => $data['ComplementoSetor'],
                ]);

                $this->registrarFase($pedido, null, 'Pedido criado via portal externo.');

                foreach ($this->carrinho as $item) {
                    /** @var ItemPedido $itemPedido */
                    $itemPedido = $pedido->itens()->create([
                        'QuantidadeMaterial' => $item['quantidade'],
                        'QuantidadeMaterialAtendida' => 0,
                        'material' => $item['descricao_resumida_id'],
                        'DescricaoDetalhada' => $item['material_id'],
                        'situacao' => self::STATUS_EM_ANALISE,
                        'valor_material' => $item['preco_unitario'],
                    ]);

                    $this->registrarFase($pedido, $itemPedido, 'Item incluído no pedido via portal externo.');
                }

                return $pedido;
            });

            Notification::make()
                ->title("Pedido #{$pedido->id} criado com sucesso.")
                ->body('O pedido e os itens foram enviados ao almoxarifado.')
                ->success()
                ->send();

            $this->limparCarrinho();
            $this->form->fill($this->getDefaultFormState());

            $this->dispatch('pedido-enviado');
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Erro ao enviar pedido.')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function registrarFase(Pedidos $pedido, ?ItemPedido $item, string $descricao): void
    {
        FasePedido::query()->create([
            'idSituacao' => self::STATUS_EM_ANALISE,
            'Descricao' => $descricao,
            'id_pedido' => $pedido->id,
            'id_itempedido' => $item?->id,
            'id_descricaoresumida' => $item?->material,
            'id_descricaodetalhada' => $item?->DescricaoDetalhada,
            'quantidade' => $item?->QuantidadeMaterial,
        ]);
    }

    protected function getDefaultFormState(): array
    {
        return [
            'Solicitante' => null,
            'UnidadeJudiciaria' => null,
            'Setor' => null,
            'ComplementoSetor' => null,
            'justificativa' => null,
        ];
    }

    public function render(): View
    {
        return view('livewire.externo.almoxarifado.carrinho-pedido-form');
    }
}
