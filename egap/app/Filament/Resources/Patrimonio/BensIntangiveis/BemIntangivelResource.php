<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource\Pages;
use App\Models\Patrimonio\BensIntangiveis\BemIntangivel;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class BemIntangivelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = BemIntangivel::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';


    protected static ?string $navigationLabel = 'Bens Intangíveis';

    protected static ?string $modelLabel = 'Bem Intangível';

    protected static ?string $pluralModelLabel = 'Bens Intangíveis';

    protected static ?string $navigationGroup = 'Bens Intangíveis';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('TabsBemIntangivel')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Intangível')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Forms\Components\Section::make('Informações Principais')
                                    ->description('Dados básicos de identificação do bem intangível.')
                                    ->schema([
                                        Forms\Components\Select::make('id_tipointangivel')
                                            ->label('Tipo de Intangível')
                                            ->relationship('idTipoIntangivelRef', 'descricao')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\TextInput::make('nome')
                                            ->label('Nome')
                                            ->placeholder('Ex: Licença de Software')
                                            ->maxLength(255)
                                            ->required(),

                                        Forms\Components\Select::make('id_fabricante')
                                            ->label('Fabricante')
                                            ->relationship('idFabricanteRef', 'descricao')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Select::make('classificacao')
                                            ->label('Classificação')
                                            ->options([
                                                'Desktops' => 'Desktops',
                                                'Servidores' => 'Servidores',
                                            ])
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Detalhes Técnicos')
                                    ->schema([
                                        Forms\Components\TextInput::make('versao')
                                            ->label('Versão')
                                            ->placeholder('Ex: 2.0')
                                            ->maxLength(255)
                                            ->required(),

                                        Forms\Components\TextInput::make('quantidade')
                                            ->label('Quantidade')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Contábil')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Section::make('Classificação e Registro')
                                    ->schema([
                                        Forms\Components\Select::make('id_planocontas')
                                            ->label('Conta Contábil')
                                            ->relationship('idPlanoContasRef', 'titulo')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Select::make('id_elementodespesa')
                                            ->label('Elemento de Despesa')
                                            ->relationship('idElementoDespesaRef', 'DescricaodaClasse')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\TextInput::make('inscricao_generica')
                                            ->label('Inscrição Genérica')
                                            ->maxLength(255)
                                            ->required(),

                                        Forms\Components\TextInput::make('nota_patrimonial')
                                            ->label('Nota Patrimonial')
                                            ->maxLength(255)
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Dados da Aquisição')
                                    ->schema([
                                        Forms\Components\TextInput::make('processo_aquisicao')
                                            ->label('Processo de Aquisição')
                                            ->maxLength(255)
                                            ->required(),

                                        Forms\Components\DatePicker::make('data_aquisicao')
                                            ->label('Data de Aquisição')
                                            ->default(now())
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->required(),

                                        Forms\Components\TextInput::make('valor_aquisicao')
                                            ->label('Valor de Aquisição')
                                            ->prefix('R$')
                                            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                                            ->stripCharacters('.')
                                            ->formatStateUsing(fn (?string $state): ?string => $state ? number_format((float)$state, 2, ',', '') : null)
                                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? str_replace(',', '.', $state) : null)
                                            ->required(),
                                    ])->columns(3),

                                Forms\Components\Section::make('Valores e Amortização')
                                    ->schema([
                                        Forms\Components\TextInput::make('vida_util_remanescente')
                                            ->label('Vida Útil Remanescente (meses)')
                                            ->numeric()
                                            ->required(),

                                        Forms\Components\TextInput::make('amortizacao_mensal')
                                            ->label('Amortização Mensal')
                                            ->prefix('R$')
                                            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                                            ->stripCharacters('.')
                                            ->formatStateUsing(fn (?string $state): ?string => $state ? number_format((float)$state, 2, ',', '') : null)
                                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? str_replace(',', '.', $state) : null)
                                            ->required(),

                                        Forms\Components\TextInput::make('amortizacao_acumulada')
                                            ->label('Amortização Acumulada')
                                            ->prefix('R$')
                                            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                                            ->stripCharacters('.')
                                            ->formatStateUsing(fn (?string $state): ?string => $state ? number_format((float)$state, 2, ',', '') : null)
                                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? str_replace(',', '.', $state) : null)
                                            ->required(),

                                        Forms\Components\TextInput::make('valor_liquido_contabil')
                                            ->label('Valor Líquido Contábil')
                                            ->prefix('R$')
                                            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                                            ->stripCharacters('.')
                                            ->formatStateUsing(fn (?string $state): ?string => $state ? number_format((float)$state, 2, ',', '') : null)
                                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? str_replace(',', '.', $state) : null)
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Situação')
                            ->icon('heroicon-o-chat-bubble-left-ellipsis')
                            ->schema([
                                Forms\Components\Section::make('Informações Adicionais')
                                    ->schema([
                                        Forms\Components\Textarea::make('observacao')
                                            ->label('Observação')
                                            ->placeholder('Insira detalhes ou observações sobre o status deste bem...')
                                            ->rows(6)
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('idTipoIntangivelRef.descricao')
                    ->label('Tipos de Intangíveis')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                Tables\Columns\TextColumn::make('idFabricanteRef.descricao')
                    ->label('Fabricante')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('classificacao')
                    ->label('Classificação')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('versao')
                    ->label('Versão')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Quantidade')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_aquisicao')
                    ->label('Data de aquisição')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor_aquisicao')
                    ->label('Valor de aquisição')
                    ->money('BRL')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('processo_aquisicao')
                    ->label('Processo de aquisição')
                    ->limit(15)
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('inscricao_generica')
                    ->label('Inscrição Genérica')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizadoPorRef.name')
                    ->label('Atualizado por')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizado_em')
                    ->label('Atualizado em')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('observacao')
                    ->label('Observação')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBemIntangivels::route('/'),
            'create' => Pages\CreateBemIntangivel::route('/create'),
            'edit' => Pages\EditBemIntangivel::route('/{record}/edit'),
        ];
    }
}
