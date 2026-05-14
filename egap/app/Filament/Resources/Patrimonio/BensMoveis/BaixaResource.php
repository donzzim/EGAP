<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis;

use App\Filament\Egap\Clusters\PatrimonioCluster;
use App\Filament\Egap\Resources\Patrimonio\BensMoveis\BaixaResource\Pages;
use App\Models\Egap\Patrimonio\BensMoveis\Baixa;
use App\Models\Egap\Patrimonio\BensMoveis\BemMovel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class BaixaResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Baixa::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Baixa de bens';
    protected static ?string $modelLabel = 'Baixa';
    protected static ?int $navigationSort = 5;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados da Baixa')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('NumeroProcesso')
                                ->label('Processo Nº')
                                ->options(function () {
                                    return Baixa::query()
                                        ->whereNotNull('NumeroProcesso')
                                        ->distinct()
                                        ->pluck('NumeroProcesso', 'NumeroProcesso')
                                        ->mapWithKeys(fn ($val) => [(string)$val => (string)$val])
                                        ->toArray();
                                })
                                ->searchable()
                                ->required()
                                ->hint('Selecione um processo existente'),

                            DatePicker::make('DataBaixa')
                                ->label('Data da Baixa')
                                ->default(now())
                                ->required(),

                            TextInput::make('Requisitante')
                                ->label('Requisitante'),

                            TextInput::make('RequisitanteCnpj')
                                ->label('CNPJ'),
                        ]),
                        Textarea::make('Endereco')
                            ->label('Endereço')
                            ->rows(2),
                        Textarea::make('Observacao')
                            ->label('Observação')
                            ->rows(3),
                    ]),

                Section::make('Materiais (Itens da Baixa)')
                    ->description('Adicione os patrimónios que serão baixados neste processo. Se abrir uma linha por engano, clique no ícone da lixeira vermelha para a remover.')
                    ->schema([
                        Repeater::make('itens')
                            ->relationship('itens')
                            ->defaultItems(1)
                            ->minItems(0)
                            ->cloneable()
                            ->schema([
                                Select::make('id_bem')
                                    ->label('Património')
                                    // Busca ao digitar: filtra apenas disponíveis
                                    ->getSearchResultsUsing(function (string $search) {
                                        $query = BemMovel::query()
                                            ->where('SituacaoBem', '!=', 3);

                                        if ($search) {
                                            $query->where(function ($q) use ($search) {
                                                $q->where('NumPatrimonio', 'like', "%{$search}%")
                                                  ->orWhere('Descricao', 'like', "%{$search}%");
                                            });
                                        }

                                        return $query->limit(50)
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                $label = $item->NumPatrimonio . ($item->Descricao ? " - {$item->Descricao}" : "");
                                                return [$item->id => (string) $label];
                                            });
                                    })
                                    // Exibe o label correto ao carregar (edit) — busca pelo id independente do status
                                    ->getOptionLabelUsing(function ($value) {
                                        $item = BemMovel::find($value);
                                        if (! $item) return $value;
                                        return $item->NumPatrimonio . ($item->Descricao ? " - {$item->Descricao}" : "");
                                    })
                                    ->searchable()
                                    ->required()
                                    ->distinct()
                                    ->columnSpan(3),

                                Select::make('id_situacao')
                                    ->label('Status')
                                    ->options([
                                        3 => 'Baixado',
                                        2 => 'Inservível',
                                    ])
                                    ->default(3)
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->createItemButtonLabel('Adicionar outro bem')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('NumeroProcesso')
                    ->label('Processo Nº')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('DataBaixa')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('Requisitante')
                    ->label('Requisitante')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('itens_count')
                    ->counts('itens')
                    ->label('Qtd. Materiais'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBaixas::route('/'),
            'create' => Pages\CreateBaixa::route('/create'),
            'edit' => Pages\EditBaixa::route('/{record}/edit'),
        ];
    }
}
