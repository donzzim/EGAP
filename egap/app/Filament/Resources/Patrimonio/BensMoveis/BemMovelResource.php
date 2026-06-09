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
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->deferLoading()
            ->poll(null)
            ->columns([
                Tables\Columns\TextColumn::make('NumPatrimonio')->label('Patrimônio')->sortable()->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('Descricao')->label('Descrição')->limit(35)->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('unidadeJudiciariaRef.Setor')->label('Unidade')->alignCenter()->default('Não Informado'),

                Tables\Columns\TextColumn::make('ultimo_termo_status')
                    ->label('Status do Termo')
                    ->getStateUsing(function ($record) {
                        $ultimo = DB::connection('egap')
                            ->table('mat_transferencia')
                            ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                            ->join('mat_termos', 'mat_transferencia.Termo', '=', 'mat_termos.id')
                            ->where('mat_transferencia.NumPatrimonio', $record->NumPatrimonio)
                            ->select('mat_arquivodigital.situacao', 'mat_termos.num_termo')
                            ->orderBy('mat_transferencia.date_time', 'desc')
                            ->first();

                        if ($record->situacaoBemRef?->descricao === 'Transferência') {
                            return '⏳ Aguardando Assinatura/Validação';
                        }

                        if (!$ultimo || empty($ultimo->num_termo)) return 'Sem Movimentação';

                        return $ultimo->situacao == 1 ? '✅ Assinado' : '⏳ Aguardando Assinatura/Validação';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '✅ Assinado' => 'success',
                        '⏳ Aguardando Assinatura/Validação' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('ValorAquisicao')->label('Valor')->alignCenter()->money('BRL')->sortable(),
                Tables\Columns\TextColumn::make('situacaoBemRef.descricao')->label('Situação')->alignCenter()->default('Não Informado'),
            ])
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
                                        if (!$elemento) return [];
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
                                'data_conciliacao' => $data['DatadaReavaliacao'], 'patrimonio' => $data['TomboSmarapd']
                            ]);
                            Notification::make()->title('Conciliado com sucesso!')->success()->send();
                        }),

                    Action::make('transferir_bem')
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
                                    'date_time' => now(), 'termo' => $id_termo, 'situacao' => 0, 'atualizado_em' => now(), 'atualizado_por' => $user
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

                    Action::make('reavaliar_bem')
                        ->label('Reavaliação')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->form([
                            Select::make('estado_conservacao')->label('Estado de Conservação')->options([10 => 'Ótimo', 8 => 'Bom', 5 => 'Regular', 2 => 'Ruim'])->required(),
                        ])->action(function ($record, array $data) { Notification::make()->title('Reavaliado!')->success()->send(); }),

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
                            return !DB::connection('egap')
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

                            if (!$status) {
                                return "Sem movimentação de transferência registrada.";
                            }

                            if ($status->situacao != 1) {
                                return "Aguardando assinatura/validação do destinatário no sistema antigo.";
                            }

                            if (empty($status->num_termo)) {
                                return "Inconsistência: Termo assinado, mas sem número de termo vinculado.";
                            }

                            return "Imprimir Termo de Responsabilidade";
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
                                    if (!$get('elemento')) return [];
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
