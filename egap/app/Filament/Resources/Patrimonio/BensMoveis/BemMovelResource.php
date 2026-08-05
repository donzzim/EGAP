<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableModalAction;
use App\Models\Almoxarifado\Pedidos;
use App\Models\Cadastro\ComplementoSetor;
use App\Models\Cadastro\ElementoDespesa;
use App\Models\Cadastro\Modelos;
use App\Models\Cadastro\Setores;
use App\Models\Cadastro\UnidadesDeMedida;
use App\Models\Patrimonio\BensMoveis\Baixa;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\ItemBaixa;
use App\Models\Patrimonio\BensMoveis\SituacaoBemMovel;
use App\Models\Patrimonio\BensMoveis\TransferenciaBemMovel;
use App\Services\Patrimonio\RecalcularDepreciacaoService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Throwable;

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
            Section::make('Identificação do Bem')
                ->description('Números de controle patrimonial que identificam o bem.')
                ->icon('heroicon-o-identification')
                ->schema([
                    TextInput::make('NumPatrimonio')
                        ->label('Nº Patrimônio')
                        ->placeholder('Informe o número do patrimônio')
                        ->numeric()
                        ->unique(ignoreRecord: true)
                        ->required(),

                    TextInput::make('NumerodePatAnterior')
                        ->label('Nº Patrimônio Anterior')
                        ->placeholder('Informe o número anterior, se houver'),

                    TextInput::make('NumerodeSerie')
                        ->label('Número de Série')
                        ->placeholder('Informe o número de série'),

                    TextInput::make('TomboSmarapd')
                        ->label('Tombo Smarapd')
                        ->placeholder('Informe o tombo Smarapd'),

                    TextInput::make('NumTomboSmarapd')
                        ->label('Nº do Tombo Smarapd')
                        ->numeric()
                        ->placeholder('Informe o número do tombo'),

                    Select::make('Unidade')
                        ->label('Unidade de Medida')
                        ->relationship('unidadeMedidaRef', 'Unidade')
                        ->getOptionLabelFromRecordUsing(fn (UnidadesDeMedida $record): string => filled($record->Sigla)
                            ? "{$record->Sigla} - {$record->Unidade}"
                            : $record->Unidade)
                        ->searchable(['Sigla', 'Unidade'])
                        ->preload()
                        ->native(false)
                        ->placeholder('Selecione a unidade de medida'),
                ])
                ->columns(3),

            Section::make('Descrição e Classificação')
                ->description('Descrição detalhada e características do bem.')
                ->icon('heroicon-o-tag')
                ->schema([
                    Select::make('DescricaoResumidadoBem')
                        ->label('Descrição Resumida')
                        ->relationship('descricaoResumidaBemRef', 'Descricao')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('Selecione a descrição resumida'),

                    Select::make('id_descricaodetalhada')
                        ->label('Descrição Detalhada')
                        ->relationship('descricaoDetalhadaRef', 'descricao_detalhada')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('Selecione a descrição detalhada'),

                    Select::make('Marca')
                        ->label('Marca')
                        ->relationship('marcaRef', 'descricao')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('Modelo', null))
                        ->placeholder('Selecione a marca'),

                    Select::make('Modelo')
                        ->label('Modelo')
                        ->placeholder(fn (Get $get) => blank($get('Marca'))
                            ? 'Selecione primeiro a marca'
                            : 'Selecione o modelo'
                        )
                        ->options(fn (Get $get): array => Modelos::query()
                            ->when(
                                $get('Marca'),
                                fn ($query, $marca) => $query->where('marca', $marca)
                            )
                            ->orderBy('descricao')
                            ->pluck('descricao', 'id')
                            ->toArray()
                        )
                        ->disabled(fn (Get $get) => blank($get('Marca')))
                        ->searchable()
                        ->native(false),

                    TextInput::make('TipodoBem')
                        ->label('Tipo do Bem')
                        ->placeholder('Informe o tipo do bem'),

                    TextInput::make('EstadodeConservacao')
                        ->label('Estado de Conservação')
                        ->placeholder('Informe o estado de conservação'),

                    TextInput::make('Voltagem')
                        ->placeholder('Informe a voltagem'),

                    TextInput::make('Categoria')
                        ->placeholder('Informe a categoria'),

                    TextInput::make('Combustivel')
                        ->placeholder('Informe o combustível'),

                    TextInput::make('ClassificacaoInservivel')
                        ->label('Classificação de Inservível')
                        ->placeholder('Informe a classificação, se inservível'),

                    Textarea::make('Descricao')
                        ->label('Descrição Detalhada do Bem')
                        ->placeholder('Descreva o bem detalhadamente')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(3),

            Section::make('Situação e Identificação Veicular')
                ->description('Situação atual do bem e dados de identificação, quando se tratar de veículo.')
                ->icon('heroicon-o-shield-check')
                ->schema([
                    Select::make('SituacaoBem')
                        ->label('Situação do Bem')
                        ->relationship('situacaoBemRef', 'descricao')
                        ->getOptionLabelFromRecordUsing(fn (SituacaoBemMovel $record): string => $record->descricao_completa)
                        ->searchable(['descricao', 'situacao'])
                        ->preload()
                        ->native(false)
                        ->placeholder('Selecione a situação do bem'),

                    TextInput::make('Placa')
                        ->maxLength(8)
                        ->placeholder('Informe a placa'),

                    TextInput::make('Chassi')
                        ->placeholder('Informe o chassi'),

                    TextInput::make('Renavam')
                        ->placeholder('Informe o Renavam'),

                    TextInput::make('AnoFabricacao')
                        ->label('Ano de Fabricação')
                        ->numeric()
                        ->maxLength(4)
                        ->placeholder('AAAA'),

                    TextInput::make('AnoModelo')
                        ->label('Ano do Modelo')
                        ->numeric()
                        ->maxLength(4)
                        ->placeholder('AAAA'),
                ])
                ->columns(3),

            Section::make('Localização do Bem')
                ->description('Unidade judiciária, setor e localização física atual do bem.')
                ->icon('heroicon-o-building-office-2')
                ->schema([
                    Select::make('UnidadeJudiciaria')
                        ->label('Unidade Judiciária')
                        ->placeholder('Selecione a unidade judiciária')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->native(false)
                        ->options(fn () => Setores::query()
                            ->whereColumn('id', 'CodigodaUO')
                            ->orderBy('UnidadeOrganizacional')
                            ->pluck('UnidadeOrganizacional', 'CodigoPai')
                            ->toArray()
                        )
                        ->afterStateUpdated(fn (Set $set) => $set('Setor', null)),

                    Select::make('Setor')
                        ->placeholder(fn (Get $get) => blank($get('UnidadeJudiciaria'))
                            ? 'Selecione primeiro a unidade judiciária'
                            : 'Selecione o setor'
                        )
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->options(fn (Get $get) => Setores::query()
                            ->when(
                                $get('UnidadeJudiciaria'),
                                fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                            )
                            ->orderBy('Setor')
                            ->pluck('Setor', 'id')
                            ->toArray()
                        )
                        ->disabled(fn (Get $get) => blank($get('UnidadeJudiciaria'))),

                    Select::make('ComplementoSetor')
                        ->label('Complemento')
                        ->relationship('complementoSetorRef', 'descricao')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('Selecione o complemento'),

                    Select::make('AndarSetor')
                        ->label('Andar')
                        ->placeholder('Informe o andar')
                        ->options(self::andaresOptions())
                        ->native(false),
                ])
                ->columns(2),

            Section::make('Aquisição e Fornecimento')
                ->description('Dados do processo de aquisição, fornecedor e controle contábil.')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Select::make('Produto')
                        ->label('Elemento de Despesa')
                        ->relationship('produtoRef', 'CodigodaClasse')
                        ->getOptionLabelFromRecordUsing(fn (ElementoDespesa $record): string => filled($record->DescricaodaClasse)
                            ? "{$record->CodigodaClasse} - {$record->DescricaodaClasse}"
                            : (string) $record->CodigodaClasse)
                        ->searchable(['CodigodaClasse', 'DescricaodaClasse'])
                        ->preload()
                        ->native(false)
                        ->placeholder('Selecione o elemento de despesa'),

                    Select::make('ContaContabil')
                        ->label('Conta Contábil')
                        ->relationship('contaContabilRef', 'titulo')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('Selecione a conta contábil'),

                    Select::make('Fornecedor')
                        ->relationship('fornecedorRef', 'NomeFornecedor')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('Selecione o fornecedor'),

                    Select::make('NotaFiscal')
                        ->label('Nota Fiscal')
                        ->relationship('notaFiscalRef', 'num_documento')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('Selecione a nota fiscal'),

                    TextInput::make('FormaAquisicao')
                        ->label('Forma de Aquisição')
                        ->placeholder('Informe a forma de aquisição'),

                    TextInput::make('SiglaUnidadeGestora')
                        ->label('Sigla da Unidade Gestora')
                        ->placeholder('Informe a sigla da unidade gestora'),

                    TextInput::make('unidade_gestora')
                        ->label('Unidade Gestora')
                        ->placeholder('Informe a unidade gestora'),

                    TextInput::make('Lote')
                        ->numeric()
                        ->placeholder('Informe o lote'),

                    TextInput::make('NumeracaoInicial')
                        ->label('Numeração Inicial')
                        ->placeholder('Informe a numeração inicial'),

                    TextInput::make('NumeracaoFinal')
                        ->label('Numeração Final')
                        ->placeholder('Informe a numeração final'),

                    TextInput::make('MesdeReferencia')
                        ->label('Mês de Referência')
                        ->placeholder('Informe o mês de referência'),

                    TextInput::make('Garantia')
                        ->placeholder('Informe a garantia'),

                    TextInput::make('numero_processo')
                        ->label('Número do Processo')
                        ->placeholder('Informe o número do processo'),

                    TextInput::make('nota_empenho')
                        ->label('Nota de Empenho')
                        ->placeholder('Informe a nota de empenho'),

                    TextInput::make('nota_liquidacao')
                        ->label('Nota de Liquidação')
                        ->placeholder('Informe a nota de liquidação'),

                    DatePicker::make('data_liquidacao')
                        ->label('Data da Liquidação')
                        ->displayFormat('d/m/Y')
                        ->native(false)
                        ->closeOnDateSelection(),

                    DatePicker::make('DatadeVencimento')
                        ->label('Data de Vencimento')
                        ->displayFormat('d/m/Y')
                        ->native(false)
                        ->closeOnDateSelection(),
                ])
                ->columns(3),

            Section::make('Valores e Depreciação')
                ->description('Valores de aquisição e reavaliação utilizados no cálculo de depreciação. Os valores contábeis atuais são atualizados automaticamente pela ação "Gerar Novo Cálculo".')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    MoneyInput::make('ValorAquisicao')
                        ->label('Valor de Aquisição')
                        ->required(),

                    MoneyInput::make('ValordeMercado')
                        ->label('Valor de Mercado'),

                    DatePicker::make('DatadeIncorporacao')
                        ->label('Data de Incorporação')
                        ->displayFormat('d/m/Y')
                        ->native(false)
                        ->closeOnDateSelection(),

                    DatePicker::make('DataDisponibilizacao')
                        ->label('Data da Disponibilização')
                        ->displayFormat('d/m/Y')
                        ->native(false)
                        ->closeOnDateSelection(),

                    DatePicker::make('DatadaReavaliacao')
                        ->label('Data da Reavaliação')
                        ->displayFormat('d/m/Y')
                        ->native(false)
                        ->closeOnDateSelection(),

                    MoneyInput::make('ValordaReavaliacao')
                        ->label('Valor da Reavaliação'),

                    TextInput::make('VidaUtilReavaliacao')
                        ->label('Vida Útil da Reavaliação')
                        ->numeric()
                        ->suffix('meses')
                        ->placeholder('0'),
                ])
                ->columns(3),

            Section::make('Observações')
                ->description('Adicione informações complementares sobre o bem, se necessário.')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->schema([
                    RichEditor::make('Observacao')
                        ->label('')
                        ->placeholder('Digite aqui alguma observação ou informação adicional...')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'bulletList',
                            'orderedList',
                            'link',
                            'undo',
                            'redo',
                        ])
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(false),
        ]);
    }

    private static function andaresOptions(): array
    {
        return [
            'SUB-SOLO' => 'Sub-solo',
            'TERREO' => 'Térreo',
            '1' => '1º andar',
            '2' => '2º andar',
            '3' => '3º andar',
            '4' => '4º andar',
            '5' => '5º andar',
            '6' => '6º andar',
            '7' => '7º andar',
            '8' => '8º andar',
            '9' => '9º andar',
            '10' => '10º andar',
            '11' => '11º andar',
            '12' => '12º andar',
            '13' => '13º andar',
            '14' => '14º andar',
            '15' => '15º andar',
            '16' => '16º andar',
            '17' => '17º andar',
            '18' => '18º andar',
            '19' => '19º andar',
            '20' => '20º andar',
        ];
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('ultimaTransferencia'))
            ->columns([
                TableColumns::text('NumPatrimonio', 'Patrimônio')
                    ->badge(),
                TableColumns::text('NumerodeSerie', 'Número de Série'),
                TableColumns::text('Descricao', 'Descrição Detalhada'),
                TableColumns::text('modeloRef.descricao', 'Modelo'),
                TableColumns::text('unidadeJudiciariaRef.UnidadeOrganizacional', 'Unidade Judiciária'),
                TableColumns::text('setorRef.Setor', 'Setor'),
                TableColumns::text('complementoSetorRef.descricao', 'Complemento do Setor'),
                TableColumns::money('Valor', 'Valor de Aquisição')
                    ->badge()
                    ->color('success'),
                TableColumns::date('DataDisponibilizacao', 'Data da Disponibilização'),
                TableColumns::date('DatadaReavaliacao', 'Data da Reavaliação'),
            ])
            ->actions([
                ...TableDefaults::actions(),
                ActionGroup::make([
                    self::imprimirTermoTableAction(),
                    self::transferirBensTableAction(),
                    self::historicoMovimentacoesTableAction(),
                    self::vincularBensTableAction(),
                    self::calculoDepreciacaoMensalTableAction(),
                    self::gerarNovoCalculoTableAction(),
                    self::conciliarBemTableAction(),
                    self::termoDigitalizadoTableAction(),
                    self::reavaliarBensTableAction(),
                    self::corrigirInformacoesTableAction(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('Imprimir Bens')
                    ->icon('heroicon-o-printer'),
                ...TableDefaults::bulkActions(),
            ]);
    }

    private static function imprimirTermoTableAction(): Action
    {
        return Action::make('imprimir_termo')
            ->label('Imprimir Termo')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->visible(fn (BemMovel $record): bool => filled($record->ultimaTransferencia?->Termo))
            ->url(fn (BemMovel $record): string => route('termo.imprimir', ['id' => $record->ultimaTransferencia->Termo]))
            ->openUrlInNewTab();
    }

    private static function transferirBensTableAction(): Action
    {
        return Action::make('transferir_bens')
            ->label('Transferir Bens')
            ->icon('heroicon-o-arrow-right-circle')
            ->modalHeading(fn (BemMovel $record): string => "Transferir Bem #{$record->NumPatrimonio}")
            ->modalSubmitActionLabel('Transferir')
            ->fillForm(fn (BemMovel $record): array => [
                'UnidadeJudiciaria' => $record->UnidadeJudiciaria,
                'Setor' => $record->Setor,
                'ComplementoSetor' => $record->ComplementoSetor,
                'Andar' => $record->AndarSetor,
                'Observacao' => $record->Observacao,
            ])
            ->form([
                Section::make('Localização do Bem')
                    ->description('Informe a unidade judiciária e o setor onde o bem será transferido.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Select::make('UnidadeJudiciaria')
                            ->label('Unidade Judiciária')
                            ->placeholder('Selecione a unidade judiciária')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->native(false)
                            ->options(fn () => Setores::query()
                                ->whereColumn('id', 'CodigodaUO')
                                ->orderBy('UnidadeOrganizacional')
                                ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                ->toArray()
                            )
                            ->afterStateUpdated(fn (Set $set) => $set('Setor', null))
                            ->columnSpan(1),

                        Select::make('Setor')
                            ->label('Setor')
                            ->placeholder(fn (Get $get) => blank($get('UnidadeJudiciaria'))
                                ? 'Selecione primeiro a unidade judiciária'
                                : 'Selecione o setor'
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(fn (Get $get) => Setores::query()
                                ->when(
                                    $get('UnidadeJudiciaria'),
                                    fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                                )
                                ->orderBy('Setor')
                                ->pluck('Setor', 'id')
                                ->toArray()
                            )
                            ->disabled(fn (Get $get) => blank($get('UnidadeJudiciaria')))
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Detalhes da Localização')
                    ->description('Informe detalhes adicionais sobre a localização física do bem.')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Select::make('ComplementoSetor')
                            ->label('Complemento')
                            ->placeholder('Selecione o complemento')
                            ->options(fn (): array => ComplementoSetor::query()
                                ->orderBy('descricao')
                                ->pluck('descricao', 'id')
                                ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->columnSpan(2),

                        Select::make('Andar')
                            ->label('Andar')
                            ->placeholder('Informe o andar')
                            ->options(self::andaresOptions())
                            ->native(false)
                            ->columnSpan(1),

                        Select::make('PedidoNo')
                            ->label('Pedido Nº')
                            ->placeholder('Selecione o pedido relacionado')
                            ->options(fn (): array => Pedidos::query()
                                ->orderByDesc('id')
                                ->limit(100)
                                ->get(['id', 'num_protocolo'])
                                ->mapWithKeys(fn (Pedidos $pedido): array => [
                                    $pedido->id => filled($pedido->num_protocolo)
                                        ? "{$pedido->id} - Protocolo {$pedido->num_protocolo}"
                                        : (string) $pedido->id,
                                ])
                                ->toArray()
                            )
                            ->searchable()
                            ->native(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Observações')
                    ->description('Adicione informações complementares sobre a transferência, se necessário.')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        RichEditor::make('Observacao')
                            ->label('')
                            ->placeholder('Digite aqui alguma observação ou informação adicional...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ])
            ->action(function (array $data, BemMovel $record): void {
                try {
                    DB::transaction(function () use ($data, $record): void {
                        TransferenciaBemMovel::create([
                            'NumPatrimonio' => $record->NumPatrimonio,
                            'UnidadeAnterior' => $record->UnidadeJudiciaria,
                            'SetorAnterior' => $record->Setor,
                            'ComplementoAnterior' => $record->ComplementoSetor,
                            'AndarAnterior' => $record->AndarSetor,
                            'UnidadeAtual' => $data['UnidadeJudiciaria'],
                            'SetorAtual' => $data['Setor'],
                            'ComplementoAtual' => $data['ComplementoSetor'],
                            'AndarAtual' => $data['Andar'],
                            'pedido_no' => $data['PedidoNo'],
                        ]);

                        $record->update([
                            'UnidadeJudiciaria' => $data['UnidadeJudiciaria'],
                            'Setor' => $data['Setor'],
                            'ComplementoSetor' => $data['ComplementoSetor'],
                            'AndarSetor' => $data['Andar'],
                            'Observacao' => $data['Observacao'],
                        ]);
                    });
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('Não foi possível transferir o bem.')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Bem transferido com sucesso.')
                    ->success()
                    ->send();
            });
    }

    private static function historicoMovimentacoesTableAction(): Action
    {
        return TableModalAction::make('historico_movimentacoes')
            ->label('Histórico de Movimentações')
            ->icon('heroicon-o-arrows-right-left')
            ->color('gray')
            ->modalHeading(fn (BemMovel $record): string => "Histórico de Movimentações #{$record->NumPatrimonio}")
            ->modalContent(fn (BemMovel $record): HtmlString => new HtmlString(
                Livewire::mount(
                    'patrimonio.historico-movimentacoes-modal',
                    ['numPatrimonio' => (int) $record->NumPatrimonio],
                    "historico-movimentacoes-{$record->getKey()}",
                )
            ));
    }

    private static function vincularBensTableAction(): Action
    {
        return Action::make('vincular_bens')
            ->label('Vincular Bens')
            ->color('gray')
            ->icon('heroicon-o-plus')
            ->modalHeading(fn (BemMovel $record): string => "Vincular Bem #{$record->NumPatrimonio}")
            ->modalSubmitActionLabel('Vincular')
            ->fillForm(fn (BemMovel $record): array => [
                'Detalhes' => $record->Observacao,
            ])
            ->form([
                Select::make('id_baixa')
                    ->label('Processo')
                    ->placeholder('Selecione o processo de baixa')
                    ->options(fn (): array => Baixa::query()
                        ->whereNull('DataBaixa')
                        ->orderByDesc('id')
                        ->get(['id', 'NumeroProcesso', 'Requisitante'])
                        ->mapWithKeys(fn (Baixa $baixa): array => [
                            $baixa->id => "{$baixa->NumeroProcesso} - {$baixa->Requisitante}",
                        ])
                        ->toArray()
                    )
                    ->searchable()
                    ->native(false)
                    ->required(),

                Select::make('id_situacao')
                    ->label('Tipo da Baixa')
                    ->placeholder('Selecione o tipo da baixa')
                    ->options(fn (): array => SituacaoBemMovel::query()
                        ->orderBy('descricao')
                        ->pluck('descricao', 'id')
                        ->toArray()
                    )
                    ->searchable()
                    ->native(false)
                    ->required(),

                Textarea::make('Detalhes')
                    ->label('Detalhes')
                    ->placeholder('Informe detalhes sobre a baixa deste bem')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, BemMovel $record): void {
                try {
                    DB::transaction(function () use ($data, $record): void {
                        ItemBaixa::create([
                            'id_baixa' => $data['id_baixa'],
                            'id_bem' => $record->id,
                            'id_situacao' => $data['id_situacao'],
                        ]);

                        $record->update([
                            'Observacao' => $data['Detalhes'],
                        ]);
                    });
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('Não foi possível vincular o bem.')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Bem vinculado com sucesso.')
                    ->success()
                    ->send();
            });
    }

    private static function calculoDepreciacaoMensalTableAction(): Action
    {
        return TableModalAction::make('calculo_depreciacao_mensal')
            ->label('Cálculo de Depreciação Mensal')
            ->tooltip('Calcular Depreciação Mensal')
            ->icon('heroicon-o-bell')
            ->modalHeading(fn (BemMovel $record): string => "Cálculo de Depreciação Mensal #{$record->NumPatrimonio}")
            ->modalContent(fn (BemMovel $record): HtmlString => new HtmlString(
                Livewire::mount(
                    'patrimonio.depreciacao-modal',
                    ['bemMovelId' => (int) $record->getKey()],
                    "depreciacao-{$record->getKey()}",
                )
            ));
    }

    private static function gerarNovoCalculoTableAction(): Action
    {
        return Action::make('gerar_novo_calculo')
            ->label('Gerar Novo Cálculo')
            ->icon('heroicon-o-calculator')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Gerar novo cálculo de depreciação')
            ->modalDescription('O histórico de depreciação deste bem será apagado e recalculado do zero, e os valores atuais do patrimônio serão atualizados de acordo com o mês corrente.')
            ->modalSubmitActionLabel('Gerar cálculo')
            ->action(function (BemMovel $record): void {
                try {
                    $resultado = app(RecalcularDepreciacaoService::class)->recalcular($record);
                } catch (ValidationException $exception) {
                    Notification::make()
                        ->title('Não foi possível gerar o cálculo.')
                        ->body(collect($exception->errors())->flatten()->implode(' '))
                        ->danger()
                        ->send();

                    return;
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('Não foi possível gerar o cálculo.')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Cálculo de depreciação gerado com sucesso.')
                    ->body("{$resultado->parcelasGeradas} parcela(s) geradas. Depreciação mensal: R$ ".number_format($resultado->depreciacaoMensal, 2, ',', '.'))
                    ->success()
                    ->send();
            });
    }

    private static function conciliarBemTableAction(): Action
    {
        return Action::make('conciliar_bem')
            ->label('Conciliar Bem')
            ->icon('heroicon-o-bars-arrow-down');
    }

    private static function termoDigitalizadoTableAction(): Action
    {
        return Action::make('termo_digitalizado')
            ->label('Termo Digitalizado')
            ->icon('heroicon-o-clipboard-document-check');
    }

    private static function reavaliarBensTableAction(): Action
    {
        return Action::make('reavaliar_bens')
            ->label('Reavaliar Bens')
            ->icon('heroicon-o-clipboard-document-check');
    }

    private static function corrigirInformacoesTableAction(): Action
    {
        return Action::make('corrigir_informacoes')
            ->label('Corrigir Informações')
            ->icon('heroicon-o-clipboard-document-check');
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
