<?php

namespace App\Filament\Egap\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use App\Models\Egap\Cadastro\ContaContabil;

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

    const RELATORIOS_COM_UNIDADE_GESTORA = [
        'tabela_10',
    ];

    const RELATORIOS_SEM_DATA = [
        'bens_patrimoniais',
        'bens_sem_tr_validos'
    ];

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
                    ->schema([
                        DatePicker::make('data_inicio')
                            ->label('Data Início')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->required()
                            ->hidden(fn (Get $get) => in_array($get('relatorio'), self::RELATORIOS_SEM_DATA)),

                        DatePicker::make('data_termino')
                            ->label('Data término')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->required()
                            ->hidden(fn (Get $get) => in_array($get('relatorio'), self::RELATORIOS_SEM_DATA)),

                        Select::make('situacao_contabil')
                            ->label('Situação Contábil')
                            ->options([
                                'Todos' => 'Todos',
                                'LOCALIZADO' => 'LOCALIZADO',
                                'NÃO LOCALIZADO' => 'NÃO LOCALIZADO',
                            ])
                            ->default('Todos'),

                        \Filament\Forms\Components\TextInput::make('numero_processo')
                            ->label('Número do Processo')
                            ->visible(fn (Get $get) => $get('relatorio') === 'bens_patrimoniais'),

                        \Filament\Forms\Components\TextInput::make('nota_fiscal')
                            ->label('Nota Fiscal')
                            ->visible(fn (Get $get) => $get('relatorio') === 'bens_patrimoniais'),

                        Select::make('materiais')
                            ->label('Materiais')
                            ->searchable()
                            ->options(fn () => \Illuminate\Support\Facades\DB::connection('egap')
                                ->table('mat_descricaodetalhada')
                                ->where('item_estoque', 1)
                                ->pluck('descricao_detalhada', 'id')
                            )
                            ->placeholder('Selecione os materiais...')
                            ->visible(fn (Get $get) => $get('relatorio') === 'media_consumo_material'),

                        Select::make('grupo')
                            ->label('Grupo')
                            ->options([
                                'A' => 'Inventariado a partir de 2015',
                                'B' => 'Inventariado antes de 2015',
                                'C' => 'Inventário online',
                                'D' => 'A inventariar'
                            ])
                            ->visible(fn (Get $get) => $get('relatorio') === 'bens_patrimoniais'),

                        Select::make('acuracia')
                            ->label('Acurácia')
                            ->options(fn () => \Illuminate\Support\Facades\DB::connection('egap')->table('mat_patrimonio')->whereNotNull('acuracia')->where('acuracia', '!=', '')->distinct()->pluck('acuracia', 'acuracia'))
                            ->searchable()
                            ->visible(fn (Get $get) => $get('relatorio') === 'bens_patrimoniais'),

                        Select::make('conta_contabil')
                            ->label('Conta contábil')
                            ->options(fn () => ContaContabil::selectRaw("id, CONCAT(codigo, ' - ', titulo) as label")->pluck('label', 'id'))
                            ->searchable()
                            ->hidden(fn (Get $get) => $get('relatorio') === 'bens_patrimoniais'),

                        Select::make('unidade_gestora')
                            ->label('Unidade Gestora')
                            ->options(['Todos' => 'Todos', 'TJ' => 'TJ'])
                            ->default('Todos')
                            ->visible(fn (Get $get) => in_array($get('relatorio'), self::RELATORIOS_COM_UNIDADE_GESTORA)),

                        Select::make('centro_custo')
                            ->label('Centro de Custo')
                            ->options(fn () => \Illuminate\Support\Facades\DB::connection('egap')->table('cad_centrocusto')->selectRaw("codigo, CONCAT(codigo, ' - ', descricao) as label")->pluck('label', 'codigo'))
                            ->searchable()
                            ->visible(fn (Get $get) => in_array($get('relatorio'), ['depreciacao_mensal_cc', 'depreciacao_mensal_imoveis_cc', 'resumo_inventario_almoxarifado_cc'])),

                        Select::make('unidade_judiciaria')
                            ->label('Unidade Judiciária')
                            ->options(fn () => \Illuminate\Support\Facades\DB::connection('egap')->table('mat_setores')->pluck('Setor', 'id'))
                            ->searchable()
                            ->live()
                            ->visible(fn (Get $get) => in_array($get('relatorio'), ['pedidos_bens_permanentes', 'pedidos_bens_permanentes_validados', 'pedidos_bens_consumo_duravel'])),

                        Select::make('setor_pedido')
                            ->label('Setor')
                            ->options(fn (Get $get) => \Illuminate\Support\Facades\DB::connection('egap')
                                ->table('mat_setores')
                                ->when($get('unidade_judiciaria'), fn($q, $v) => $q->where('CodigoPai', $v))
                                ->pluck('Setor', 'id')
                            )
                            ->searchable()
                            ->visible(fn (Get $get) => in_array($get('relatorio'), ['pedidos_bens_permanentes', 'pedidos_bens_permanentes_validados', 'pedidos_bens_consumo_duravel'])),

                        Select::make('material_pedido')
                            ->label('Material')
                            ->options(fn () => \Illuminate\Support\Facades\DB::connection('egap')
                                ->table('mat_descricaoresumida')
                                ->selectRaw("id, CONCAT(Descricao, ' [', id_tipo_material, ']') as label")
                                ->pluck('label', 'id')
                            )
                            ->searchable()
                            ->visible(fn (Get $get) => in_array($get('relatorio'), ['pedidos_bens_permanentes', 'pedidos_bens_permanentes_validados', 'pedidos_bens_consumo_duravel'])),

                        Select::make('situacao_pedido')
                            ->label('Situação Material')
                            ->options(fn () => \Illuminate\Support\Facades\DB::connection('egap')->table('ped_situacao')->pluck('Descricao', 'id'))
                            ->searchable()
                            ->visible(fn (Get $get) => in_array($get('relatorio'), ['pedidos_bens_permanentes', 'pedidos_bens_permanentes_validados', 'pedidos_bens_consumo_duravel'])),

                    ])->columns(2)
                      ->visible(fn (Get $get) => $get('relatorio') !== null),

                Section::make('Ações')
                    ->schema([
                        Actions::make([
                            Action::make('imprimir')
                                ->label('Imprimir')
                                ->color('info')
                                ->icon('heroicon-o-printer')
                                ->action(function () {
                                    $filtros = $this->form->getState();
                                    $url = route('relatorios.gerais.imprimir', $filtros);
                                    $this->js("window.open('{$url}', '_blank');");
                                }),

                            Action::make('excel')
                                ->label('Excel')
                                ->color('success')
                                ->icon('heroicon-o-document-arrow-down')
                                ->action(function () {}),
                        ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
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
                'bens_incorporados'          => 'Bens Incorporados [Data da Incorporação]',
                'bens_baixados'              => 'Bens Baixados [Data da Baixa]',
                'bens_baixados_por_processo' => 'Bens Baixados por Processo [Data da Baixa]',
                'bens_conciliados'           => 'Bens Conciliados [Data da Reavaliação]',
                'analitico_contabil'         => 'Analítico Contábil [Data de Incorporação]',
                'depreciacao_mensal'         => 'Depreciação Mensal [Data da Disponibilização]',
                'depreciacao_mensal_cc'      => 'Depreciação Mensal por Centro de Custo [Data da Disponibilização]',
                'bens_patrimoniais'          => 'Bens Patrimoniais',
                'inventario_bens_moveis'     => 'Inventário dos Bens Móveis',
                'inventario_bens_moveis_detalhado' => 'Inventário dos Bens Móveis Detalhado',
            ],
            'BENS IMÓVEIS' => [
                'relacao_bens_imoveis' => 'Relação dos Bens Imóveis',
                'depreciacao_mensal_imoveis' => 'Depreciação Mensal Imóveis',
                'depreciacao_mensal_imoveis_cc' => 'Depreciação Mensal Imóveis por Centro de Custo',
                'inventario_bens_imoveis' => 'Inventário dos Bens Imóveis',
                'ajustes_reavaliacao_imoveis' => 'Ajustes/Reavaliação Imóveis',
                'saldo_anterior_imoveis' => 'Saldo Anterior',
            ],
            'BENS INTANGÍVEIS' => [
                'inventario_bens_intangiveis' => 'Inventário dos Bens Intangíveis',
            ],
            'ALMOXARIFADO' => [
                'notas_fiscais_por_fornecedor' => 'Notas Fiscais por Fornecedor',
                'balancete_contabil_analitico' => 'Balancete Contábil - Analítico',
                'media_consumo_material'       => 'Média de Consumo por Material',
                'pedidos_validados_setor'      => 'Pedidos Validados por Setor',
                'gasto_anual_itens_estoque'    => 'Gasto Anual com Itens de Estoque',
                'consumo_material_subelemento' => 'Consumo de Material por Subelemento de Despesa',
                'resumo_inventario_almoxarifado_cc' => 'Resumo do Inventário do Almoxarifado por Centro de Custo',
            ],
            'PEDIDOS' => [
                'pedidos_bens_permanentes' => 'Pedidos - Bens Permanentes',
                'pedidos_bens_permanentes_validados' => 'Pedidos - Bens Permanentes Validados',
                'pedidos_bens_consumo_duravel' => 'Pedidos - Bens de Consumo Duráveis',
            ],
            'BENS PERMANENTES' => [
                'bens_sem_tr_validos' => 'Bens sem TR Válidos Vinculado',
            ]
        ];
    }
}
