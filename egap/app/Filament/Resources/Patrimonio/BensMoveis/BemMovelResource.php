<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis;

use App\Filament\Egap\Clusters\PatrimonioCluster;
use App\Filament\Egap\Resources\Patrimonio\BensMoveis\BemMovelResource\Pages;
use App\Models\Egap\Cadastro\Modelos;
use App\Models\Egap\Cadastro\Setores;
use App\Models\Egap\Patrimonio\BensMoveis\BemMovel;
use App\Models\Egap\Patrimonio\BensMoveis\SituacaoBemMovel;
use Filament\Forms;
use Filament\Forms\Components\{DatePicker, Grid, Select, Tabs, Textarea, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BemMovelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = BemMovel::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Administração dos bens móveis';
    protected static ?string $modelLabel = 'Bem Móvel';
    protected static ?string $pluralModelLabel = 'Bens Móveis';
    protected static ?string $slug = 'bem-moveis';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Administração do Bem')
                    ->tabs([
                        Tabs\Tab::make('1. Patrimônio')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('NumPatrimonio')->label('Patrimônio')->required()->unique(ignoreRecord: true),
                                    TextInput::make('TomboSmarapd')->label('Patrimônio (Conciliação)'),
                                    TextInput::make('NumTomboSmarapd')->label('Patrimônio (sem cód. barras)'),
                                    TextInput::make('NumerodeSerie')->label('Número de Série'),
                                ]),
                            ]),

                        Tabs\Tab::make('2. Descrição do Bem')
                            ->schema([
                                Select::make('DescricaoResumidadoBem')
                                    ->label('Descrição Resumida')
                                    ->relationship('descricaoResumidaBemRef', 'Descricao')
                                    ->searchable()
                                    ->preload(),

                                Textarea::make('Descricao')->label('Descrição Detalhada')->required()->rows(5)->columnSpanFull(),

                                Grid::make(2)->schema([
                                    Select::make('Marca')
                                        ->label('Marca')
                                        ->relationship('marcaRef', 'descricao')
                                        ->searchable()->live()
                                        ->afterStateUpdated(fn (Forms\Set $set) => $set('Modelo', null)),

                                    Select::make('Modelo')
                                        ->label('Modelo')
                                        ->options(function (Forms\Get $get) {
                                            $marcaId = $get('Marca');
                                            if (!$marcaId) return [];
                                            return Modelos::where('marca', $marcaId)->pluck('descricao', 'id');
                                        })->searchable(),

                                    Select::make('TipodoBem')->label('Tipo do Bem')->options(['Móveis' => 'Móveis', 'Imóveis' => 'Imóveis', 'Veículos' => 'Veículos']),
                                    Select::make('EstadodeConservacao')->label('Estado de Conservação')->options(['ÓTIMO' => 'ÓTIMO', 'BOM' => 'BOM', 'REGULAR' => 'REGULAR', 'RUIM' => 'RUIM', 'SUCATA' => 'SUCATA']),
                                    Select::make('Voltagem')->label('Voltagem')->options(['N/A' => 'Não Aplicável', '110v' => '110v', '220v' => '220v', 'BIVOLT' => 'BIVOLT']),
                                ]),
                            ]),

                        Tabs\Tab::make('3. Localização')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('UnidadeJudiciaria')
                                        ->label('Unidade Judiciária')
                                        ->relationship('unidadeJudiciariaRef', 'Setor')
                                        ->searchable()->live()
                                        ->afterStateUpdated(fn (Forms\Set $set) => $set('Setor', null)),

                                    Select::make('Setor')
                                        ->label('Setor')
                                        ->options(function (Forms\Get $get) {
                                            $unidadeId = $get('UnidadeJudiciaria');
                                            if (!$unidadeId) return [];
                                            return Setores::where('CodigoPai', $unidadeId)->pluck('Setor', 'id');
                                        })->searchable(),
                                    TextInput::make('ComplementoSetor')->label('Complemento'),
                                    TextInput::make('AndarSetor')->label('Andar'),
                                ]),
                            ]),

                        Tabs\Tab::make('4. Informações da Nota')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('Fornecedor')->label('Fornecedor')->relationship('fornecedorRef', 'NomeFornecedor')->searchable(),
                                    TextInput::make('NotaFiscal')->label('N° Nota Fiscal'),
                                    DatePicker::make('DataCadastro')->label('Data do Cadastro'),
                                    TextInput::make('ValorAquisicao')->label('Valor Aquisição')->numeric()->prefix('R$'),
                                    TextInput::make('numero_processo')->label('N° do Processo'),
                                    TextInput::make('nota_empenho')->label('Nota de Empenho'),
                                ]),
                            ]),

                        Tabs\Tab::make('5. Veículos')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('Placa')->label('Placa'),
                                    TextInput::make('Chassi')->label('Chassi'),
                                    TextInput::make('Renavam')->label('Renavam'),
                                    Select::make('Combustivel')->label('Combustível')->options(['Gasolina' => 'Gasolina', 'Diesel' => 'Diesel', 'Flex' => 'Flex']),
                                    TextInput::make('AnoFabricacao')->label('Ano Fabricação'),
                                    TextInput::make('AnoModelo')->label('Ano Modelo'),
                                ]),
                            ]),

                        Tabs\Tab::make('6. Contábil')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('ContaContabil')->label('Conta Contábil')->relationship('contaContabilRef', 'titulo')->searchable(),
                                    TextInput::make('VidaUtil')->label('Vida Útil (meses)')->numeric(),
                                    TextInput::make('ValorResidual')->label('Valor Residual (R$)')->numeric()->prefix('R$'),
                                    TextInput::make('DepreciacaoAcumulada')->label('Depreciação Acumulada')->numeric()->prefix('R$'),
                                ]),
                            ]),

                        Tabs\Tab::make('7. Observação')
                            ->schema([
                                Textarea::make('Observacao')->label('Observações Gerais')->columnSpanFull()->rows(4),
                            ]),

                        Tabs\Tab::make('8. Reavaliação')
                            ->schema([
                                Grid::make(2)->schema([
                                    DatePicker::make('DatadaReavaliacao')->label('Data da Reavaliação'),
                                    TextInput::make('ValordaReavaliacao')->label('Valor da Reavaliação')->numeric()->prefix('R$'),
                                    TextInput::make('VidaUtilReavaliacao')->label('Vida Útil Reavaliação (meses)'),
                                    TextInput::make('ValordeMercado')->label('Valor de Mercado')->numeric()->prefix('R$'),
                                ]),
                            ]),

                        Tabs\Tab::make('9. Situação')
                            ->schema([
                                Grid::make(2)->schema([
                                    DatePicker::make('DataBaixa')->label('Data da Baixa')->disabled(),
                                    TextInput::make('ProcessoBaixa')->label('Processo de Baixa')->disabled(),
                                    Select::make('SituacaoBem')
                                        ->label('Situação')
                                        ->relationship('situacaoBemRef', 'descricao')
                                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->descricao_completa)
                                        ->disabled(),
                                ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('NumPatrimonio')->label('Patrimônio')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('Descricao')->label('Descrição')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('unidadeJudiciariaRef.Setor')->label('Unidade')->alignCenter(),
                Tables\Columns\TextColumn::make('ValorAquisicao')->label('Valor')->alignCenter()->money('BRL')->sortable(),
                Tables\Columns\TextColumn::make('situacaoBemRef.descricao_completa')->label('Situação')->alignCenter(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Editar'),

                    // ✅ CONCILIAÇÃO (ATUALIZADA)
                    Action::make('conciliar_bem')
                        ->label('Conciliar bem')
                        ->icon('heroicon-s-check-badge')
                        ->color('warning')
                        ->form([
                            Grid::make(2)->schema([
                                TextInput::make('NumPatrimonio')->label('Patrimônio Atual')->disabled(),
                                TextInput::make('TomboSmarapd')->label('Patrimônio (Conciliação Smarapd)'),
                                TextInput::make('NumTomboSmarapd')->label('Patrimônio (sem cód. barras)'),
                                DatePicker::make('DatadaReavaliacao')->label('Data da Conciliação')->default(now()),
                            ]),
                        ])
                        ->fillForm(fn (BemMovel $record): array => [
                            'NumPatrimonio' => $record->NumPatrimonio,
                            'TomboSmarapd' => $record->TomboSmarapd,
                            'NumTomboSmarapd' => $record->NumTomboSmarapd,
                            'DatadaReavaliacao' => $record->DatadaReavaliacao,
                        ])
                        ->action(function (BemMovel $record, array $data) {
                            DB::connection('egap')->transaction(function () use ($record, $data) {
                                $record->update([
                                    'TomboSmarapd' => $data['TomboSmarapd'],
                                    'NumTomboSmarapd' => $data['NumTomboSmarapd'],
                                    'DatadaReavaliacao' => $data['DatadaReavaliacao'],
                                    'Observacao' => $record->Observacao . "\n[CONCILIAÇÃO REALIZADA EM " . now()->format('d/m/Y') . "]",
                                ]);

                                DB::connection('egap')->table('mat_conciliacao')->insert([
                                    'date_time' => now(),
                                    'NumPatrimonio' => $record->NumPatrimonio,
                                    'Usuario' => auth()->id(),
                                    'DataConciliacao' => $data['DatadaReavaliacao'],
                                    'TomboAnterior' => $record->getOriginal('TomboSmarapd'),
                                    'TomboNovo' => $data['TomboSmarapd'],
                                ]);
                            });
                            Notification::make()->title('Bem conciliado com sucesso!')->success()->send();
                        }),

                    // ✅ TRANSFERÊNCIA
                    Action::make('transferir_bem')
                        ->label('Transferir bens')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('warning')
                        ->form([
                            Grid::make(2)->schema([
                                Select::make('unidade_atual')->label('Unidade')->relationship('unidadeJudiciariaRef', 'Setor')->searchable()->live()->required(),
                                Select::make('setor_atual')->label('Setor')->options(fn (Forms\Get $get) => $get('unidade_atual') ? Setores::where('CodigoPai', $get('unidade_atual'))->pluck('Setor', 'id') : [])->searchable()->required(),
                                Select::make('pedido_no')
                                    ->label('Pedido No')
                                    ->options(fn () => DB::connection('egap')->table('age_solicitacao')->select('id', 'date_time')->limit(50)->get()->mapWithKeys(fn ($i) => [$i->id => "{$i->id}/".Carbon::parse($i->date_time)->year]))
                                    ->searchable(),
                            ]),
                        ])
                        ->action(function (BemMovel $record, array $data) {
                            DB::connection('egap')->transaction(function () use ($record, $data) {
                                DB::connection('egap')->table('mat_transferencia')->insert([
                                    'date_time' => now(), 'NumPatrimonio' => $record->NumPatrimonio, 'Usuario' => auth()->id(),
                                    'UnidadeAnterior' => $record->UnidadeJudiciaria, 'SetorAnterior' => $record->Setor,
                                    'UnidadeAtual' => $data['unidade_atual'], 'SetorAtual' => $data['setor_atual'], 'pedido_no' => $data['pedido_no'] ?? null,
                                ]);
                                $record->update(['UnidadeJudiciaria' => $data['unidade_atual'], 'Setor' => $data['setor_atual']]);
                            });
                            Notification::make()->title('Bem transferido!')->success()->send();
                        }),

                    // ✅ BAIXA
                    Action::make('vincular_baixa')
                        ->label('Vincular bens para baixa')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->color('danger')
                        ->form([
                            Grid::make(2)->schema([
                                TextInput::make('processo_baixa')->label('Processo Nº')->required()->mask('9999.99.999.999'),
                                Select::make('situacao_baixa_id')
                                    ->label('Tipo da baixa')
                                    ->options(fn() => SituacaoBemMovel::where('situacao', 'Baixado')->get()->pluck('descricao_completa', 'id'))
                                    ->required(),
                                DatePicker::make('data_baixa')->label('Data da Baixa')->default(now())->required(),
                                TextInput::make('requisitante')->label('Requisitante'),
                                Textarea::make('detalhes')->label('Detalhes')->rows(3)->columnSpanFull(),
                            ]),
                        ])
                        ->action(function (BemMovel $record, array $data) {
                            DB::connection('egap')->transaction(function () use ($record, $data) {
                                DB::connection('egap')->table('mat_baixa')->insert([
                                    'date_time' => now(), 'Usuario' => auth()->id(), 'NumeroProcesso' => $data['processo_baixa'],
                                    'DataBaixa' => $data['data_baixa'], 'Requisitante' => $data['requisitante'] ?? null, 'Observacao' => $data['detalhes'] ?? null,
                                ]);
                                $record->update(['ProcessoBaixa' => $data['processo_baixa'], 'DataBaixa' => $data['data_baixa'], 'SituacaoBem' => $data['situacao_baixa_id']]);
                            });
                            Notification::make()->title('Bem baixado com sucesso!')->success()->send();
                        })
                        ->visible(fn ($record) => in_array($record->SituacaoBem, [1, 7, 8, 9, 12])),

                    // ✅ HISTÓRICO
                    Action::make('historico_movimentacoes')
                        ->label('Histórico')
                        ->icon('heroicon-o-clock')
                        ->modalContent(fn ($record) => view('patrimonio.historico-movimentacoes', [
                            'historico' => DB::connection('egap')->table('mat_transferencia')->where('NumPatrimonio', $record->NumPatrimonio)->orderBy('date_time', 'desc')->get()
                        ])),

                    Action::make('calculo_depreciacao')
                        ->label('Cálculo de Depreciação Mensal')
                        ->icon('heroicon-o-calculator')
                        ->color('success')
                        ->url(fn (BemMovel $record) => route('depreciacao.imprimir', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    Action::make('imprimir_termo')
                        ->label('Imprimir termo')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn ($record) => route('termo.imprimir.dinamico', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    Tables\Actions\ViewAction::make()->label('Visualizar'),
                ])->label('Opções')->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('imprimir_selecionados')
                        ->label('Relatório de Bens (Selecionados)')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $ids = $records->pluck('id')->implode(',');
                            return redirect()->route('bens.imprimir.lote', ['ids' => $ids]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ])->label('Ações em Grupo'),
            ])
            ->striped()
            ->paginated([50, 100, 150]);
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