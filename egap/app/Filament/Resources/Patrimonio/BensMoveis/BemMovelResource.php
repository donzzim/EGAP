<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource\Pages;
use App\Models\Patrimonio\BensMoveis\BemMovel;
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
use PhpParser\Node\Stmt\Return_;

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
                                TextInput::make('DescricaoResumidadoBem')->label('ID Descrição Resumida'),
                                Textarea::make('Descricao')->label('Descrição Detalhada')->required()->rows(5)->columnSpanFull(),
                                Grid::make(3)->schema([
                                    TextInput::make('Marca')->label('ID Marca'),
                                    TextInput::make('Modelo')->label('ID Modelo'),
                                    Select::make('TipodoBem')->label('Tipo do Bem')->options(['Móveis' => 'Móveis', 'Imóveis' => 'Imóveis', 'Veículos' => 'Veículos']),
                                    Select::make('EstadodeConservacao')->label('Estado de Conservação')->options(['ÓTIMO' => 'ÓTIMO', 'BOM' => 'BOM', 'REGULAR' => 'REGULAR', 'RUIM' => 'RUIM', 'SUCATA' => 'SUCATA']),
                                    Select::make('Voltagem')->label('Voltagem')->options(['N/A' => 'Não Aplicável', '110v' => '110v', '220v' => '220v', 'BIVOLT' => 'BIVOLT']),
                                ]),
                            ]),

                        Tabs\Tab::make('3. Localização')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('UnidadeJudiciaria')->label('ID Unidade Judiciária'),
                                    TextInput::make('Setor')->label('ID Setor'),
                                    TextInput::make('ComplementoSetor')->label('Complemento'),
                                    TextInput::make('AndarSetor')->label('Andar'),
                                ]),
                            ]),

                        Tabs\Tab::make('4. Informações da Nota')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('Fornecedor')->label('ID Fornecedor'),
                                    TextInput::make('NotaFiscal')->label('N° Nota Fiscal'),
                                    DatePicker::make('DataCadastro')->label('Data do Cadastro'),
                                    TextInput::make('ValorAquisicao')->label('Valor Aquisição')->numeric()->prefix('R$'),
                                    TextInput::make('numero_processo')->label('N° do Processo'),
                                    TextInput::make('nota_empenho')->label('Nota de Empenho'),
                                ]),
                            ]),

                        Tabs\Tab::make('5. Veículos')->schema([
                            Grid::make(3)->schema([
                                TextInput::make('Placa')->label('Placa'),
                                TextInput::make('Chassi')->label('Chassi'),
                                TextInput::make('Renavam')->label('Renavam'),
                                Select::make('Combustivel')->label('Combustível')->options(['Gasolina' => 'Gasolina', 'Diesel' => 'Diesel', 'Flex' => 'Flex']),
                            ]),
                        ]),

                        Tabs\Tab::make('6. Contábil')->schema([
                            Grid::make(2)->schema([
                                TextInput::make('ContaContabil')->label('ID Conta Contábil'),
                                TextInput::make('VidaUtil')->label('Vida Útil (meses)')->numeric(),
                                TextInput::make('ValorResidual')->label('Valor Residual (R$)')->numeric()->prefix('R$'),
                                TextInput::make('DepreciacaoAcumulada')->label('Depreciação Acumulada')->numeric()->prefix('R$'),
                            ]),
                        ]),

                        Tabs\Tab::make('7. Observação')->schema([Textarea::make('Observacao')->label('Observações Gerais')->columnSpanFull()->rows(4)]),

                        Tabs\Tab::make('9. Situação')->schema([
                            Grid::make(2)->schema([
                                DatePicker::make('DataBaixa')->label('Data da Baixa')->disabled(),
                                TextInput::make('ProcessoBaixa')->label('Processo de Baixa')->disabled(),
                                DatePicker::make('DatadaReavaliacao')->label('Data da Última Reavaliação')->disabled(),
                                TextInput::make('SituacaoBem')->label('ID Situação')->disabled(),
                            ]),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll(null)
            ->columns([
                Tables\Columns\TextColumn::make('NumPatrimonio')->label('Patrimônio')->sortable()->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('Descricao')->label('Descrição')->limit(35)->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('unidadeJudiciariaRef.Setor')->label('Unidade')->alignCenter()->default('Não Informado'),
                Tables\Columns\TextColumn::make('ValorAquisicao')->label('Valor')->alignCenter()->money('BRL')->sortable(),
                Tables\Columns\TextColumn::make('situacaoBemRef.descricao')->label('Situação')->alignCenter()->default('Não Informado'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Editar'),

                    // ✅ CORRIGIR INFORMAÇÕES INDIVIDUAL
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
                                    ])
                                    ->required()
                                    ->live(),

                                Select::make('valor')
                                    ->label('Valor')
                                    ->options(function (Forms\Get $get) {
                                        $elemento = $get('elemento');
                                        if (!$elemento) return [];

                                        try {
                                            return match ($elemento) {
                                                'DescricaoResumidadoBem' => DB::connection('egap')->table('mat_descricaoresumida')->orderBy('Descricao')->pluck('Descricao', 'id'),
                                                'UnidadeJudiciaria' => DB::connection('egap')->table('mat_setores')->whereRaw('id = CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'),
                                                'Setor' => DB::connection('egap')->table('mat_setores')->whereRaw('id != CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'),
                                                'UnidadeGestora' => DB::connection('egap')->table('mat_unidadegestora')->orderBy('nome')->pluck('nome', 'id'),
                                                default => [],
                                            };
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->searchable()
                                    ->required(),
                            ]),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                $data['elemento'] => $data['valor']
                            ]);

                            Notification::make()->title('Informação do bem corrigida!')->success()->send();
                        }),

                    // ✅ HISTÓRICO
                    Action::make('historico_movimentacao')
                        ->label('Histórico')
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->modalHeading(fn ($record) => "Histórico de Movimentações - {$record->NumPatrimonio}")
                        ->modalSubmitAction(false)
                        ->modalContent(function ($record) {
                            $movimentacoes = DB::connection('egap')->table('mat_transferencia')->where('NumPatrimonio', $record->id)->orderBy('date_time', 'desc')->get();
                            return view('patrimonio.historico-movimentacoes', ['historico' => $movimentacoes]);
                        }),

                    // ✅ TERMOS DIGITALIZADOS CORRIGIDO
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
                                ->where('mat_transferencia.NumPatrimonio', $record->id)
                                ->select([
                                    'mat_termos.id as TermoID',
                                    'mat_termos.num_termo',
                                    'mat_termos.ano_termo',
                                    'mat_transferencia.date_time',
                                    'mat_arquivodigital.situacao as StatusArquivo'
                                ])
                                ->orderBy('mat_transferencia.date_time', 'desc')
                                ->get()
                                ->unique('TermoID');

                            return view('patrimonio.termos-digitalizados-modal', ['termos' => $termos]);
                        }),

                    // ✅ CONCILIAÇÃO
                    Action::make('conciliar_bem')
                        ->label('Conciliar bem')
                        ->icon('heroicon-s-check-badge')
                        ->color('warning')
                        ->form([
                            Grid::make(2)->schema([
                                TextInput::make('NumPatrimonio')->label('Patrimônio Atual')->disabled(),
                                TextInput::make('TomboSmarapd')->label('Patrimônio (Smarapd)')->required(),
                                DatePicker::make('DatadaReavaliacao')->label('Data Conciliation')->default(now())->required(),
                            ]),
                        ])
                        ->fillForm(fn ($record) => ['NumPatrimonio' => $record->NumPatrimonio, 'TomboSmarapd' => $record->TomboSmarapd, 'DatadaReavaliacao' => $record->DatadaReavaliacao])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'TomboSmarapd' => $data['TomboSmarapd'],
                                'DatadaReavaliacao' => $data['DatadaReavaliacao']
                            ]);

                            DB::connection('egap')->table('mat_conciliacao')->insert([
                                'date_time' => now(), 'numero_patrimonio' => $record->NumPatrimonio, 'descricao' => $record->Descricao,
                                'data_conciliacao' => $data['DatadaReavaliacao'], 'patrimonio' => $data['TomboSmarapd']
                            ]);
                            Notification::make()->title('Conciliado com sucesso!')->success()->send();
                        }),

                    // ✅ TRANSFERÊNCIA AUTOMATIZADA CORRIGIDA
                    Tables\Actions\Action::make('transferir_bem')
                        ->label('Transferir bens')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('warning')
                        ->form([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('unidade_atual')
                                    ->label('Unidade Destino')
                                    ->options(fn () => DB::connection('egap')->table('mat_setores')->whereRaw('id = CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'))
                                    ->searchable()->live()->required(),
                                Forms\Components\Select::make('setor_atual')
                                    ->label('Setor Destino')
                                    ->options(fn (Forms\Get $get) => $get('unidade_atual') ? DB::connection('egap')->table('mat_setores')->where('CodigoPai', $get('unidade_atual'))->orderBy('Setor')->pluck('Setor', 'id') : [])
                                    ->searchable()->required(),
                                Forms\Components\TextInput::make('pedido_no')->label('Pedido Nº'),
                                Forms\Components\TextInput::make('observacao')->label('Observação'),
                            ]),
                        ])
                        ->action(function ($record, array $data) {
                            DB::connection('egap')->transaction(function () use ($record, $data) {
                                $user = auth()->id();
                                $anoAtual = now()->year;

                                $proximoNumTermo = DB::connection('egap')->table('mat_termos')->where('ano_termo', $anoAtual)->max('num_termo') + 1 ?: 1;

                                $id_termo = DB::connection('egap')->table('mat_termos')->insertGetId([
                                    'date_time' => now(),
                                    'num_termo' => $proximoNumTermo,
                                    'ano_termo' => $anoAtual,
                                    'atualizado_em' => now(),
                                    'atualizado_por' => $user,
                                    'pedido_no' => $data['pedido_no'] ?? null,
                                ]);

                                DB::connection('egap')->table('mat_arquivodigital')->insert([
                                    'date_time' => now(),
                                    'termo' => $id_termo,
                                    'situacao' => 1,
                                    'atualizado_em' => now(),
                                    'atualizado_por' => $user,
                                    'arquivo_digital' => null
                                ]);

                                DB::connection('egap')->table('mat_transferencia')->insert([
                                    'date_time' => now(),
                                    'NumPatrimonio' => $record->id,
                                    'UnidadeAnterior' => $record->UnidadeJudiciaria,
                                    'SetorAnterior' => $record->Setor,
                                    'UnidadeAtual' => $data['unidade_atual'],
                                    'SetorAtual' => $data['setor_atual'],
                                    'Usuario' => $user,
                                    'Termo' => $id_termo,
                                    'pedido_no' => $data['pedido_no'] ?? null,
                                ]);

                                $record->update([
                                    'UnidadeJudiciaria' => $data['unidade_atual'],
                                    'Setor' => $data['setor_atual']
                                ]);
                            }); // Fechamento correto do escopo da transação database

                            Notification::make()->title('Bens transferidos e comarca atualizada automaticamente no EGAP!')->success()->send();
                        }), // Fechamento correto do escopo da Action transferir_bem

                    // ✅ BAIXA
                    Action::make('vincular_baixa')
                        ->label('Vincular para baixa')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->color('danger')
                        ->form([
                            TextInput::make('processo_baixa')->label('Processo Nº')->required(),
                            Select::make('situacao_baixa_id')->label('Motivo')
                                ->options(fn() => DB::connection('egap')->table('mat_situacao')->where('situacao', 'Baixado')->pluck('descricao', 'id'))
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

                    // ✅ REAVALIAÇÃO
                    Action::make('reavaliar_bem')
                        ->label('Reavaliação')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->form([
                            Select::make('estado_conservacao')
                                ->label('Estado de Conservação (Cálculo)')
                                ->options([
                                    10 => 'Ótimo (10)',
                                    8 => 'Bom (8)',
                                    5 => 'Regular (5)',
                                    2 => 'Ruim (2)',
                                ])
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            // Lógica de cálculo customizada
                        }),

                    // ✅ DEPRECIAÇÃO
                    Action::make('calculo_depreciacao')
                        ->label('Depreciação')
                        ->icon('heroicon-o-calculator')
                        ->color('success')
                        ->url(fn ($record) => route('depreciacao.imprimir', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    // 🔒 IMPRIMIR BLINDADO CONTRA CLIQUE FANTASMA EM NOVAS ABAS (RETORNA URL NULA SE SITUACAO != 1)
                    Action::make('imprimir_termo')
                        ->label('Imprimir termo')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->disabled(function ($record) {
                            if (!empty($record->ProcessoBaixa)) {
                                return false;
                            }

                            $ultimoTermo = DB::connection('egap')
                                ->table('mat_transferencia')
                                ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                                ->where('mat_transferencia.NumPatrimonio', $record->id)
                                ->select('mat_arquivodigital.situacao')
                                ->orderBy('mat_transferencia.date_time', 'desc')
                                ->first();

                            return (!$ultimoTermo || $ultimoTermo->situacao != 1);
                        })
                        ->tooltip(function ($record) {
                            $ultimoTermo = DB::connection('egap')
                                ->table('mat_transferencia')
                                ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                                ->where('mat_transferencia.NumPatrimonio', $record->id)
                                ->orderBy('mat_transferencia.date_time', 'desc')
                                ->first();

                            if (!$ultimoTermo) return "Nenhum termo gerado para este bem.";
                            if ($ultimoTermo->situacao != 1) return "Aguardando assinatura/validação do termo.";
                            return "Imprimir Documento Oficial";
                        })
                        ->url(function ($record) {
                            if (!empty($record->ProcessoBaixa)) {
                                return route('termo.imprimir.dinamico', ['id' => $record->id]);
                            }

                            $ultimoTermo = DB::connection('egap')
                                ->table('mat_transferencia')
                                ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                                ->where('mat_transferencia.NumPatrimonio', $record->id)
                                ->select('mat_arquivodigital.situacao')
                                ->orderBy('mat_transferencia.date_time', 'desc')
                                ->first();

                            if ($ultimoTermo && $ultimoTermo->situacao == 1) {
                                return route('termo.imprimir.dinamico', ['id' => $record->id]);
                            }

                            return null;
                        })
                        ->openUrlInNewTab(),

                    Tables\Actions\ViewAction::make()->label('Visualizar'),
                ])->label('Opções')->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // ✅ CORRIGIR INFORMAÇÕES EM LOTE
                    Tables\Actions\BulkAction::make('corrigir_informacoes_lote')
                        ->label('Corrigir informações')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->deselectRecordsAfterCompletion()
                        ->form([
                            Grid::make(2)->schema([
                                Select::make('elemento')
                                    ->label('Elemento')
                                    ->options([
                                        'DescricaoResumidadoBem' => 'Descrição Resumida',
                                        'UnidadeJudiciaria' => 'Unidade Judiciária',
                                        'Setor' => 'Setor',
                                        'UnidadeGestora' => 'Unidade Gestora',
                                    ])
                                    ->required()
                                    ->live(),

                                Select::make('valor')
                                    ->label('Valor')
                                    ->options(function (Forms\Get $get) {
                                        if (!$get('elemento')) return [];
                                        return match ($get('elemento')) {
                                            'DescricaoResumidadoBem' => DB::connection('egap')->table('mat_descricaoresumida')->orderBy('Descricao')->pluck('Descricao', 'id'),
                                            'UnidadeJudiciaria' => DB::connection('egap')->table('mat_setores')->whereRaw('id = CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'),
                                            'Setor' => DB::connection('egap')->table('mat_setores')->whereRaw('id != CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'),
                                            'UnidadeGestora' => DB::connection('egap')->table('mat_unidadegestora')->orderBy('nome')->pluck('nome', 'id'),
                                            default => [],
                                        };
                                    })
                                    ->searchable()
                                    ->required(),
                            ]),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
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

    public static function getPages(): array
    {
        return ['index' => Pages\ListBemMovels::route('/')];
    }
}
