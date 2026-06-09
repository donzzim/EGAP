<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\BemImovelResource\Pages;
use App\Models\Patrimonio\BensImoveis\BemImovel;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class BemImovelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = BemImovel::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $recordTitleAttribute = 'descricao';
    protected static ?string $modelLabel = 'Bem Imóvel';
    protected static ?string $pluralModelLabel = 'Administração dos bens imóveis';
    protected static ?string $navigationLabel = 'Administração dos bens imóveis';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'bens-imoveis/adm-bens-imoveis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('1. Imóveis')->schema([
                            Grid::make(3)->schema([
                                Forms\Components\TextInput::make('num_registro')->label('Núm. registro'),
                                Forms\Components\DatePicker::make('data_construcao')->label('Data construção')->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('valor_historico_escritura')->label('Valor histórico escritura')->numeric(),

                                Forms\Components\TextInput::make('valor_historico_iptu')->label('Valor historico IPTU')->numeric(),
                                Forms\Components\TextInput::make('valor_historico_1a_avaliacao')->label('Valor historico 1a avaliacao')->numeric(),
                                Forms\Components\TextInput::make('criterio_valor_historico')->label('Critério valor histórico'),

                                Forms\Components\TextInput::make('criterio_valor_atualizado')->label('Critério valor atualizado'),

                                Forms\Components\Select::make('id_setores')
                                    ->label('Setores')
                                    ->relationship('setoresRelacaoref', 'Setor')
                                    ->searchable()
                                    ->optionsLimit(50),

                                Forms\Components\Select::make('id_responsavel')
                                    ->label('Responsável')
                                    ->relationship('responsavelRelacaoref', 'descricao')
                                    ->searchable()
                                    ->optionsLimit(50),

                                Forms\Components\TextInput::make('num_processo_tj')->label('Processo TJ'),
                                Forms\Components\TextInput::make('num_processo_seger')->label('Processo SEGER'),
                                Forms\Components\TextInput::make('num_matricula')->label('Matrícula'),

                                Forms\Components\Select::make('num_processo_adm')
                                    ->label('Proc Administrativo')
                                    ->relationship('processoAdmRelacaoref', 'num_processo')
                                    ->searchable()
                                    ->optionsLimit(50),
                            ])
                        ]),

                        Tabs\Tab::make('2. Localização')->schema([
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('end_logradouro')->label('Logradouro'),
                                Forms\Components\TextInput::make('end_numero')->label('Número'),
                                Forms\Components\TextInput::make('end_cidade')->label('Cidade'),
                                Forms\Components\TextInput::make('end_bairro')->label('Bairro'),
                                Forms\Components\TextInput::make('end_estado')->label('Estado'),
                                Forms\Components\TextInput::make('end_cep')->label('CEP'),
                                Forms\Components\TextInput::make('end_compl_endereco')->label('Complemento'),
                                Forms\Components\TextInput::make('end_latitude')->label('Latitude'),
                                Forms\Components\TextInput::make('end_longitude')->label('Longitude'),

                                Forms\Components\Select::make('id_cidade')
                                    ->label('Cidade (Select)')
                                    ->relationship('cidadeRelacaoref', 'descricao')
                                    ->searchable()
                                    ->optionsLimit(50),

                                Forms\Components\Select::make('id_ciduf')
                                    ->label('Cidade/UF')
                                    ->relationship('cidufRelacaoref', 'cd_uf')
                                    ->searchable()
                                    ->optionsLimit(50),
                            ])
                        ]),

                        Tabs\Tab::make('3. Descrição do Imóvel')->schema([
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('descricao')->label('Descrição')->columnSpanFull(),
                                Forms\Components\TextInput::make('inscricao_generica')->label('Inscricao genérica'),
                                Forms\Components\TextInput::make('area')->label('Área')->numeric(),
                                Forms\Components\TextInput::make('area_terreno_total')->label('Área terreno total')->numeric(),
                                Forms\Components\TextInput::make('area_edificacao')->label('Área edificação')->numeric(),

                                Forms\Components\Select::make('id_tipoimovel')
                                    ->label('Tipo Imóvel')
                                    ->relationship('tipoImovelRelacaoref', 'desc_tipo_imovel')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('id_denominacao')
                                    ->label('Denominação')
                                    ->relationship('denominacaoRelacaoref', 'denominacao')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('id_tipodebem')
                                    ->label('Tipo de bem')
                                    ->relationship('tipoDeBemRelacaoref', 'Descricao')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\TextInput::make('inscricao_fiscal')->label('Inscricao Fiscal'),
                                Forms\Components\TextInput::make('inscricao_imobiliaria')->label('Inscricao Imobiliária'),
                            ])
                        ]),

                        Tabs\Tab::make('4. Contábil')->schema([
                            Grid::make(2)->schema([
                                Forms\Components\DatePicker::make('data_aquisicao')->label('Data aquisição')->displayFormat('d/m/Y')->native(false),
                                Forms\Components\DatePicker::make('data_incorporacao')->label('Data incorporação')->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('vida_util')->label('Vida Útil'),
                                Forms\Components\DatePicker::make('data_ingresso_contabil')->label('Data ingresso contábil')->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('idade_aparente_anos')->label('Idade Aparente (anos)')->numeric(),

                                Forms\Components\Select::make('id_planocontas')
                                    ->label('Conta Contábil')
                                    ->relationship('planoContasRelacaoref', 'titulo')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->codigo} / {$record->titulo}")
                                    ->searchable(['codigo', 'titulo'])
                                    ->optionsLimit(50),

                                Forms\Components\Select::make('id_elementodespesa')
                                    ->label('Elemento Despesa')
                                    ->relationship('elementoDespesaRelacaoref', 'DescricaodaClasse')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->CodigodaClasse} - {$record->DescricaodaClasse}")
                                    ->searchable(['CodigodaClasse', 'DescricaodaClasse'])
                                    ->optionsLimit(50),

                                Forms\Components\TextInput::make('vida_util_remanescente')->label('Vida Útil Remanescente'),
                                Forms\Components\TextInput::make('depreciacao_mensal')->label('Depreciacao Mensal')->numeric(),
                                Forms\Components\TextInput::make('depreciacao_acumulada')->label('Depreciacao Acumulada')->numeric(),
                                Forms\Components\TextInput::make('valor_liquido_contabil')->label('Valor Líquido Contabil')->numeric(),
                                Forms\Components\TextInput::make('valor_residual')->label('Valor Residual')->numeric(),
                            ])
                        ]),

                        Tabs\Tab::make('5. Reavaliação')->schema([
                            Grid::make(2)->schema([
                                Forms\Components\DatePicker::make('data_reavaliacao')->label('Data reavaliação')->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('valor_atualizado')->label('Valor atualizado')->numeric(),
                                Forms\Components\TextInput::make('valor_reavaliado')->label('Valor reavaliado')->numeric(),
                            ])
                        ]),

                        Tabs\Tab::make('6. Situação')->schema([
                            Grid::make(2)->schema([
                                Forms\Components\Select::make('id_situacao')
                                    ->label('Conta')
                                    ->relationship('situacaoRelacaoref', 'Descricao')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('id_condicaouso')
                                    ->label('Condição de Uso')
                                    ->relationship('condicaoUsoRelacaoref', 'descricao')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('id_estadoconservacao')
                                    ->label('Estado de conservação')
                                    ->relationship('estadoConservacaoRelacaoref', 'descEstadoConservacao')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('id_entradasaida')
                                    ->label('Entrada/Saída')
                                    ->relationship('entradaSaidaRelacaoref', 'tipo')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->tipo} - {$record->descricao}")
                                    ->searchable(['tipo', 'descricao'])
                                    ->preload(),

                                Forms\Components\DatePicker::make('data_baixa')->label('Data Baixa')->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('processo_baixa')->label('Processo de baixa'),
                                Forms\Components\Textarea::make('observacao')->label('Observação')->columnSpanFull(),
                                Forms\Components\DatePicker::make('data_situacao')->label('Data Transf. Ativo')->displayFormat('d/m/Y')->native(false),
                            ])
                        ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('num_registro')
                    ->label('Núm. registro')
                    ->searchable()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('setoresRelacaoRef.Setor')
                    ->label('Setores')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('responsavelRelacaoRef.descricao')
                    ->label('Responsável')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('num_processo_tj')
                    ->label('Processo TJ')
                    ->searchable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('num_processo_seger')
                    ->label('Processo SEGER')
                    ->searchable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('num_matricula')
                    ->label('Matrícula')
                    ->searchable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('end_cidade')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('cidufRelacaoref.cd_uf')
                    ->label('Cidade/UF')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('tipoImovelRelacaoref.desc_tipo_imovel')
                    ->label('Tipo Imóvel')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('denominacaoRelacaoref.denominacao')
                    ->label('Denominação')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('tipoDeBemRelacaoref.Descricao')
                    ->label('Tipo de bem'),

                Tables\Columns\TextColumn::make('data_aquisicao')
                    ->label('Data aquisição')
                    ->date('d/m/Y')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('valor_reavaliado')
                    ->label('Valor reavaliado')
                    ->money('BRL')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('situacaoRelacaoref.Descricao')
                    ->label('Conta')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('condicaoUsoRelacaoref.descricao')
                    ->label('Condição de Uso/Forma de Aquisição')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('estadoConservacaoRelacaoref.descEstadoConservacao')
                    ->label('Estado de conservação')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('id_entradasaida')
                    ->label('Entrada/Saída')
                    ->formatStateUsing(fn ($record) => $record->entradaSaidaRelacaoref
                        ? "{$record->entradaSaidaRelacaoref->tipo} - {$record->entradaSaidaRelacaoref->descricao}"
                        : null)
                    ->alignCenter(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->color('warning')
                        ->icon('heroicon-o-pencil-square')
                        ->modalHeading('Editar Bem Imóvel')
                        ->modalWidth('7xl'),

                    Tables\Actions\ViewAction::make()
                        ->label('Visualizar')
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Excluir')
                        ->color('danger')
                        ->icon('heroicon-o-trash'),

                Tables\Actions\EditAction::make('gerenciar_tributos')
                        ->label('Tributos')
                        ->color('info')
                        ->icon('heroicon-o-document-text')
                        ->modalHeading(fn ($record) => "Gerenciar Tributos - " . $record->descricao)
                        ->modalWidth('6xl')
                        ->modalSubmitActionLabel('Salvar alterações')
                        ->form([
                            Forms\Components\Repeater::make('tributosRelacaoRef')
                                ->relationship('tributosRelacaoRef')
                                ->label('')
                                ->defaultItems(0)
                                ->addActionLabel('Adicionar novo tributo')
                                ->collapsible()
                                ->schema([
                                    Forms\Components\Select::make('tipo_tributo')
                                        ->label('Tipo do tributo')
                                        ->relationship('tipoTributoRelacaoref', 'descricao')
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Forms\Components\DatePicker::make('vencimento')
                                        ->label('Vencimento')
                                        ->displayFormat('d/m/Y')
                                        ->native(false),

                                    Forms\Components\TextInput::make('valor')
                                        ->label('Valor')
                                        ->numeric()
                                        ->prefix('R$'),

                                    Forms\Components\DatePicker::make('pago_em')
                                        ->label('Pago em')
                                        ->displayFormat('d/m/Y')
                                        ->native(false),

                                    Forms\Components\TextInput::make('valor_pago')
                                        ->label('Valor Pago')
                                        ->numeric()
                                        ->prefix('R$'),

                                    Forms\Components\TextInput::make('processo_pagto')
                                        ->label('Processo Pagto'),

                                    Forms\Components\Textarea::make('observacao')
                                        ->label('Observação')
                                        ->columnSpanFull()
                                        ->rows(2),

                                    Forms\Components\Hidden::make('atualizado_por')->default(fn () => auth()->id()),
                                    Forms\Components\Hidden::make('date_time')->default(now()),
                                ])
                                ->columns(3)
                        ]),

                Tables\Actions\Action::make('abrir_ocupacoes')
                        ->label('Ocupações de Terceiros')
                        ->color('info')
                        ->icon('heroicon-o-map-pin')
                        ->url(fn () => \App\Filament\Resources\Patrimonio\BensImoveis\CedidoResource::getUrl('index'))
                        ->openUrlInNewTab(),

                Tables\Actions\EditAction::make('gerenciar_reavaliacoes')
                    ->label('Reavaliação dos Imóveis')
                    ->color('info')
                    ->icon('heroicon-o-currency-dollar')
                    ->modalHeading(fn ($record) => "Reavaliações - " . $record->descricao)
                    ->modalWidth('7xl')
                    ->modalSubmitActionLabel('Salvar alterações')
                    ->form([
                        Forms\Components\Repeater::make('reavaliacoesRelacaoRef')
                            ->relationship('reavaliacoesRelacaoRef')
                            ->label('')
                            ->defaultItems(0)
                            ->addActionLabel('Adicionar nova reavaliação')
                            ->collapsible()
                            ->itemLabel(fn (array $state) => 'Reavaliação: ' . (isset($state['data_reavaliacao']) ? date('d/m/Y', strtotime($state['data_reavaliacao'])) : 'Nova'))
                            ->schema([
                                Forms\Components\Tabs::make('Tabs')
                                    ->tabs([
                                        Forms\Components\Tabs\Tab::make('Reavaliação dos Imóveis')
                                            ->schema([

                                                Forms\Components\DatePicker::make('data_reavaliacao')
                                                    ->label('Data Reavaliação')
                                                    ->default(now())
                                                    ->displayFormat('d/m/Y')
                                                    ->native(false),

                                                Forms\Components\TextInput::make('valor_reavaliacao')
                                                    ->label('Valor Reavaliação')
                                                    ->numeric()
                                                    ->prefix('R$'),

                                                Forms\Components\TextInput::make('vida_util_reavaliacao')
                                                    ->label('Vida Útil Reavaliação')
                                                    ->numeric(),

                                                Forms\Components\Select::make('Id_estadoconservacao')
                                                    ->label('Estado de Conservação')
                                                    ->relationship('estadoConservacaoRelacaoref', 'descEstadoConservacao')
                                                    ->searchable()
                                                    ->preload(),

                                                Forms\Components\TextInput::make('ajuste_contabil')
                                                    ->label('Ajuste Contábil')
                                                    ->numeric()
                                                    ->prefix('R$'),

                                                Forms\Components\Textarea::make('observacao')
                                                    ->label('Observação')
                                                    ->columnSpanFull()
                                                    ->rows(2),
                                            ])->columns(4),

                                        Forms\Components\Tabs\Tab::make('Complemento')
                                            ->schema([
                                                Forms\Components\TextInput::make('valor_mercado')
                                                    ->label('Valor Mercado')
                                                    ->numeric()
                                                    ->prefix('R$'),

                                                Forms\Components\DateTimePicker::make('data_disponibilizacao')
                                                    ->label('Data Disponibilização')
                                                    ->default(now())
                                                    ->displayFormat('d/m/Y H:i:s')
                                                    ->native(false),

                                                Forms\Components\DateTimePicker::make('data_referencia')
                                                    ->label('Data Referência')
                                                    ->default(now())
                                                    ->displayFormat('d/m/Y H:i:s')
                                                    ->native(false),

                                                Forms\Components\TextInput::make('valor_aquisicao')
                                                    ->label('Valor Aquisição')
                                                    ->numeric()
                                                    ->prefix('R$'),

                                                Forms\Components\TextInput::make('vida_util_siafi')
                                                    ->label('Vida Útil SIAFI')
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('vida_util')
                                                    ->label('Vida Útil')
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('tempo_utilizacao_meses')
                                                    ->label('Tempo Utilização Meses')
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('vida_util_remanescente_meses')
                                                    ->label('Vida Útil Remanescente Meses')
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('vida_util_estimada_anos')
                                                    ->label('Vida Útil Estimada Anos')
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('PUB1')
                                                    ->label('PUB1')
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('PUV')
                                                    ->label('PUV')
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('FR')
                                                    ->label('FR')
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('utilizacao_bem_anos')
                                                    ->label('Utilização Bem Anos')
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('idade_aparente_anos')
                                                    ->label('Idade Aparente Anos')
                                                    ->numeric(),

                                                Forms\Components\Hidden::make('atualizado_por')->default(fn () => auth()->id()),
                                                Forms\Components\Hidden::make('date_time')->default(now()),
                                            ])->columns(4),
                                    ])
                                    ->columnSpanFull()
                            ])
                    ]),

                    Tables\Actions\Action::make('imprimir')
                        ->label('Imprimir termo')
                        ->color('info')
                        ->icon('heroicon-o-printer')
                        ->url(fn ($record) => "https://sistemas.tjes.jus.br/patrimonio/index.php?option=com_reports&name=termo-imovel&tmpl=component&bens={$record->Id}")
                        ->openUrlInNewTab(),

                    Tables\Actions\EditAction::make('gerenciar_obras')
                        ->label('Obras e Ampliações')
                        ->color('info')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->modalHeading(fn ($record) => "Obras e Ampliações - " . $record->descricao)
                        ->modalWidth('4xl')
                        ->modalSubmitActionLabel('Salvar alterações')
                        ->form([
                            Forms\Components\Repeater::make('obrasRelacaoRef')
                                ->relationship('obrasRelacaoRef')
                                ->label('')
                                ->defaultItems(0)
                                ->addActionLabel('Adicionar nova obra/ampliação')
                                ->collapsible()

                                ->itemLabel(fn (array $state) => 'Obra: ' . (isset($state['data']) ? date('d/m/Y', strtotime($state['data'])) : 'Nova'))
                                ->schema([
                                    Forms\Components\Textarea::make('descricao')
                                        ->label('Descrição')
                                        ->columnSpanFull()
                                        ->rows(3),

                                    Forms\Components\DatePicker::make('data')
                                        ->label('Data')
                                        ->displayFormat('d/m/Y')
                                        ->native(false),

                                    Forms\Components\TextInput::make('valor')
                                        ->label('Valor')
                                        ->numeric()
                                        ->prefix('R$'),

                                    Forms\Components\Hidden::make('atualizado_por')->default(fn () => auth()->id()),
                                    Forms\Components\Hidden::make('date_time')->default(now()),
                                ])
                                ->columns(2)
                        ]),

                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Opções')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ]),
            ])
            ->selectCurrentPageOnly()
            ->striped()
            ->deferLoading()
            ->emptyStateHeading('Nenhum bem imóvel encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBemImovels::route('/'),
        ];
    }
}
