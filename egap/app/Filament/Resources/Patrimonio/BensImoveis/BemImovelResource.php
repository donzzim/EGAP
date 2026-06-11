<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\BemImovelResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensImoveis\BemImovel;
use App\Models\Patrimonio\BensImoveis\Depreciacao;
use Carbon\CarbonImmutable;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class BemImovelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = BemImovel::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $recordTitleAttribute = 'descricao';

    protected static ?string $modelLabel = 'Bem Imóvel';

    protected static ?string $pluralModelLabel = 'Administração dos Bens Imóveis';

    protected static ?string $navigationLabel = 'Administração';

    protected static ?string $navigationGroup = 'Bens Imóveis';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'bens-imoveis/adm-bens-imoveis';

    // -------------------------------------------------------------------------
    // FORM
    // -------------------------------------------------------------------------

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('TabsBemImovel')
                ->tabs([
                    self::tabImovel(),
                    self::tabLocalizacao(),
                    self::tabDescricao(),
                    self::tabContabil(),
                    self::tabReavaliacao(),
                    self::tabSituacao(),
                ])
                ->columnSpanFull(),
        ]);
    }

    // ---- Tabs ----------------------------------------------------------------

    private static function tabImovel(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Imóvel')
            ->icon('heroicon-o-building-office-2')
            ->schema([
                self::section('Identificação', 'heroicon-o-identification', [
                    self::text('num_registro', 'Núm. registro')
                        ->columnSpan(3),
                    self::date('data_construcao', 'Data de construção')
                        ->columnSpan(3),
                    self::text('num_matricula', 'Matrícula')
                        ->columnSpan(3),
                    self::select('Id_setores', 'Setor', 'setoresRelacaoRef', 'Setor')
                        ->columnSpan(6),
                    self::select('id_responsavel', 'Responsável', 'responsavelRelacaoRef', 'descricao')
                        ->columnSpan(6),
                ])
                    ->description('Dados principais de registro e responsabilidade do imóvel.')
                    ->columns(12),

                self::section('Processos', 'heroicon-o-folder-open', [
                    self::text('num_processo_tj', 'Processo TJ'),
                    self::text('num_processo_seger', 'Processo SEGER'),
                    self::select('num_processo_adm', 'Processo Administrativo', 'processoAdmRelacaoRef', 'num_processo'),
                ])
                    ->description('Vinculações processuais relacionadas ao imóvel.')
                    ->columns(3),

                self::section('Valores históricos', 'heroicon-o-banknotes', [
                    MoneyInput::make('valor_historico_escritura')
                        ->label('Valor histórico da escritura')
                        ->columnSpan(4),
                    MoneyInput::make('valor_historico_iptu')
                        ->label('Valor histórico do IPTU')
                        ->columnSpan(4),
                    MoneyInput::make('valor_historico_1a_avaliacao')
                        ->label('Valor histórico da 1ª avaliação')
                        ->columnSpan(4),
                    self::text('criterio_valor_historico', 'Critério do valor histórico')
                        ->columnSpan(6),
                    self::text('criterio_valor_atualizado', 'Critério do valor atualizado')
                        ->columnSpan(6),
                ])
                    ->description('Critérios e valores históricos usados na composição patrimonial.')
                    ->columns(12),
            ]);
    }

    private static function tabLocalizacao(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Localização')
            ->icon('heroicon-o-map-pin')
            ->schema([
                self::section('Endereço', 'heroicon-o-map', [
                    self::text('end_logradouro', 'Logradouro')->columnSpan(7),
                    self::text('end_numero', 'Número')->columnSpan(2),
                    self::text('end_cep', 'CEP')->columnSpan(3),
                    self::text('end_bairro', 'Bairro')->columnSpan(4),
                    self::text('end_cidade', 'Cidade')->columnSpan(4),
                    self::text('end_estado', 'Estado')->maxLength(2)->columnSpan(2),
                    self::text('end_compl_endereco', 'Complemento')->columnSpanFull(),
                ])->columns(12),

                self::section('Referências geográficas', 'heroicon-o-globe-alt', [
                    self::text('end_latitude', 'Latitude'),
                    self::text('end_longitude', 'Longitude'),
                    self::select('id_cidade', 'Cidade cadastrada', 'cidadeRelacaoRef', 'descricao'),
                    self::select('id_ciduf', 'Cidade/UF', 'cidufRelacaoRef', 'cd_uf'),
                ])->columns(4),
            ]);
    }

    private static function tabDescricao(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Descrição')
            ->icon('heroicon-o-document-text')
            ->schema([
                self::section('Descrição do imóvel', 'heroicon-o-home-modern', [
                    self::text('descricao', 'Descrição')->columnSpanFull(),
                    self::text('inscricao_generica', 'Inscrição genérica')->columnSpan(4),
                    self::text('inscricao_fiscal', 'Inscrição fiscal')->columnSpan(4),
                    self::text('inscricao_imobiliaria', 'Inscrição imobiliária')->columnSpan(4),
                ])->columns(12),

                self::section('Classificação', 'heroicon-o-squares-2x2', [
                    self::select('Id_tipoimovel', 'Tipo de imóvel', 'tipoImovelRelacaoRef', 'desc_tipo_imovel'),
                    self::select('id_denominacao', 'Denominação', 'denominacaoRelacaoRef', 'denominacao'),
                    self::select('Id_tipodebem', 'Tipo de bem', 'tipoDeBemRelacaoRef', 'Descricao'),
                ])->columns(3),

                self::section('Áreas', 'heroicon-o-arrows-pointing-out', [
                    self::number('area', 'Área')->suffix('m²'),
                    self::number('area_terreno_total', 'Área do terreno total')->suffix('m²'),
                    self::number('area_edificacao', 'Área de edificação')->suffix('m²'),
                ])->columns(3),
            ]);
    }

    private static function tabContabil(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Contábil')
            ->icon('heroicon-o-calculator')
            ->schema([
                self::section('Classificação contábil', 'heroicon-o-clipboard-document-list', [
                    self::select('id_planocontas', 'Conta contábil', 'planoContasRelacaoRef', 'titulo')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->codigo} / {$record->titulo}")
                        ->searchable(['codigo', 'titulo'])
                        ->columnSpan(6),
                    self::select('id_elementodespesa', 'Elemento de despesa', 'elementoDespesaRelacaoRef', 'DescricaodaClasse')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->CodigodaClasse} - {$record->DescricaodaClasse}")
                        ->searchable(['CodigodaClasse', 'DescricaodaClasse'])
                        ->columnSpan(6),
                ])->columns(12),

                self::section('Datas e vida útil', 'heroicon-o-calendar-days', [
                    self::date('data_aquisicao', 'Data de aquisição'),
                    self::date('data_incorporacao', 'Data de incorporação'),
                    self::date('data_ingresso_contabil', 'Data de ingresso contábil'),
                    self::number('vida_util', 'Vida útil')->suffix('meses'),
                    self::number('vida_util_remanescente', 'Vida útil remanescente')->suffix('meses'),
                    self::number('idade_aparente_anos', 'Idade aparente')->suffix('anos'),
                ])->columns(3),

                self::section('Valores contábeis', 'heroicon-o-banknotes', [
                    MoneyInput::make('depreciacao_mensal')->label('Depreciação mensal'),
                    MoneyInput::make('depreciacao_acumulada')->label('Depreciação acumulada'),
                    MoneyInput::make('valor_liquido_contabil')->label('Valor líquido contábil'),
                    MoneyInput::make('valor_residual')->label('Valor residual'),
                ])->columns(4),
            ]);
    }

    private static function tabReavaliacao(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Reavaliação')
            ->icon('heroicon-o-currency-dollar')
            ->schema([
                self::section('Última reavaliação', 'heroicon-o-arrow-path', [
                    self::date('data_reavaliacao', 'Data da reavaliação'),
                    MoneyInput::make('valor_atualizado')->label('Valor atualizado'),
                    MoneyInput::make('valor_reavaliado')->label('Valor reavaliado'),
                ])->columns(3),
            ]);
    }

    private static function tabSituacao(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Situação')
            ->icon('heroicon-o-check-circle')
            ->schema([
                self::section('Estado atual', 'heroicon-o-check-badge', [
                    self::select('Id_situacao', 'Situação', 'situacaoRelacaoRef', 'Descricao'),
                    self::select('id_condicaouso', 'Condição de uso', 'condicaoUsoRelacaoRef', 'descricao'),
                    self::select('Id_estadoconservacao', 'Estado de conservação', 'estadoConservacaoRelacaoRef', 'descEstadoConservacao'),
                    self::select('id_entradasaida', 'Entrada/Saída', 'entradaSaidaRelacaoRef', 'tipo')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->tipo} - {$record->descricao}")
                        ->searchable(['tipo', 'descricao']),
                ])->columns(4),

                self::section('Baixa e observações', 'heroicon-o-archive-box-x-mark', [
                    self::date('data_baixa', 'Data da baixa'),
                    self::text('processo_baixa', 'Processo de baixa'),
                    self::date('data_situacao', 'Data de transferência do ativo'),
                    self::textarea('observacao', 'Observação')->columnSpanFull(),
                ])->columns(3),
            ]);
    }

    // -------------------------------------------------------------------------
    // TABLE
    // -------------------------------------------------------------------------

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->deferLoading()
            ->defaultSort('Id', 'desc')
            ->columns([
                TableColumns::text('Id', '#', isFirstColumn: true),
                TableColumns::text('num_registro', 'Núm. registro')->badge(),
                TableColumns::text('descricao', 'Descrição')
                    ->limit(45)
                    ->tooltip(fn ($record): ?string => $record->descricao),
                TableColumns::text('setoresRelacaoRef.Setor', 'Setor')
                    ->limit(35)
                    ->tooltip(fn ($record): ?string => $record->setoresRelacaoRef?->Setor),
                TableColumns::text('responsavelRelacaoRef.descricao', 'Responsável')
                    ->limit(30)
                    ->tooltip(fn ($record): ?string => $record->responsavelRelacaoRef?->descricao),
                TableColumns::text('end_cidade', 'Cidade'),
                TableColumns::text('cidufRelacaoRef.cd_uf', 'UF'),
                TableColumns::text('tipoImovelRelacaoRef.desc_tipo_imovel', 'Tipo de imóvel'),
                TableColumns::text('denominacaoRelacaoRef.denominacao', 'Denominação'),
                TableColumns::date('data_aquisicao', 'Aquisição'),
                TableColumns::money('valor_reavaliado', 'Valor reavaliado'),
                TableColumns::text('situacaoRelacaoRef.Descricao', 'Situação')->badge(),
                TableColumns::text('condicaoUsoRelacaoRef.descricao', 'Condição'),
                TableColumns::text('estadoConservacaoRelacaoRef.descEstadoConservacao', 'Conservação'),
                TableColumns::text('entradaSaidaRelacaoRef.tipo', 'Entrada/Saída')
                    ->formatStateUsing(fn ($record): string => $record->entradaSaidaRelacaoRef
                        ? "{$record->entradaSaidaRelacaoRef->tipo} - {$record->entradaSaidaRelacaoRef->descricao}"
                        : '-'),
            ])
            ->actions([
                ...TableDefaults::actions(),
                Tables\Actions\ActionGroup::make([
                    self::calcularDepreciacaoTableAction(),
                    self::registroDepreciacaoTableAction(),
                    self::tributosTableAction(),
                    self::ocupacaoTableAction(),
                    self::imprimirTermoTableAction(),
                    self::processosTableAction(),
                    self::reavaliacaoTableAction(),
                    self::obrasTableAction(),
                ])
                    ->hiddenLabel()
                    ->icon('heroicon-m-ellipsis-vertical'),
            ]);
    }

    // ---- Table Actions -------------------------------------------------------

    private static function calcularDepreciacaoTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('calcular_depreciacao_action')
            ->label('Calcular Depreciação')
            ->icon('heroicon-o-calculator')
            ->requiresConfirmation()
            ->modalHeading('Calcular depreciação')
            ->modalDescription('Os registros de depreciação existentes a partir da data-base serão recalculados.')
            ->visible(fn (BemImovel $record): bool => (int) $record->id_denominacao === 1)
            ->action(fn (BemImovel $record) => self::executarCalculoDepreciacao($record));
    }

    private static function registroDepreciacaoTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('registro_depreciacao_action')
            ->label('Registros de Depreciação')
            ->icon('heroicon-o-pencil-square')
            ->modalHeading(fn (BemImovel $record): string => "Registros de depreciação do imóvel #{$record->Id}")
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalWidth('7xl')
            ->mountUsing(fn (BemImovel $record) => $record->loadMissing('depreciacoesRelacaoRef'))
            ->infolist(fn (BemImovel $record): array => [
                InfolistSection::make('Resumo')
                    ->icon('heroicon-o-calculator')
                    ->description('Registros carregados somente ao abrir este modal.')
                    ->schema([
                        InfolistGrid::make(3)->schema([
                            TextEntry::make('depreciacoes_total')
                                ->label('Total de registros')
                                ->state(fn (BemImovel $record): int => $record->depreciacoesRelacaoRef->count())
                                ->badge()
                                ->color('primary'),
                            TextEntry::make('depreciacoes_ultima_data')
                                ->label('Última Data')
                                ->state(fn (BemImovel $record): string => self::formatDate($record->depreciacoesRelacaoRef->last()?->data_calculo))
                                ->placeholder('-'),
                            TextEntry::make('depreciacoes_primeira_data')
                                ->label('Primeira Data')
                                ->state(fn (BemImovel $record): string => self::formatDate($record->depreciacoesRelacaoRef->first()?->data_calculo))
                                ->placeholder('-'),
                        ]),
                    ]),
                InfolistSection::make('Depreciações')
                    ->icon('heroicon-o-arrow-trending-down')
                    ->schema([
                        TextEntry::make('depreciacoes_vazias')
                            ->hiddenLabel()
                            ->state('Nenhum registro de depreciação relacionado a este bem imóvel.')
                            ->badge()
                            ->color('gray')
                            ->visible(fn (BemImovel $record): bool => $record->depreciacoesRelacaoRef->isEmpty()),
                        RepeatableEntry::make('depreciacoesRelacaoRef')
                            ->hiddenLabel()
                            ->contained(false)
                            ->schema([
                                InfolistGrid::make(12)->schema([
                                    TextEntry::make('item')
                                        ->label('Item')
                                        ->badge()->color('gray')
                                        ->placeholder('-')
                                        ->columnSpan(1),
                                    TextEntry::make('data_calculo')
                                        ->label('Data cálculo')
                                        ->formatStateUsing(fn ($state): string => self::formatDate($state))
                                        ->placeholder('-')
                                        ->columnSpan(2),
                                    TextEntry::make('valor')
                                        ->label('Valor base')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->placeholder('-')
                                        ->columnSpan(2),
                                    TextEntry::make('vida_util')
                                        ->label('Vida útil')
                                        ->suffix(' meses')
                                        ->badge()
                                        ->color('info')
                                        ->placeholder('-')
                                        ->columnSpan(1),
                                    TextEntry::make('valor_residual')
                                        ->label('Residual')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->placeholder('-')
                                        ->columnSpan(2),
                                    TextEntry::make('depreciacao_mensal')
                                        ->label('Mensal')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->placeholder('-')
                                        ->columnSpan(2),
                                    TextEntry::make('depreciacao_acumulada')
                                        ->label('Acumulada')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->color('warning')
                                        ->placeholder('-')
                                        ->columnSpan(2),
                                    TextEntry::make('valor_liquido_contabil')
                                        ->label('Valor líquido contábil')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->color('success')
                                        ->placeholder('-')
                                        ->columnSpan(3),
                                    TextEntry::make('obraRelacaoref.descricao')
                                        ->label('Obra/ampliação')
                                        ->placeholder('-')
                                        ->columnSpan(9),
                                ]),
                            ])
                            ->visible(fn (BemImovel $record): bool => $record->depreciacoesRelacaoRef->isNotEmpty()),
                    ]),
            ]);
    }

    private static function tributosTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('tributos_action')
            ->label('Tributos')
            ->icon('heroicon-o-clipboard')
            ->modalHeading(fn (BemImovel $record): string => "Tributos do imóvel #{$record->Id}")
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalWidth('7xl')
            ->mountUsing(fn (BemImovel $record) => $record->loadMissing('tributosRelacaoRef'))
            ->infolist(fn (BemImovel $record): array => [
                InfolistSection::make('Resumo')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->description('Tributos carregados somente ao abrir este modal.')
                    ->schema([
                        InfolistGrid::make(3)->schema([
                            TextEntry::make('tributos_total')
                                ->label('Total de tributos')
                                ->state(fn (BemImovel $record): int => $record->tributosRelacaoRef->count())
                                ->badge()->color('primary'),
                            TextEntry::make('tributos_valor_total')
                                ->label('Valor previsto')
                                ->state(fn (BemImovel $record): string => self::formatMoney($record->tributosRelacaoRef->sum('valor')))
                                ->color('warning'),
                            TextEntry::make('tributos_valor_pago_total')
                                ->label('Valor pago')
                                ->state(fn (BemImovel $record): string => self::formatMoney($record->tributosRelacaoRef->sum('valor_pago')))
                                ->color('success'),
                        ]),
                    ]),
                InfolistSection::make('Tributos')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextEntry::make('tributos_vazios')
                            ->hiddenLabel()
                            ->state('Nenhum tributo relacionado a este bem imóvel.')
                            ->badge()->color('gray')
                            ->visible(fn (BemImovel $record): bool => $record->tributosRelacaoRef->isEmpty()),
                        RepeatableEntry::make('tributosRelacaoRef')
                            ->hiddenLabel()
                            ->contained(false)
                            ->schema([
                                InfolistGrid::make(12)->schema([
                                    TextEntry::make('tipoTributoRelacaoref.descricao')
                                        ->label('Tipo')
                                        ->badge()->color('info')->placeholder('-')->columnSpan(3),
                                    TextEntry::make('vencimento')
                                        ->label('Vencimento')
                                        ->formatStateUsing(fn ($state): string => self::formatDate($state))
                                        ->placeholder('-')->columnSpan(2),
                                    TextEntry::make('valor')
                                        ->label('Valor')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->color('warning')->placeholder('-')->columnSpan(2),
                                    TextEntry::make('pago_em')
                                        ->label('Pago em')
                                        ->formatStateUsing(fn ($state): string => self::formatDate($state))
                                        ->placeholder('-')->columnSpan(2),
                                    TextEntry::make('valor_pago')
                                        ->label('Valor pago')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->color('success')->placeholder('-')->columnSpan(3),
                                    TextEntry::make('processo_pagto')
                                        ->label('Processo')
                                        ->placeholder('-')->columnSpan(4),
                                    TextEntry::make('observacao')
                                        ->label('Observação')
                                        ->placeholder('-')->columnSpan(8),
                                ]),
                            ])
                            ->visible(fn (BemImovel $record): bool => $record->tributosRelacaoRef->isNotEmpty()),
                    ]),
            ]);
    }

    private static function ocupacaoTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('ocupacoes_action')
            ->label('Ocupações de Terceiros')
            ->icon('heroicon-o-flag')
            ->modalHeading(fn (BemImovel $record): string => "Ocupações de terceiros do imóvel #{$record->Id}")
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalWidth('7xl')
            ->mountUsing(fn (BemImovel $record) => $record->loadMissing([
                'cedidosRelacaoRef' => fn ($query) => $query
                    ->orderByDesc('data_assinatura')
                    ->orderByDesc('id'),
            ]))
            ->infolist(fn (BemImovel $record): array => [
                InfolistSection::make('Resumo')
                    ->icon('heroicon-o-users')
                    ->schema([
                        TextEntry::make('ocupacoes_total')
                            ->label('Total de ocupações')
                            ->state(fn (BemImovel $record): int => $record->cedidosRelacaoRef->count())
                            ->badge()
                            ->color('primary'),
                    ]),
                InfolistSection::make('Ocupações de terceiros')
                    ->icon('heroicon-o-flag')
                    ->schema([
                        TextEntry::make('ocupacoes_vazias')
                            ->hiddenLabel()
                            ->state('Nenhuma ocupação de terceiro relacionada a este bem imóvel.')
                            ->badge()
                            ->color('gray')
                            ->visible(fn (BemImovel $record): bool => $record->cedidosRelacaoRef->isEmpty()),
                        RepeatableEntry::make('cedidosRelacaoRef')
                            ->hiddenLabel()
                            ->contained(false)
                            ->schema([
                                InfolistGrid::make(12)->schema([
                                    TextEntry::make('situacao')
                                        ->label('Situação')
                                        ->badge()
                                        ->placeholder('-')
                                        ->columnSpan(2),
                                    TextEntry::make('num_processo')
                                        ->label('Nº do processo')
                                        ->placeholder('-')
                                        ->columnSpan(3),
                                    TextEntry::make('resumo')
                                        ->label('Partes/Terceiros')
                                        ->placeholder('-')
                                        ->columnSpan(7),
                                    TextEntry::make('proprietario_responsavel')
                                        ->label('Proprietário/Responsável')
                                        ->placeholder('-')
                                        ->columnSpan(6),
                                    TextEntry::make('condicao_uso')
                                        ->label('Condição de uso')
                                        ->placeholder('-')
                                        ->columnSpan(6),
                                    TextEntry::make('objeto')
                                        ->label('Objeto')
                                        ->placeholder('-')
                                        ->columnSpan(12),
                                    TextEntry::make('data_assinatura')
                                        ->label('Assinatura')
                                        ->formatStateUsing(fn ($state): string => self::formatDate($state))
                                        ->columnSpan(2),
                                    TextEntry::make('data_publicacao')
                                        ->label('Publicação')
                                        ->formatStateUsing(fn ($state): string => self::formatDate($state))
                                        ->columnSpan(2),
                                    TextEntry::make('vencimento')
                                        ->label('Vencimento')
                                        ->formatStateUsing(fn ($state): string => self::formatDate($state))
                                        ->columnSpan(2),
                                    TextEntry::make('vigencia')
                                        ->label('Vigência')
                                        ->placeholder('-')
                                        ->columnSpan(2),
                                    TextEntry::make('retribuicao')
                                        ->label('Retribuição')
                                        ->badge()
                                        ->placeholder('-')
                                        ->columnSpan(2),
                                    TextEntry::make('despesas')
                                        ->label('Despesas')
                                        ->listWithLineBreaks()
                                        ->bulleted()
                                        ->placeholder('-')
                                        ->columnSpan(2),
                                    TextEntry::make('observacao')
                                        ->label('Observação')
                                        ->placeholder('-')
                                        ->columnSpan(12),
                                ]),
                            ])
                            ->visible(fn (BemImovel $record): bool => $record->cedidosRelacaoRef->isNotEmpty()),
                    ]),
            ]);
    }

    private static function imprimirTermoTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('imprimir')
            ->label('Imprimir termo')
            ->icon('heroicon-o-printer')
            ->url(fn ($record) => "https://sistemas.tjes.jus.br/patrimonio/index.php?option=com_reports&name=termo-imovel&tmpl=component&bens={$record->Id}")
            ->openUrlInNewTab();
    }

    private static function processosTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('processos_action')
            ->label('Processos')
            ->icon('heroicon-o-folder')
            ->modalHeading(fn (BemImovel $record): string => "Processos do imóvel #{$record->Id}")
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalWidth('7xl')
            ->mountUsing(fn (BemImovel $record) => $record->loadMissing([
                'processoAdmRelacaoRef.tipoProcessoRelacaoRef',
                'processoAdmRelacaoRef.unidadeRequisitanteRelacaoRef',
                'processoAdmRelacaoRef.gestorTitularRelacaoRef',
            ]))
            ->infolist(fn (BemImovel $record): array => [
                InfolistSection::make('Referências do imóvel')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        InfolistGrid::make(3)->schema([
                            TextEntry::make('num_processo_tj')
                                ->label('Processo TJ')
                                ->placeholder('-'),
                            TextEntry::make('num_processo_seger')
                                ->label('Processo SEGER')
                                ->placeholder('-'),
                            TextEntry::make('processo_administrativo_total')
                                ->label('Processos administrativos vinculados')
                                ->state(fn (BemImovel $record): int => $record->processoAdmRelacaoRef ? 1 : 0)
                                ->badge()
                                ->color('primary'),
                        ]),
                    ]),
                InfolistSection::make('Processo administrativo')
                    ->icon('heroicon-o-folder-open')
                    ->schema([
                        TextEntry::make('processo_administrativo_vazio')
                            ->hiddenLabel()
                            ->state('Nenhum processo administrativo relacionado a este bem imóvel.')
                            ->badge()
                            ->color('gray')
                            ->visible(fn (BemImovel $record): bool => $record->processoAdmRelacaoRef === null),
                        InfolistGrid::make(12)
                            ->schema([
                                TextEntry::make('processoAdmRelacaoRef.num_processo')
                                    ->label('Nº Processo TJES')
                                    ->placeholder('-')
                                    ->columnSpan(3),
                                TextEntry::make('processoAdmRelacaoRef.no_processo_sei')
                                    ->label('Nº Processo SEI')
                                    ->placeholder('-')
                                    ->columnSpan(3),
                                TextEntry::make('processoAdmRelacaoRef.tipoProcessoRelacaoRef.descricao')
                                    ->label('Tipo')
                                    ->placeholder('-')
                                    ->columnSpan(3),
                                TextEntry::make('processoAdmRelacaoRef.situacao_atual')
                                    ->label('Situação atual')
                                    ->badge()
                                    ->placeholder('-')
                                    ->columnSpan(3),
                                TextEntry::make('processoAdmRelacaoRef.data_abertura')
                                    ->label('Data de abertura')
                                    ->formatStateUsing(fn ($state): string => self::formatDate($state))
                                    ->columnSpan(2),
                                TextEntry::make('processoAdmRelacaoRef.data_vigencia')
                                    ->label('Fim da vigência')
                                    ->formatStateUsing(fn ($state): string => self::formatDate($state))
                                    ->columnSpan(2),
                                TextEntry::make('processoAdmRelacaoRef.unidadeRequisitanteRelacaoRef.Setor')
                                    ->label('Unidade requisitante')
                                    ->placeholder('-')
                                    ->columnSpan(4),
                                TextEntry::make('processoAdmRelacaoRef.gestorTitularRelacaoRef.name')
                                    ->label('Gestor titular')
                                    ->placeholder('-')
                                    ->columnSpan(4),
                                TextEntry::make('processoAdmRelacaoRef.descricao')
                                    ->label('Descrição')
                                    ->placeholder('-')
                                    ->columnSpan(12),
                            ])
                            ->visible(fn (BemImovel $record): bool => $record->processoAdmRelacaoRef !== null),
                    ]),
            ]);
    }

    private static function reavaliacaoTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('reavaliacao_action')
            ->label('Reavaliação dos Imóveis')
            ->icon('heroicon-o-forward')
            ->modalHeading(fn (BemImovel $record): string => "Reavaliações do imóvel #{$record->Id}")
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalWidth('7xl')
            ->mountUsing(fn (BemImovel $record) => $record->load([
                'reavaliacoesRelacaoRef' => fn ($query) => $query
                    ->with('estadoConservacaoRelacaoref')
                    ->orderByDesc('data_reavaliacao')
                    ->orderByDesc('Id'),
            ]))
            ->infolist(fn (BemImovel $record): array => [
                InfolistSection::make('Resumo')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        TextEntry::make('reavaliacoes_total')
                            ->label('Total de reavaliações')
                            ->state(fn (BemImovel $record): int => $record->reavaliacoesRelacaoRef->count())
                            ->badge()
                            ->color('primary'),
                    ]),
                InfolistSection::make('Reavaliações')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextEntry::make('reavaliacoes_vazias')
                            ->hiddenLabel()
                            ->state('Nenhuma reavaliação relacionada a este bem imóvel.')
                            ->badge()
                            ->color('gray')
                            ->visible(fn (BemImovel $record): bool => $record->reavaliacoesRelacaoRef->isEmpty()),
                        RepeatableEntry::make('reavaliacoesRelacaoRef')
                            ->hiddenLabel()
                            ->contained(false)
                            ->schema([
                                InfolistGrid::make(12)->schema([
                                    TextEntry::make('data_reavaliacao')
                                        ->label('Data da reavaliação')
                                        ->formatStateUsing(fn ($state): string => self::formatDate($state))
                                        ->columnSpan(2),
                                    TextEntry::make('estadoConservacaoRelacaoref.descEstadoConservacao')
                                        ->label('Estado de conservação')
                                        ->badge()
                                        ->placeholder('-')
                                        ->columnSpan(3),
                                    TextEntry::make('valor_reavaliacao')
                                        ->label('Valor da reavaliação')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->color('success')
                                        ->columnSpan(3),
                                    TextEntry::make('ajuste_contabil')
                                        ->label('Ajuste contábil')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->columnSpan(2),
                                    TextEntry::make('vida_util_reavaliacao')
                                        ->label('Vida útil')
                                        ->suffix(' meses')
                                        ->placeholder('-')
                                        ->columnSpan(2),
                                    TextEntry::make('valor_mercado')
                                        ->label('Valor de mercado')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->columnSpan(3),
                                    TextEntry::make('valor_aquisicao')
                                        ->label('Valor de aquisição')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->columnSpan(3),
                                    TextEntry::make('vida_util_remanescente_meses')
                                        ->label('Vida útil remanescente')
                                        ->suffix(' meses')
                                        ->placeholder('-')
                                        ->columnSpan(3),
                                    TextEntry::make('idade_aparente_anos')
                                        ->label('Idade aparente')
                                        ->suffix(' anos')
                                        ->placeholder('-')
                                        ->columnSpan(3),
                                    TextEntry::make('observacao')
                                        ->label('Observação')
                                        ->placeholder('-')
                                        ->columnSpan(12),
                                ]),
                            ])
                            ->visible(fn (BemImovel $record): bool => $record->reavaliacoesRelacaoRef->isNotEmpty()),
                    ]),
            ]);
    }

    private static function obrasTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('obras_action')
            ->label('Obras e Ampliações')
            ->icon('heroicon-o-home')
            ->modalHeading(fn (BemImovel $record): string => "Obras e ampliações do imóvel #{$record->Id}")
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalWidth('7xl')
            ->mountUsing(fn (BemImovel $record) => $record->load([
                'obrasRelacaoRef' => fn ($query) => $query
                    ->orderByDesc('data')
                    ->orderByDesc('id'),
            ]))
            ->infolist(fn (BemImovel $record): array => [
                InfolistSection::make('Resumo')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        InfolistGrid::make(2)->schema([
                            TextEntry::make('obras_total')
                                ->label('Total de obras e ampliações')
                                ->state(fn (BemImovel $record): int => $record->obrasRelacaoRef->count())
                                ->badge()
                                ->color('primary'),
                            TextEntry::make('obras_valor_total')
                                ->label('Valor total')
                                ->state(fn (BemImovel $record): string => self::formatMoney($record->obrasRelacaoRef->sum('valor')))
                                ->color('success'),
                        ]),
                    ]),
                InfolistSection::make('Obras e ampliações')
                    ->icon('heroicon-o-home')
                    ->schema([
                        TextEntry::make('obras_vazias')
                            ->hiddenLabel()
                            ->state('Nenhuma obra ou ampliação relacionada a este bem imóvel.')
                            ->badge()
                            ->color('gray')
                            ->visible(fn (BemImovel $record): bool => $record->obrasRelacaoRef->isEmpty()),
                        RepeatableEntry::make('obrasRelacaoRef')
                            ->hiddenLabel()
                            ->contained(false)
                            ->schema([
                                InfolistGrid::make(12)->schema([
                                    TextEntry::make('data')
                                        ->label('Data')
                                        ->formatStateUsing(fn ($state): string => self::formatDate($state))
                                        ->columnSpan(2),
                                    TextEntry::make('descricao')
                                        ->label('Descrição')
                                        ->placeholder('-')
                                        ->columnSpan(7),
                                    TextEntry::make('valor')
                                        ->label('Valor')
                                        ->formatStateUsing(fn ($state): string => self::formatMoney($state))
                                        ->color('success')
                                        ->columnSpan(3),
                                ]),
                            ])
                            ->visible(fn (BemImovel $record): bool => $record->obrasRelacaoRef->isNotEmpty()),
                    ]),
            ]);
    }

    // -------------------------------------------------------------------------
    // PAGES
    // -------------------------------------------------------------------------

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBemImovels::route('/'),
            'create' => Pages\CreateBemImovel::route('/create'),
            'edit' => Pages\EditBemImovel::route('/{record}/edit'),
        ];
    }

    // -------------------------------------------------------------------------
    // LÓGICA DE DEPRECIAÇÃO
    // -------------------------------------------------------------------------

    /**
     * Executa o cálculo de depreciação e envia notificação ao usuário.
     * Separa o tratamento de erros da lógica de negócio.
     */
    private static function executarCalculoDepreciacao(BemImovel $record): void
    {
        try {
            $total = self::calcularDepreciacao($record);

            Notification::make()
                ->title('Depreciação calculada')
                ->body("{$total} registro(s) gerado(s).")
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Não foi possível calcular a depreciação')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Calcula e persiste os registros de depreciação do imóvel.
     *
     * @return int Quantidade de registros gerados.
     *
     * @throws InvalidArgumentException quando os dados do imóvel são insuficientes.
     */
    private static function calcularDepreciacao(BemImovel $record): int
    {
        // Recarrega o registro com o relacionamento necessário para não depender
        // do estado que foi passado (pode estar incompleto/em cache).
        $imovel = BemImovel::query()
            ->with('elementoDespesaRelacaoRef')
            ->findOrFail($record->getKey());

        if ((int) $imovel->id_denominacao !== 1) {
            throw new InvalidArgumentException('A depreciação só pode ser calculada para imóveis com denominação 1.');
        }

        [$dataBase, $valor, $vidaUtil] = self::resolverParametrosDepreciacao($imovel);

        $percentualResidual = self::decimal($imovel->elementoDespesaRelacaoRef?->ValorResidual);
        $valorResidual = $valor * ($percentualResidual / 100);
        $depreciacaoMensal = ($valor - $valorResidual) / $vidaUtil;

        $rows = [];
        $seq = 1;
        $dataCalculo = $dataBase;

        for ($mesesRestantes = $vidaUtil; $mesesRestantes > 0; $mesesRestantes--) {
            $depreciacaoAcumulada = ($seq - 1) * $depreciacaoMensal;

            $rows[] = [
                'date_time' => now(),
                'Id_imovel' => $imovel->getKey(),
                'item' => $seq,
                'data_calculo' => $dataCalculo->toDateString(),
                'valor' => $valor,
                'vida_util' => $mesesRestantes,
                'valor_residual' => $valorResidual,
                'depreciacao_mensal' => $depreciacaoMensal,
                'depreciacao_acumulada' => $depreciacaoAcumulada,
                'valor_liquido_contabil' => $valor - $depreciacaoAcumulada,
            ];

            $seq++;
            $dataCalculo = $dataCalculo->addMonthNoOverflow()->startOfMonth();
        }

        DB::transaction(function () use ($imovel, $dataBase, $rows): void {
            Depreciacao::query()
                ->where('Id_imovel', $imovel->getKey())
                ->whereDate('data_calculo', '>=', $dataBase->toDateString())
                ->delete();

            // Inserção em lotes para evitar queries muito grandes.
            foreach (array_chunk($rows, 500) as $chunk) {
                Depreciacao::query()->insert($chunk);
            }
        });

        return count($rows);
    }

    /**
     * Extrai e valida os três parâmetros essenciais para o cálculo:
     * data-base, valor e vida útil.
     *
     * @return array{CarbonImmutable, float, int}
     *
     * @throws InvalidArgumentException
     */
    private static function resolverParametrosDepreciacao(BemImovel $imovel): array
    {
        $dataReavaliacao = self::dateOrNull($imovel->data_reavaliacao);

        $dataBase = $dataReavaliacao
            ? $dataReavaliacao->subMonthNoOverflow()
            : self::dateOrNull($imovel->data_aquisicao);

        if ($dataBase === null) {
            throw new InvalidArgumentException('Informe a data de aquisição ou a data de reavaliação do imóvel.');
        }

        $valor = $dataReavaliacao
            ? self::decimal($imovel->valor_reavaliado)
            : self::decimal($imovel->valor_historico_1a_avaliacao);

        if ($valor <= 0) {
            throw new InvalidArgumentException('O valor-base para depreciação deve ser maior que zero.');
        }

        $vidaUtil = $dataReavaliacao
            ? (int) $imovel->vida_util
            : (int) ($imovel->elementoDespesaRelacaoRef?->VidaUtil ?? 0);

        if ($vidaUtil <= 0) {
            throw new InvalidArgumentException('Informe a vida útil do imóvel ou do elemento de despesa.');
        }

        return [$dataBase, $valor, $vidaUtil];
    }

    // -------------------------------------------------------------------------
    // HELPERS DE FORMULÁRIO
    // -------------------------------------------------------------------------

    private static function section(string $heading, string $icon, array $schema): Forms\Components\Section
    {
        return Forms\Components\Section::make($heading)
            ->icon($icon)
            ->schema($schema);
    }

    private static function select(string $field, string $label, string $relationship, string $titleAttribute): Select
    {
        return Select::make($field)
            ->label($label)
            ->relationship($relationship, $titleAttribute)
            ->searchable()
            ->preload()
            ->native(false)
            ->optionsLimit(50)
            ->placeholder("Selecione {$label}");
    }

    private static function text(string $field, string $label): TextInput
    {
        return TextInput::make($field)
            ->label($label);
    }

    private static function number(string $field, string $label): TextInput
    {
        return TextInput::make($field)
            ->label($label)
            ->numeric()
            ->placeholder('0');
    }

    private static function textarea(string $field, string $label): Textarea
    {
        return Textarea::make($field)
            ->label($label)
            ->rows(3)
            ->placeholder($label);
    }

    private static function date(string $field, string $label): DatePicker
    {
        return DatePicker::make($field)
            ->label($label)
            ->displayFormat('d/m/Y')
            ->native(false)
            ->placeholder('dd/mm/aaaa');
    }

    private static function dateTime(string $field, string $label): DateTimePicker
    {
        return DateTimePicker::make($field)
            ->label($label)
            ->default(now())
            ->displayFormat('d/m/Y H:i:s')
            ->native(false);
    }

    // -------------------------------------------------------------------------
    // HELPERS DE FORMATAÇÃO
    // -------------------------------------------------------------------------

    private static function dateOrNull(mixed $value): ?CarbonImmutable
    {
        if (! $value || str_starts_with((string) $value, '0000-00-00')) {
            return null;
        }

        return CarbonImmutable::parse($value)->startOfDay();
    }

    private static function decimal(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '.', (string) $value);
    }

    private static function formatMoney(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }

    private static function formatDate(mixed $value): string
    {
        if (! $value || str_starts_with((string) $value, '0000-00-00')) {
            return '-';
        }

        return CarbonImmutable::parse($value)->format('d/m/Y');
    }
}
