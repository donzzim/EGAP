<?php

namespace App\Filament\Clusters\ExternoCluster\Almoxarifado;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Concerns\ResolveUsuarioExterno;
use App\Models\Almoxarifado\FasePedido;
use App\Models\Almoxarifado\ItemPedido;
use App\Models\Almoxarifado\MovimentacaoEstoque;
use App\Models\Almoxarifado\Pedidos;
use App\Models\Cadastro\ComplementoSetor;
use App\Models\Cadastro\DescricaoDetalhada;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Base compartilhada pelas páginas de requisição de materiais de consumo (tipo 'C')
 * e de consumo duráveis (tipo 'D') do Ambiente Externo. Reproduz, em Filament, o
 * fluxo do legado pedidos_consumo.php: setor/unidade fixos pela lotação do usuário
 * logado, seleção de materiais com quantidade, complemento de setor de destino e
 * justificativa, gerando um `ped_pedidos` + `ped_itempedido` num único envio.
 */
abstract class RequisicaoMateriaisConsumoPage extends Page implements HasForms
{
    use InteractsWithForms;
    use ResolveUsuarioExterno;

    protected const STATUS_EM_ANALISE = 6;

    protected const SETOR_ALMOXARIFADO = 799;

    protected static ?string $cluster = ExternoCluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Almoxarifado';

    public ?array $data = [];

