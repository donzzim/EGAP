<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis;
use App\Models\Cadastro\ContaContabil;
use App\Models\Cadastro\ElementoDespesa;
use App\Models\Cadastro\Fornecedores;
use App\Models\Cadastro\Marcas;
use App\Models\Cadastro\Modelos;
use App\Models\Cadastro\Setores;
use App\Models\Cadastro\SituacaoBem;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class IncorporarBensResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = BemMovel::class;
  protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
  protected static ?string $navigationGroup = 'Bens Móveis';
  protected static ?string $navigationLabel = 'Incorporar bens';
  protected static ?string $label = 'Incorporação de Bens';
  protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Incorporar bens')
                    ->schema([
                        Grid::make(3)->schema([
                           Select::make('modelo_id')
                            ->label('Basear incorporação em um bem existente (Busca por Pat/Desc/NF)')
                            ->options(function () {
                                return BemMovel::query()
                                    ->latest('id')
                                    ->limit(500)
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        $pat  = $item->NumPatrimonio ?? 'S/P';
                                        $nf   = $item->NotaFiscal    ?? 'S/N';
                                        $desc = $item->Descricao      ?? 'Sem Descrição';
                                        return [$item->id => "Pat. Nº{$pat} - {$desc} - NF {$nf}"];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->columnSpan(2)
                            ->live()
                            ->createOptionForm(self::getEsquemaNoveAbas())
                            ->createOptionUsing(function (array $data): int {
                                $bem = BemMovel::create($data);
                                return $bem->id;
                            }),

                            TextInput::make('quantidade_total')
                                ->label('Quantidade Total')
                                ->numeric()
                                ->live()
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calcularPatrimonioFinal($get, $set)),
                        ]),
                    ]),

                Section::make('Faixa de patrimônio')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('num_pat_inicial')
                                ->label('Núm. Pat. Inicial')
                                ->numeric()
                                ->live()
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calcularPatrimonioFinal($get, $set))
                                ->required(),

                            TextInput::make('num_pat_final')
                                ->label('Núm. Pat. Final')
                                ->numeric()
                                ->readOnly()
                                ->helperText('Calculado automaticamente'),

                            TextInput::make('qtde_calculada')
                                ->label('Qtde')
                                ->numeric()
                                ->readOnly(),
                        ]),
                    ]),
            ]);
    }

    public static function calcularPatrimonioFinal(Get $get, Set $set): void
    {
        $inicial    = (int) $get('num_pat_inicial');
        $quantidade = (int) $get('quantidade_total');

        if ($inicial > 0 && $quantidade > 0) {
            $set('num_pat_final', $inicial + $quantidade - 1);
            $set('qtde_calculada', $quantidade);
        } else {
            $set('num_pat_final', null);
            $set('qtde_calculada', 0);
        }
    }

    public static function getEsquemaNoveAbas(): array
    {
        return [
            Tabs::make('Novo Cadastro de Bem')
                ->tabs([

                    Tabs\Tab::make('1. Patrimônio')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('NumPatrimonio')->label('Patrimônio')->required(),
                                TextInput::make('TomboSmarapd')->label('Patrimônio (Conciliação)'),
                                TextInput::make('NumTomboSmarapd')->label('Patrimônio (sem cód. barras)'),
                                TextInput::make('NumerodeSerie')->label('Número de Série'),
                            ]),
                        ]),

                    Tabs\Tab::make('2. Descrição do Bem')
                        ->schema([
                            Textarea::make('Descricao')
                                ->label('Descrição Detalhada')
                                ->required()
                                ->rows(3)
                                ->columnSpanFull(),

                            Grid::make(2)->schema([
                                Select::make('Marca')
                                    ->label('Marca')
                                    ->options(fn () => Marcas::pluck('descricao', 'id'))
                                    ->searchable()
                                    ->live(),

                                Select::make('Modelo')
                                    ->label('Modelo')
                                    ->options(fn () => Modelos::pluck('descricao', 'id'))
                                    ->searchable(),

                                Select::make('TipodoBem')
                                    ->label('Tipo')
                                    ->options(['Móveis' => 'Móveis', 'Imóveis' => 'Imóveis', 'Veículos' => 'Veículos']),

                                Select::make('EstadodeConservacao')
                                    ->label('Estado')
                                    ->options(['ÓTIMO' => 'ÓTIMO', 'BOM' => 'BOM', 'REGULAR' => 'REGULAR', 'RUIM' => 'RUIM', 'SUCATA' => 'SUCATA']),
                            ]),
                        ]),

                    Tabs\Tab::make('3. Localização')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('UnidadeJudiciaria')
                                    ->label('Unidade Judiciária')
                                    ->options(fn () => Setores::pluck('Setor', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('Setor', null)),

                                Select::make('Setor')
                                    ->label('Setor')
                                    ->options(function (Get $get) {
                                        $unidadeId = $get('UnidadeJudiciaria');
                                        if (!$unidadeId) return [];
                                        return Setores::where('CodigoPai', $unidadeId)->pluck('Setor', 'id');
                                    })
                                    ->searchable(),

                                TextInput::make('ComplementoSetor')->label('Complemento'),
                                TextInput::make('AndarSetor')->label('Andar'),
                            ]),
                        ]),

                    Tabs\Tab::make('4. Informações da Nota')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('Fornecedor')
                                    ->label('Fornecedor')
                                    ->options(fn () => Fornecedores::pluck('NomeFornecedor', 'id'))
                                    ->searchable(),

                                TextInput::make('NotaFiscal')->label('N° Nota Fiscal'),
                                DatePicker::make('DataCadastro')->label('Data do Cadastro'),
                                TextInput::make('ValorAquisicao')->label('Valor Aquisição')->numeric()->prefix('R$'),
                            ]),
                        ]),

                    Tabs\Tab::make('6. Contábil')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('ContaContabil')
                                    ->label('Conta Contábil')
                                    ->options(fn () => ContaContabil::pluck('titulo', 'id'))
                                    ->searchable(),

                               Select::make('Produto')
                                ->label('Elemento de Despesa')
                                ->options(fn () => ElementoDespesa::pluck('DescricaodaClasse', 'id'))
                                ->searchable(),
                                TextInput::make('VidaUtilSIAFI')->label('Vida Útil SIAFI'),
                                TextInput::make('DepreciacaoAcumulada')->label('Depreciação Acumulada')->numeric()->prefix('R$'),
                            ]),
                        ]),

                    Tabs\Tab::make('9. Situação')
                        ->schema([
                            Grid::make(2)->schema([
                                DatePicker::make('DataBaixa')->label('Data da Baixa'),
                                TextInput::make('ProcessoBaixa')->label('Processo de Baixa'),

                                Select::make('SituacaoBem')
                                    ->label('Situação')
                                    ->options(fn () => SituacaoBem::pluck('descricao', 'id'))
                                    ->searchable()
                                    ->required(),
                            ]),
                        ]),

                ])->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('NumPatrimonio')->label('Patrimônio')->sortable(),
                Tables\Columns\TextColumn::make('Descricao')->label('Descrição')->limit(50),
                Tables\Columns\TextColumn::make('date_time')->label('Incorporado em')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('unidadeJudiciariaRel.Setor')->label('Unidade'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => BensMoveis\IncorporarBensResource\Pages\ListIncorporarBens::route('/'),
            'create' => BensMoveis\IncorporarBensResource\Pages\CreateIncorporarBens::route('/create'),
        ];
    }
}
