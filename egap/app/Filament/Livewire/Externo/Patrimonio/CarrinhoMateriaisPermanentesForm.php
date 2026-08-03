<?php

namespace App\Filament\Livewire\Externo\Patrimonio;

use App\Filament\Livewire\Externo\Almoxarifado\CarrinhoMateriaisConsumoForm;
use App\Models\Almoxarifado\Pedidos;
use App\Models\Cadastro\ComplementoSetor;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use App\Services\Mobile\PedidosMobileService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

/**
 * Carrinho e envio do pedido de materiais permanentes do Ambiente Externo
 * (legado: pedidos.php), companheiro de {@see MateriaisPermanentesTable}.
 *
 * "Dados do pedido" segue o mesmo padrão de
 * {@see CarrinhoMateriaisConsumoForm}
 * (Solicitante/Unidade/Setor/Complemento escolhidos manualmente, sem depender
 * de sessão de setor).
 *
 * O pedido em si é criado reaproveitando {@see PedidosMobileService::criarPedido()}:
 * a mesma regra de negócio (formatação da justificativa de adição/substituição,
 * cálculo do valor do bem, registro de fases) já usada pelo aplicativo mobile
 * para o tipo "permanente", evitando duplicar essa lógica aqui.
 */
class CarrinhoMateriaisPermanentesForm extends Component implements HasForms
{
    use InteractsWithForms;

    /** @var array<int, array{material_id: int, descricao: string, quantidade: int, tipo_atendimento: string, patrimonio_substituido: ?string, justificativa: string, preco_unitario: float}> */
    public array $carrinho = [];

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getDefaultFormState());
    }

    #[On('item-adicionado-ao-carrinho-permanente')]
    public function onItemAdicionado(
        int $materialId,
        string $descricao,
        int $quantidade,
        string $tipoAtendimento,
        ?string $patrimonioSubstituido,
        string $justificativa,
        float $precoUnitario,
    ): void {
        $this->carrinho[] = [
            'material_id' => $materialId,
            'descricao' => $descricao,
            'quantidade' => $quantidade,
            'tipo_atendimento' => $tipoAtendimento,
            'patrimonio_substituido' => $patrimonioSubstituido,
            'justificativa' => $justificativa,
            'preco_unitario' => $precoUnitario,
        ];
    }

    public function removerItem(int $index): void
    {
        unset($this->carrinho[$index]);
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
                            //->preload()
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
                            //->preload()
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
                            //->preload()
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
                            //->preload()
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

        $scope = [
            'user_id' => (int) $data['Solicitante'],
            'id_egap' => (int) $data['Solicitante'],
            'setor' => (int) $data['Setor'],
            'unidade_judiciaria' => (int) $data['UnidadeJudiciaria'],
        ];

        try {
            $pedido = app(PedidosMobileService::class)->criarPedido($scope, [
                'tipo' => 'permanente',
                'complemento_setor_id' => $data['ComplementoSetor'],
                'itens' => collect($this->carrinho)
                    ->map(fn (array $item): array => [
                        'material_id' => $item['material_id'],
                        'quantidade' => $item['quantidade'],
                        'tipo_atendimento' => $item['tipo_atendimento'],
                        'justificativa' => $item['justificativa'],
                        'patrimonio_substituido' => $item['patrimonio_substituido'],
                    ])
                    ->all(),
            ]);

            Notification::make()
                ->title("Pedido #{$pedido->id} criado com sucesso.")
                ->body('O pedido e os itens foram enviados à Seção de Patrimônio.')
                ->success()
                ->send();

            $this->limparCarrinho();
            $this->form->fill($this->getDefaultFormState());

            $this->dispatch('pedido-enviado');
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Não foi possível enviar o pedido.')
                ->body((string) collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Erro ao enviar pedido.')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
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

    public function render(): View
    {
        return view('livewire.externo.patrimonio.carrinho-pedido-permanente-form');
    }
}
