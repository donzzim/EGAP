<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Models\Cadastro\ContaContabil;
use App\Models\Cadastro\CentroCusto;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Cadastro\DescricaoDetalhada;
use App\Models\Cadastro\Setores;
use App\Models\Cadastro\DescricaoResumida;
use App\Models\Almoxarifado\SituacaoPedido;

class RelatoriosGerais extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.relatorios-gerais';
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Relatórios Gerais';
    protected static ?string $title = 'Relatórios Gerais';
    protected static ?string $slug = 'relatorios-gerais';
    protected static ?string $navigationGroup = 'Relatórios';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    const SEM_FILTROS = [
        'bens_sem_tr_validos', 'diferenca_contabil', 'estatico_acuracia_documental',
        'estoque_atual', 'qtd_material_setor', 'qtd_insumos_impressao',
        'qtd_material_consumo_unidade', 'aquisicao_materiais_comarca',
        'aquisicao_materiais_estoque_visivel', 'estatistico_consumo_almoxarifado_meta',
    ];

    const FILTROS_EXTRAS = [
        'tabela_10'                      => ['unidade_gestora'],
        'depreciacao_mensal_cc'          => ['centro_custo'],
        'depreciacao_mensal_imoveis_cc'  => ['centro_custo'],
        'resumo_inventario_almoxarifado_cc' => ['centro_custo'],
    ];

    const FILTROS_PERSONALIZADOS = [
        'bens_patrimoniais'              => ['situacao_contabil', 'numero_processo', 'nota_fiscal', 'grupo', 'acuracia'],
        'media_consumo_material'         => ['data', 'materiais'],
        'pedidos_bens_permanentes'           => ['data', 'pedidos'],
        'pedidos_bens_permanentes_validados' => ['data', 'pedidos'],
        'pedidos_bens_consumo_duravel'       => ['data', 'pedidos'],
    ];

    const FILTROS_COMUNS = ['data', 'situacao_contabil', 'conta_contabil'];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Relatórios Gerais')
                    ->schema([
                        Select::make('relatorio')
                            ->label('Selecione o relatório')
                            ->options($this->getOpcoesRelatorios())
                            ->searchable()
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                    ]),

                Section::make('Filtros do Relatório')
                    ->schema($this->getFiltrosSchema())
                    ->columns(2)
                    ->visible(fn (Get $get) => filled($get('relatorio'))),

                Section::make('Ações')
                    ->schema($this->getAcoesSchema())
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    private function getFiltrosAtivos(string $relatorio): array
    {
        if (in_array($relatorio, self::SEM_FILTROS)) {
            return [];
        }

        if (isset(self::FILTROS_PERSONALIZADOS[$relatorio])) {
            return self::FILTROS_PERSONALIZADOS[$relatorio];
        }

        return array_merge(self::FILTROS_COMUNS, self::FILTROS_EXTRAS[$relatorio] ?? []);
    }

    private function temFiltro(Get $get, string $filtro): bool
    {
        $relatorio = $get('relatorio');
        return $relatorio && in_array($filtro, $this->getFiltrosAtivos($relatorio));
    }

    private function getFiltrosSchema(): array
    {
        return [
            DatePicker::make('data_inicio')
                ->label('Data Início')
                ->displayFormat('d/m/Y')
                ->native(false)
                ->required()
                ->visible(fn (Get $get) => $this->temFiltro($get, 'data')),

            DatePicker::make('data_termino')
                ->label('Data término')
                ->displayFormat('d/m/Y')
                ->native(false)
                ->required()
                ->visible(fn (Get $get) => $this->temFiltro($get, 'data')),

            Select::make('situacao_contabil')
                ->label('Situação Contábil')
                ->options(['Todos' => 'Todos', 'LOCALIZADO' => 'LOCALIZADO', 'NÃO LOCALIZADO' => 'NÃO LOCALIZADO'])
                ->default('Todos')
                ->visible(fn (Get $get) => $this->temFiltro($get, 'situacao_contabil')),

            Select::make('conta_contabil')
                ->label('Conta contábil')
                ->options(fn () => ContaContabil::selectRaw("id, CONCAT(codigo, ' - ', titulo) as label")->pluck('label', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $this->temFiltro($get, 'conta_contabil')),

            Select::make('unidade_gestora')
                ->label('Unidade Gestora')
                ->options(['Todos' => 'Todos', 'TJ' => 'TJ'])
                ->default('Todos')
                ->visible(fn (Get $get) => $this->temFiltro($get, 'unidade_gestora')),

            Select::make('centro_custo')
                ->label('Centro de Custo')
                ->options(fn () => CentroCusto::selectRaw("codigo, CONCAT(codigo, ' - ', descricao) as label")->pluck('label', 'codigo'))
                ->searchable()
                ->visible(fn (Get $get) => $this->temFiltro($get, 'centro_custo')),

            TextInput::make('numero_processo')
                ->label('Número do Processo')
                ->visible(fn (Get $get) => $this->temFiltro($get, 'numero_processo')),

            TextInput::make('nota_fiscal')
                ->label('Nota Fiscal')
                ->visible(fn (Get $get) => $this->temFiltro($get, 'nota_fiscal')),

            Select::make('grupo')
                ->label('Grupo')
                ->options(['A' => 'Inventariado a partir de 2015', 'B' => 'Inventariado antes de 2015', 'C' => 'Inventário online', 'D' => 'A inventariar'])
                ->visible(fn (Get $get) => $this->temFiltro($get, 'grupo')),

            Select::make('acuracia')
                ->label('Acurácia')
                ->options(fn () => BemMovel::whereNotNull('acuracia')->where('acuracia', '!=', '')->distinct()->pluck('acuracia', 'acuracia'))
                ->searchable()
                ->visible(fn (Get $get) => $this->temFiltro($get, 'acuracia')),

            Select::make('materiais')
                ->label('Materiais')
                ->options(fn () => DescricaoDetalhada::where('item_estoque', 1)->pluck('descricao_detalhada', 'id'))
                ->searchable()
                ->placeholder('Selecione os materiais...')
                ->visible(fn (Get $get) => $this->temFiltro($get, 'materiais')),

            Select::make('unidade_judiciaria')
                ->label('Unidade Judiciária')
                ->options(fn () => Setores::pluck('Setor', 'id'))
                ->searchable()
                ->live()
                ->visible(fn (Get $get) => $this->temFiltro($get, 'pedidos')),

            Select::make('setor_pedido')
                ->label('Setor')
                ->options(fn (Get $get) => Setores::when($get('unidade_judiciaria'), fn ($q, $v) => $q->where('CodigoPai', $v))->pluck('Setor', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $this->temFiltro($get, 'pedidos')),

            Select::make('material_pedido')
                ->label('Material')
                ->options(fn () => DescricaoResumida::selectRaw("id, CONCAT(Descricao, ' [', id_tipo_material, ']') as label")->pluck('label', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $this->temFiltro($get, 'pedidos')),

            Select::make('situacao_pedido')
                ->label('Situação Material')
                ->options(fn () => SituacaoPedido::pluck('Descricao', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $this->temFiltro($get, 'pedidos')),
        ];
    }

    private function getAcoesSchema(): array
    {
        return [
            Actions::make([
                Action::make('imprimir')
                    ->label('Imprimir')
                    ->color('info')
                    ->icon('heroicon-o-printer')
                    ->action(function () {
                        $url = route('relatorios.gerais.imprimir', $this->form->getState());
                        $this->js("window.open('{$url}', '_blank');");
                    }),

                Action::make('excel')
                    ->label('Excel')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {}),
            ]),
        ];
    }

    private function getOpcoesRelatorios(): array
    {
        return [
            'TCE' => [
                'tabela_10' => 'TCE IN 34/43 - Tabela 10 - Resumo do Inventário de Bens Móveis',
                'tabela_11' => 'TCE IN 34/43 - Tabela 11 - Demonstrativo Analítico das Entradas e Saídas de Bens Móveis',
                'tabela_12' => 'TCE IN 34/43 - Tabela 12 - Resumo do Inventário de Bens Imóveis',
                'tabela_13' => 'TCE IN 34/43 - Tabela 13 - Demonstrativo Analítico das Entradas e Saídas de Bens Imóveis',
                'tabela_14' => 'TCE IN 34/43 - Tabela 14 - Resumo do Inventário do Almoxarifado - Material de Consumo',
                'tabela_15' => 'TCE IN 34/43 - Tabela 15 - Demonstrativo Analítico Entradas/Saídas Almoxarifado Consumo',
                'tabela_16' => 'TCE IN 34/43 - Tabela 16 - Resumo do Inventário do Almoxarifado - Material Permanente',
                'tabela_17' => 'TCE IN 34/43 - Tabela 17 - Demonstrativo Analítico Entradas/Saídas Almoxarifado Permanente',
            ],
            'BENS MÓVEIS' => [
                'bens_incorporados'                => 'Bens Incorporados [Data da Incorporação]',
                'bens_baixados'                    => 'Bens Baixados [Data da Baixa]',
                'bens_baixados_por_processo'       => 'Bens Baixados por Processo [Data da Baixa]',
                'bens_conciliados'                 => 'Bens Conciliados [Data da Reavaliação]',
                'analitico_contabil'               => 'Analítico Contábil [Data de Incorporação]',
                'depreciacao_mensal'               => 'Depreciação Mensal [Data da Disponibilização]',
                'depreciacao_mensal_cc'            => 'Depreciação Mensal por Centro de Custo [Data da Disponibilização]',
                'bens_patrimoniais'                => 'Bens Patrimoniais',
                'inventario_bens_moveis'           => 'Inventário dos Bens Móveis',
                'inventario_bens_moveis_detalhado' => 'Inventário dos Bens Móveis Detalhado',
            ],
            'BENS IMÓVEIS' => [
                'relacao_bens_imoveis'          => 'Relação dos Bens Imóveis',
                'depreciacao_mensal_imoveis'    => 'Depreciação Mensal Imóveis',
                'depreciacao_mensal_imoveis_cc' => 'Depreciação Mensal Imóveis por Centro de Custo',
                'inventario_bens_imoveis'       => 'Inventário dos Bens Imóveis',
                'ajustes_reavaliacao_imoveis'   => 'Ajustes/Reavaliação Imóveis',
                'saldo_anterior_imoveis'        => 'Saldo Anterior',
            ],
            'BENS INTANGÍVEIS' => [
                'inventario_bens_intangiveis' => 'Inventário dos Bens Intangíveis',
            ],
            'ALMOXARIFADO' => [
                'notas_fiscais_por_fornecedor'           => 'Notas Fiscais por Fornecedor',
                'balancete_contabil_analitico'           => 'Balancete Contábil - Analítico',
                'media_consumo_material'                 => 'Média de Consumo por Material',
                'pedidos_validados_setor'                => 'Pedidos Validados por Setor',
                'gasto_anual_itens_estoque'              => 'Gasto Anual com Itens de Estoque',
                'consumo_material_subelemento'           => 'Consumo de Material por Subelemento de Despesa',
                'resumo_inventario_almoxarifado_cc'      => 'Resumo do Inventário do Almoxarifado por Centro de Custo',
                'estoque_atual'                          => 'Relatório - Estoque Atual',
                'aquisicao_materiais_comarca'            => 'Aquisição de Materiais por Comarca/Tribunal',
                'aquisicao_materiais_estoque_visivel'    => 'Aquisição de Materiais - Item Estoque/Visível',
                'estatistico_consumo_almoxarifado_meta'  => 'Relatório Estatístico de Consumo do Almoxarifado - Meta',
            ],
            'PEDIDOS' => [
                'pedidos_bens_permanentes'           => 'Pedidos - Bens Permanentes',
                'pedidos_bens_permanentes_validados' => 'Pedidos - Bens Permanentes Validados',
                'pedidos_bens_consumo_duravel'       => 'Pedidos - Bens de Consumo Duráveis',
                'qtd_material_setor'                 => 'Quantidade de Materiais por Setor',
                'qtd_insumos_impressao'              => 'Quantidade de Insumos de Impressão por Setor',
                'qtd_material_consumo_unidade'       => 'Materiais de Consumo por Setor',
            ],
            'BENS PERMANENTES' => [
                'bens_sem_tr_validos'          => 'Bens sem TR Válidos Vinculado',
                'diferenca_contabil'           => 'Relatório de Diferença Contábil',
                'estatico_acuracia_documental' => 'Relatório Estático de Acurácia Documental',
            ],
        ];
    }
}
