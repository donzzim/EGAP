<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BemMovelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = BemMovel::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Administração';

    protected static ?string $modelLabel = 'Bem Móvel';

    protected static ?string $pluralModelLabel = 'Administração dos Bens Móveis';

    protected static ?string $recordTitleAttribute = 'NumPatrimonio';

    protected static ?string $slug = 'bens-moveis/adm-bens-moveis';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Administração do Bem Móvel')
                ->persistTabInQueryString()
                ->tabs([
                    self::tabPatrimonio(),
                    self::tabDescricao(),
                    self::tabLocalizacao(),
                    self::tabAquisicao(),
                    self::tabVeiculo(),
                    self::tabContabil(),
                    self::tabObservacao(),
                    self::tabSituacao(),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function tabPatrimonio(): Tabs\Tab
    {
        return Tabs\Tab::make('Patrimônio')
            ->icon('heroicon-o-identification')
            ->schema([
                self::section('Identificação Patrimonial', 'heroicon-o-qr-code', [
                    self::text('NumPatrimonio', 'Patrimônio')
                        ->required()
                        ->unique(ignoreRecord: true),
                    self::text('TomboSmarapd', 'Patrimônio de Conciliação'),
                    self::text('NumTomboSmarapd', 'Patrimônio sem Código de Barras'),
                    self::text('NumerodeSerie', 'Número de Série'),
                ])->description('Códigos usados para identificação e conciliação do bem.')->columns(2),
            ]);
    }

    private static function tabDescricao(): Tabs\Tab
    {
        return Tabs\Tab::make('Descrição')
            ->icon('heroicon-o-document-text')
            ->schema([
                self::section('Descrição do Bem', 'heroicon-o-archive-box', [
                    self::select('DescricaoResumidadoBem', 'Descrição Resumida', 'descricaoResumidaBemRef', 'Descricao')
                        ->columnSpanFull(),
                    self::textarea('Descricao', 'Descrição Detalhada')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull(),
                ])->description('Descrição resumida e detalhada utilizada na identificação do material.'),

                self::section('Classificação e Características', 'heroicon-o-squares-2x2', [
                    self::select('Marca', 'Marca', 'marcaRef', 'descricao'),
                    self::select('Modelo', 'Modelo', 'modeloRef', 'descricao'),
                    self::options('TipodoBem', 'Tipo do Bem', [
                        'Móveis' => 'Móveis',
                        'Imóveis' => 'Imóveis',
                        'Veículos' => 'Veículos',
                    ]),
                    self::options('EstadodeConservacao', 'Estado de Conservação', [
                        'ÓTIMO' => 'ÓTIMO',
                        'BOM' => 'BOM',
                        'REGULAR' => 'REGULAR',
                        'RUIM' => 'RUIM',
                        'SUCATA' => 'SUCATA',
                    ]),
                    self::options('Voltagem', 'Voltagem', [
                        'N/A' => 'Não Aplicável',
                        '110v' => '110v',
                        '220v' => '220v',
                        'BIVOLT' => 'BIVOLT',
                    ]),
                ])->columns(3),
            ]);
    }

    private static function tabLocalizacao(): Tabs\Tab
    {
        return Tabs\Tab::make('Localização')
            ->icon('heroicon-o-map-pin')
            ->schema([
                self::section('Localização Atual', 'heroicon-o-building-office', [
                    self::select('UnidadeJudiciaria', 'Unidade Judiciária', 'unidadeJudiciariaRef', 'Setor'),
                    self::select('Setor', 'Setor', 'setorRef', 'Setor'),
                    self::text('ComplementoSetor', 'Complemento'),
                    self::text('AndarSetor', 'Andar'),
                ])->description('Unidade e setor onde o bem está localizado.')->columns(2),
            ]);
    }

    private static function tabAquisicao(): Tabs\Tab
    {
        return Tabs\Tab::make('Aquisição')
            ->icon('heroicon-o-receipt-percent')
            ->schema([
                self::section('Documento de Aquisição', 'heroicon-o-document-check', [
                    self::select('Fornecedor', 'Fornecedor', 'fornecedorRef', 'NomeFornecedor'),
                    self::text('NotaFiscal', 'Nota Fiscal'),
                    self::date('DataCadastro', 'Data do Cadastro'),
                    MoneyInput::make('ValorAquisicao')->label('Valor de Aquisição'),
                    self::text('numero_processo', 'Número do Processo'),
                    self::text('nota_empenho', 'Nota de Empenho'),
                    self::text('nota_liquidacao', 'Nota de Liquidação'),
                    self::date('data_liquidacao', 'Data de Liquidação'),
                ])->description('Origem documental e financeira da incorporação do bem.')->columns(2),
            ]);
    }

    private static function tabVeiculo(): Tabs\Tab
    {
        return Tabs\Tab::make('Veículo')
            ->icon('heroicon-o-truck')
            ->schema([
                self::section('Dados do Veículo', 'heroicon-o-truck', [
                    self::text('Placa', 'Placa'),
                    self::text('Chassi', 'Chassi'),
                    self::text('Renavam', 'Renavam'),
                    self::options('Combustivel', 'Combustível', [
                        'Gasolina' => 'Gasolina',
                        'Diesel' => 'Diesel',
                        'Flex' => 'Flex',
                    ]),
                    self::number('AnoFabricacao', 'Ano de Fabricação'),
                    self::number('AnoModelo', 'Ano do Modelo'),
                ])->description('Preencha apenas para bens classificados como veículos.')->columns(3),
            ]);
    }

    private static function tabContabil(): Tabs\Tab
    {
        return Tabs\Tab::make('Contábil')
            ->icon('heroicon-o-calculator')
            ->schema([
                self::section('Classificação Contábil', 'heroicon-o-clipboard-document-list', [
                    self::select('ContaContabil', 'Conta Contábil', 'contaContabilRef', 'titulo'),
                    self::select('Produto', 'Elemento de Despesa', 'elementoDespesaRef', 'DescricaodaClasse'),
                    self::number('VidaUtil', 'Vida Útil')->suffix('meses'),
                ])->columns(3),

                self::section('Valores Contábeis', 'heroicon-o-banknotes', [
                    MoneyInput::make('ValorResidual')->label('Valor Residual'),
                    MoneyInput::make('DepreciacaoMensal')->label('Depreciação Mensal'),
                    MoneyInput::make('DepreciacaoAcumulada')->label('Depreciação Acumulada'),
                    MoneyInput::make('ValorReavaliado')->label('Valor Reavaliado'),
                ])->columns(2),
            ]);
    }

    private static function tabObservacao(): Tabs\Tab
    {
        return Tabs\Tab::make('Observação')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->schema([
                self::section('Informações Adicionais', 'heroicon-o-pencil-square', [
                    self::textarea('Observacao', 'Observações Gerais')
                        ->rows(6)
                        ->columnSpanFull(),
                ])->description('Registre informações complementares relevantes para o bem.'),
            ]);
    }

    private static function tabSituacao(): Tabs\Tab
    {
        return Tabs\Tab::make('Situação')
            ->icon('heroicon-o-check-circle')
            ->schema([
                self::section('Situação Atual', 'heroicon-o-check-badge', [
                    self::select('SituacaoBem', 'Situação', 'situacaoBemRef', 'descricao'),
                    self::date('DataBaixa', 'Data da Baixa')->disabled(),
                    self::text('ProcessoBaixa', 'Processo de Baixa')->disabled(),
                    self::date('DatadaReavaliacao', 'Data da Última Reavaliação')->disabled(),
                ])->description('Dados de situação, baixa e última reavaliação do bem.')->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->deferLoading()
            ->poll(null)
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'ultimaTransferencia.termoRel.arquivoDigital',
            ]))
            ->columns([
                TableColumns::text('NumPatrimonio', 'Patrimônio', isFirstColumn: true)
                    ->badge()
                    ->copyable()
                    ->copyMessage('Patrimônio copiado')
                    ->searchable(isIndividual: true),
                TableColumns::text('Descricao', 'Descrição')
                    ->limit(40)
                    ->tooltip(fn (BemMovel $record): ?string => $record->Descricao)
                    ->searchable(isIndividual: true),
                TableColumns::text('unidadeJudiciariaRef.Setor', 'Unidade')
                    ->wrap(),

                Tables\Columns\TextColumn::make('ultimo_termo_status')
                    ->label('Status do Termo')
                    ->getStateUsing(function (BemMovel $record): string {
                        if ($record->situacaoBemRef?->descricao === 'Transferência') {
                            return 'Aguardando validação';
                        }

                        $termo = $record->ultimaTransferencia?->termoRel;

                        if (! $termo?->num_termo) {
                            return 'Sem movimentação';
                        }

                        return (int) $termo->arquivoDigital?->situacao === 1
                            ? 'Assinado'
                            : 'Aguardando validação';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Assinado' => 'success',
                        'Aguardando validação' => 'warning',
                        default => 'gray',
                    }),

                TableColumns::money('ValorAquisicao', 'Valor de Aquisição'),
                TableColumns::text('situacaoBemRef.descricao', 'Situação')
                    ->badge()
                    ->color(fn (?string $state): string => match (mb_strtolower($state ?? '')) {
                        'ativo', 'em uso' => 'success',
                        'transferência' => 'warning',
                        'baixado', 'inservível' => 'danger',
                        default => 'gray',
                    }),
                TableColumns::text('marcaRef.descricao', 'Marca')
                    ->toggleable(isToggledHiddenByDefault: true),
                TableColumns::text('modeloRef.descricao', 'Modelo')
                    ->toggleable(isToggledHiddenByDefault: true),
                TableColumns::dateTime('date_time', 'Atualizado em')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('UnidadeJudiciaria')
                    ->label('Unidade Judiciária')
                    ->relationship('unidadeJudiciariaRef', 'Setor')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('SituacaoBem')
                    ->label('Situação')
                    ->relationship('situacaoBemRef', 'descricao')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('TipodoBem')
                    ->label('Tipo do Bem')
                    ->options(['Móveis' => 'Móveis', 'Imóveis' => 'Imóveis', 'Veículos' => 'Veículos']),
                Tables\Filters\SelectFilter::make('EstadodeConservacao')
                    ->label('Estado de Conservação')
                    ->options(['ÓTIMO' => 'ÓTIMO', 'BOM' => 'BOM', 'REGULAR' => 'REGULAR', 'RUIM' => 'RUIM', 'SUCATA' => 'SUCATA']),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Editar'),

                    Action::make('corrigir_informacao_individual')
                        ->label('Corrigir informações')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->form([
                            Grid::make(2)->schema([
                                Select::make('elemento')
                                    ->label('Elemento')
                                    ->options([
                                        'DescricaoResumidadoBem' => 'Descrição Resumida',
                                        'UnidadeJudiciaria' => 'Unidade Judiciária',
                                        'Setor' => 'Setor',
                                        'UnidadeGestora' => 'Unidade Gestora',
                                    ])->required()->live(),
                                Select::make('valor')
                                    ->label('Valor')
                                    ->options(function (Forms\Get $get) {
                                        $elemento = $get('elemento');
                                        if (! $elemento) {
                                            return [];
                                        }

                                        return match ($elemento) {
                                            'DescricaoResumidadoBem' => DB::connection('egap')->table('mat_descricaoresumida')->orderBy('Descricao')->pluck('Descricao', 'id'),
                                            'UnidadeJudiciaria' => DB::connection('egap')->table('mat_setores')->whereRaw('id = CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'),
                                            'Setor' => DB::connection('egap')->table('mat_setores')->whereRaw('id != CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'),
                                            'UnidadeGestora' => DB::connection('egap')->table('mat_unidadegestora')->orderBy('nome')->pluck('nome', 'id'),
                                            default => [],
                                        };
                                    })->searchable()->required(),
                            ]),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([$data['elemento'] => $data['valor']]);
                            Notification::make()->title('Informação corrigida!')->success()->send();
                        }),

                    Action::make('historico_movimentacao')
                        ->label('Histórico')
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->modalHeading(fn ($record) => "Histórico de Movimentações - {$record->NumPatrimonio}")
                        ->modalSubmitAction(false)
                        ->modalContent(function ($record) {
                            $movimentacoes = DB::connection('egap')
                                ->table('mat_transferencia')
                                ->where('NumPatrimonio', $record->NumPatrimonio)
                                ->orderBy('date_time', 'desc')
                                ->get();

                            return view('patrimonio.historico-movimentacoes', ['historico' => $movimentacoes]);
                        }),

                    Action::make('termos_digitalizados')
                        ->label('Termos Digitalizados')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('gray')
                        ->modalHeading(fn ($record) => "Termos Digitalizados - Patrimônio {$record->NumPatrimonio}")
                        ->modalSubmitAction(false)
                        ->modalContent(function ($record) {
                            $termos = DB::connection('egap')
                                ->table('mat_transferencia')
                                ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                                ->join('mat_termos', 'mat_transferencia.Termo', '=', 'mat_termos.id')
                                ->where('mat_transferencia.NumPatrimonio', $record->NumPatrimonio)
                                ->select(['mat_termos.id as TermoID', 'mat_termos.num_termo', 'mat_termos.ano_termo', 'mat_transferencia.date_time', 'mat_arquivodigital.situacao as StatusArquivo'])
                                ->orderBy('mat_transferencia.date_time', 'desc')
                                ->get()->unique('TermoID');

                            return view('patrimonio.termos-digitalizados-modal', ['termos' => $termos]);
                        }),

                    Action::make('conciliar_bem')
                        ->label('Conciliar bem')
                        ->icon('heroicon-s-check-badge')
                        ->color('warning')
                        ->form([
                            Grid::make(2)->schema([
                                TextInput::make('NumPatrimonio')->label('Patrimônio Atual')->disabled(),
                                TextInput::make('TomboSmarapd')->label('Patrimônio (Smarapd)')->required(),
                                DatePicker::make('DatadaReavaliacao')->label('Data Conciliação')->default(now())->required(),
                            ]),
                        ])
                        ->fillForm(fn ($record) => ['NumPatrimonio' => $record->NumPatrimonio, 'TomboSmarapd' => $record->TomboSmarapd, 'DatadaReavaliacao' => $record->DatadaReavaliacao])
                        ->action(function ($record, array $data) {
                            $record->update(['TomboSmarapd' => $data['TomboSmarapd'], 'DatadaReavaliacao' => $data['DatadaReavaliacao']]);
                            DB::connection('egap')->table('mat_conciliacao')->insert([
                                'date_time' => now(), 'numero_patrimonio' => $record->NumPatrimonio, 'descricao' => $record->Descricao,
                                'data_conciliacao' => $data['DatadaReavaliacao'], 'patrimonio' => $data['TomboSmarapd'],
                            ]);
                            Notification::make()->title('Conciliado com sucesso!')->success()->send();
                        }),

                    Action::make('transferir_bem')
                        ->label('Transferir bens')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('warning')
                        ->form([
                            Grid::make(2)->schema([
                                Select::make('unidade_atual')
                                    ->label('Unidade Destino')
                                    ->options(fn () => DB::connection('egap')->table('mat_setores')->whereRaw('id = CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'))
                                    ->searchable()->live()->required(),
                                Select::make('setor_atual')
                                    ->label('Setor Destino')
                                    ->options(fn (Forms\Get $get) => $get('unidade_atual') ? DB::connection('egap')->table('mat_setores')->where('CodigoPai', $get('unidade_atual'))->orderBy('Setor')->pluck('Setor', 'id') : [])
                                    ->searchable()->required(),
                                TextInput::make('pedido_no')->label('Pedido Nº'),
                                TextInput::make('observacao')->label('Observação'),
                            ]),
                        ])
                        ->action(function ($record, array $data) {
                            DB::connection('egap')->transaction(function () use ($record, $data) {
                                $user = auth()->id();
                                $anoAtual = now()->year;
                                $id_termo = DB::connection('egap')->table('mat_termos')->insertGetId([
                                    'date_time' => now(),
                                    'num_termo' => DB::connection('egap')->table('mat_termos')->where('ano_termo', $anoAtual)->max('num_termo') + 1 ?: 1,
                                    'ano_termo' => $anoAtual,
                                    'atualizado_em' => now(),
                                    'atualizado_por' => $user,
                                    'pedido_no' => $data['pedido_no'] ?? null,
                                ]);

                                DB::connection('egap')->table('mat_arquivodigital')->insert([
                                    'date_time' => now(), 'termo' => $id_termo, 'situacao' => 0, 'atualizado_em' => now(), 'atualizado_por' => $user,
                                ]);

                                DB::connection('egap')->table('mat_transferencia')->insert([
                                    'date_time' => now(),
                                    'NumPatrimonio' => $record->NumPatrimonio,
                                    'UnidadeAnterior' => $record->UnidadeJudiciaria,
                                    'SetorAnterior' => $record->Setor,
                                    'UnidadeAtual' => $data['unidade_atual'],
                                    'SetorAtual' => $data['setor_atual'],
                                    'Usuario' => $user,
                                    'Termo' => $id_termo,
                                    'pedido_no' => $data['pedido_no'] ?? null,
                                ]);
                            });
                            Notification::make()->title('Solicitação registrada! Aguarde assinatura no sistema antigo.')->warning()->send();
                        }),

                    Action::make('vincular_baixa')
                        ->label('Vincular para baixa')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->color('danger')
                        ->form([
                            TextInput::make('processo_baixa')->label('Processo Nº')->required(),
                            Select::make('situacao_baixa_id')->label('Motivo')
                                ->options(fn () => DB::connection('egap')->table('mat_situacao')->where('situacao', 'Baixado')->pluck('descricao', 'id'))
                                ->required(),
                            DatePicker::make('data_baixa')->label('Data')->default(now())->required(),
                        ])
                        ->action(function ($record, array $data) {
                            DB::connection('egap')->transaction(function () use ($record, $data) {
                                DB::connection('egap')->table('mat_baixa')->insert(['date_time' => now(), 'Usuario' => auth()->id(), 'NumeroProcesso' => $data['processo_baixa'], 'DataBaixa' => $data['data_baixa']]);
                                $record->update(['ProcessoBaixa' => $data['processo_baixa'], 'DataBaixa' => $data['data_baixa'], 'SituacaoBem' => $data['situacao_baixa_id']]);
                            });
                            Notification::make()->title('Baixa registrada!')->success()->send();
                        }),

                    Action::make('reavaliar_bem')
                        ->label('Reavaliação')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->form([
                            Select::make('estado_conservacao')->label('Estado de Conservação')->options([10 => 'Ótimo', 8 => 'Bom', 5 => 'Regular', 2 => 'Ruim'])->required(),
                        ])->action(function ($record, array $data) {
                            Notification::make()->title('Reavaliado!')->success()->send();
                        }),

                    Action::make('calculo_depreciacao')
                        ->label('Depreciação')
                        ->icon('heroicon-o-calculator')
                        ->color('success')
                        ->url(fn ($record) => route('depreciacao.imprimir', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    // Imprime exclusivamente o Termo de Responsabilidade de Transferência.
                    // Baixa é um documento diferente (Certidão/Termo de Baixa) com rota própria — não misturar aqui.
                    Action::make('imprimir_termo')
                        ->label('Imprimir termo')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->disabled(function ($record) {
                            // Só habilita se existir transferência assinada com termo válido em mat_transferencia
                            return ! DB::connection('egap')
                                ->table('mat_transferencia')
                                ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                                ->join('mat_termos', 'mat_transferencia.Termo', '=', 'mat_termos.id')
                                ->where('mat_transferencia.NumPatrimonio', $record->NumPatrimonio)
                                ->where('mat_arquivodigital.situacao', 1)
                                ->whereNotNull('mat_termos.num_termo')
                                ->exists();
                        })
                        ->tooltip(function ($record) {
                            $status = DB::connection('egap')
                                ->table('mat_transferencia')
                                ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                                ->join('mat_termos', 'mat_transferencia.Termo', '=', 'mat_termos.id')
                                ->where('mat_transferencia.NumPatrimonio', $record->NumPatrimonio)
                                ->select('mat_arquivodigital.situacao', 'mat_termos.num_termo')
                                ->orderBy('mat_transferencia.date_time', 'desc')
                                ->first();

                            if (! $status) {
                                return 'Sem movimentação de transferência registrada.';
                            }

                            if ($status->situacao != 1) {
                                return 'Aguardando assinatura/validação do destinatário no sistema antigo.';
                            }

                            if (empty($status->num_termo)) {
                                return 'Inconsistência: Termo assinado, mas sem número de termo vinculado.';
                            }

                            return 'Imprimir Termo de Responsabilidade';
                        })
                        ->url(function ($record) {
                            $valido = DB::connection('egap')
                                ->table('mat_transferencia')
                                ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                                ->join('mat_termos', 'mat_transferencia.Termo', '=', 'mat_termos.id')
                                ->where('mat_transferencia.NumPatrimonio', $record->NumPatrimonio)
                                ->where('mat_arquivodigital.situacao', 1)
                                ->whereNotNull('mat_termos.num_termo')
                                ->exists();

                            return $valido ? route('termo.imprimir', ['id' => $record->id]) : null;
                        })
                        ->openUrlInNewTab(),

                    Tables\Actions\ViewAction::make()->label('Visualizar'),
                ])->label('Opções')->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('corrigir_informacoes_lote')
                        ->label('Corrigir informações')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->deselectRecordsAfterCompletion()
                        ->form([
                            Grid::make(2)->schema([
                                Select::make('elemento')->label('Elemento')->options(['DescricaoResumidadoBem' => 'Descrição Resumida', 'UnidadeJudiciaria' => 'Unidade Judiciária', 'Setor' => 'Setor', 'UnidadeGestora' => 'Unidade Gestora'])->required()->live(),
                                Select::make('valor')->label('Valor')->options(function (Forms\Get $get) {
                                    if (! $get('elemento')) {
                                        return [];
                                    }

                                    return match ($get('elemento')) {
                                        'DescricaoResumidadoBem' => DB::connection('egap')->table('mat_descricaoresumida')->orderBy('Descricao')->pluck('Descricao', 'id'),
                                        'UnidadeJudiciaria' => DB::connection('egap')->table('mat_setores')->whereRaw('id = CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'),
                                        'Setor' => DB::connection('egap')->table('mat_setores')->whereRaw('id != CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'),
                                        'UnidadeGestora' => DB::connection('egap')->table('mat_unidadegestora')->orderBy('nome')->pluck('nome', 'id'),
                                        default => [],
                                    };
                                })->searchable()->required(),
                            ]),
                        ])
                        ->action(function (Collection $records, array $data) {
                            DB::connection('egap')->transaction(function () use ($records, $data) {
                                $ids = $records->pluck('id')->toArray();
                                DB::connection('egap')->table('mat_patrimonio')->whereIn('id', $ids)->update([$data['elemento'] => $data['valor']]);
                            });
                            Notification::make()->title('Informações corrigidas em massa!')->success()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ])->label('Ações em Grupo'),
            ]);
    }

    private static function section(string $heading, string $icon, array $schema): Section
    {
        return Section::make($heading)
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

    private static function options(string $field, string $label, array $options): Select
    {
        return Select::make($field)
            ->label($label)
            ->options($options)
            ->native(false)
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBemMovels::route('/'),
            'create' => Pages\CreateBemMovel::route('/create'),
            'edit' => Pages\EditBemMovel::route('/{record}/edit'),
        ];
    }
}