    abstract protected function tipoMaterial(): string;

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(): void
    {
        if (! $this->lotacaoAtual()) {
            Notification::make()
                ->title('Lotação não encontrada')
                ->body('Não foi possível identificar o setor e a unidade judiciária do seu usuário. Contate o suporte antes de enviar um pedido.')
                ->danger()
                ->persistent()
                ->send();
        }

        $this->form->fill($this->getDefaultFormState());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Local da solicitação')
                    ->description('Identificado automaticamente pela lotação do seu usuário.')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                Placeholder::make('unidade_judiciaria_label')
                                    ->label('Unidade judiciária')
                                    ->content(fn (): string => $this->lotacaoAtual()?->unidadeJudiciaria?->UnidadeOrganizacional ?? 'Não identificada')
                                    ->columnSpan(6),

                                Placeholder::make('setor_label')
                                    ->label('Setor')
                                    ->content(fn (): string => $this->lotacaoAtual()?->setorRef?->Setor ?? 'Não identificado')
                                    ->columnSpan(6),
                            ]),
                    ]),

                Section::make('Materiais solicitados')
                    ->description('Adicione os materiais e as quantidades desejadas.')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Repeater::make('itens')
                            ->hiddenLabel()
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('Adicionar material')
                            ->collapsible()
                            ->columns(12)
                            ->schema([
                                Select::make('material')
                                    ->label('Material')
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->getSearchResultsUsing(fn (?string $search): array => $this->materiaisOptions($search))
                                    ->getOptionLabelUsing(fn ($value): ?string => DescricaoDetalhada::query()->find($value)?->descricao_detalhada)
                                    ->columnSpan(8),

                                TextInput::make('quantidade')
                                    ->label('Quantidade')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->columnSpan(4),

                                Placeholder::make('disponibilidade')
                                    ->label('Disponibilidade')
                                    ->content(fn (Get $get): string => $this->disponibilidadeLabel($get('material')))
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Destino e justificativa')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Select::make('complemento_setor_id')
                            ->label('Complemento do setor de destino')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(fn (?string $search): array => ComplementoSetor::query()
                                ->when(filled($search), fn (Builder $query) => $query->where('descricao', 'like', "%{$search}%"))
                                ->orderBy('descricao')
                                ->limit(50)
                                ->pluck('descricao', 'id')
                                ->toArray())
                            ->getOptionLabelUsing(fn ($value): ?string => ComplementoSetor::query()->find($value)?->descricao)
                            ->columnSpanFull(),

                        Textarea::make('justificativa')
                            ->label('Justificativa')
                            ->required()
                            ->rows(4)
                            ->maxLength(300)
                            ->placeholder('Descreva a necessidade do pedido.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $lotacao = $this->lotacaoAtual();
        $usuarioEgap = $this->usuarioEgapAtual();

        if (! $lotacao || ! $usuarioEgap) {
            Notification::make()
                ->title('Não foi possível identificar seu setor/unidade judiciária.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        try {
            $this->validateBusinessRules($data);

            $pedido = DB::connection('egap')->transaction(function () use ($data, $lotacao, $usuarioEgap): Pedidos {
                $pedido = Pedidos::query()->create([
                    'date_time' => now(),
                    'Solicitante' => $usuarioEgap->id,
                    'UnidadeJudiciaria' => $lotacao->unidade_judiciaria,
                    'Setor' => $lotacao->setor,
                    'idSituacao' => self::STATUS_EM_ANALISE,
                    'setor_responsavel' => self::SETOR_ALMOXARIFADO,
                    'Observacao' => $data['justificativa'],
                    'ComplementoSetor' => $data['complemento_setor_id'],
                ]);

                $this->registrarFase($pedido, null, 'Pedido criado via portal externo.');

                $materialIds = collect($data['itens'])
                    ->pluck('material')
                    ->map(fn ($id): int => (int) $id)
                    ->all();

                $estoques = MovimentacaoEstoque::estoquesAtuais($materialIds);

                foreach ($data['itens'] as $itemData) {
                    $materialId = (int) $itemData['material'];

                    /** @var DescricaoDetalhada $material */
                    $material = DescricaoDetalhada::query()->find($materialId);
                    $estoque = $estoques->get($materialId);

                    /** @var ItemPedido $item */
                    $item = $pedido->itens()->create([
                        'date_time' => now(),
                        'QuantidadeMaterial' => (int) $itemData['quantidade'],
                        'QuantidadeMaterialAtendida' => 0,
                        'material' => $material->descricao_resumida,
                        'DescricaoDetalhada' => $material->id,
                        'situacao' => self::STATUS_EM_ANALISE,
                        'valor_material' => (float) ($estoque?->preco_medio_estoque ?? 0),
                    ]);

                    $this->registrarFase($pedido, $item, 'Item incluído no pedido via portal externo.');
                }

                return $pedido;
            });

            Notification::make()
                ->title("Pedido #{$pedido->id} criado com sucesso.")
                ->body('O pedido e os itens foram enviados ao almoxarifado.')
                ->success()
                ->send();

            $this->form->fill($this->getDefaultFormState());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Erro ao criar pedido.')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getDefaultFormState(): array
    {
        return [
            'complemento_setor_id' => null,
            'justificativa' => null,
            'itens' => [
                ['quantidade' => 1],
            ],
        ];
    }

    protected function validateBusinessRules(array $data): void
    {
        $errors = [];

        $complementoId = $data['complemento_setor_id'] ?? null;

        if (blank($complementoId) || ! ComplementoSetor::query()->whereKey($complementoId)->exists()) {
            $errors['data.complemento_setor_id'] = 'Selecione um complemento de setor válido.';
        }

        if (blank($data['justificativa'] ?? null)) {
            $errors['data.justificativa'] = 'Informe a justificativa do pedido.';
        }

        $itens = $data['itens'] ?? [];

        if (! is_array($itens) || $itens === []) {
            $errors['data.itens'] = 'Adicione ao menos um material ao pedido.';
        }

        foreach ($itens as $index => $itemData) {
            $prefix = "data.itens.{$index}";
            $materialId = (int) ($itemData['material'] ?? 0);
            $quantidade = (int) ($itemData['quantidade'] ?? 0);

            if ($materialId <= 0) {
                $errors["{$prefix}.material"] = 'Selecione um material.';

                continue;
            }

            if ($quantidade < 1) {
                $errors["{$prefix}.quantidade"] = 'Informe uma quantidade maior que zero.';
            }

            $materialValido = DescricaoDetalhada::query()
                ->whereKey($materialId)
                ->whereHas('descricao_resumida_text', fn (Builder $query) => $query->where('id_tipo_material', $this->tipoMaterial()))
                ->exists();

            if (! $materialValido) {
                $errors["{$prefix}.material"] = 'Material inválido para este tipo de requisição.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
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

    /**
     * @return array<int, string>
     */
    protected function materiaisOptions(?string $search): array
    {
        return DescricaoDetalhada::query()
            ->select(['id', 'descricao_detalhada'])
            ->whereIn('visibilidade', $this->visibilidadesPermitidas())
            ->whereHas('descricao_resumida_text', fn (Builder $query) => $query->where('id_tipo_material', $this->tipoMaterial()))
            ->when(filled($search), fn (Builder $query) => $query->where('descricao_detalhada', 'like', "%{$search}%"))
            ->orderBy('descricao_detalhada')
            ->limit(50)
            ->pluck('descricao_detalhada', 'id')
            ->toArray();
    }

    protected function disponibilidadeLabel(mixed $materialId): string
    {
        if (blank($materialId)) {
            return 'Selecione um material para ver a disponibilidade.';
        }

        $estoque = MovimentacaoEstoque::estoquesAtuais([(int) $materialId])->get((int) $materialId);
        $quantidade = (int) ($estoque?->quantidade_estoque ?? 0);
        $preco = (float) ($estoque?->preco_medio_estoque ?? 0);

        $label = "Estoque atual: {$quantidade} — Preço médio: R$ ".number_format($preco, 2, ',', '.');

        if ($this->tipoMaterial() === 'C' && $quantidade === 0) {
            $label .= ' — Material indisponível no momento.';
        }

        return $label;
    }

    /**
     * @return array<int>
     */
    protected function visibilidadesPermitidas(): array
    {
        $unidade = (int) ($this->lotacaoAtual()?->unidade_judiciaria ?? 0);

        return in_array($unidade, [766, 866], true) ? [2, 3] : [1, 3];
    }
}
