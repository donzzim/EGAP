<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis;
use App\Models\Patrimonio\BensMoveis\TransferenciaBemMovel;
use Filament\Forms;
use Filament\Forms\Components\{Grid, Select};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Support\Facades\DB;

class TransferenciaBemResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TransferenciaBemMovel::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Histórico das movimentações';
    protected static ?string $modelLabel = 'Transferência';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('bem.NumPatrimonio')
                    ->label('Patrimônio')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('unidadeAnteriorRel.Setor')
                    ->label('Origem'),

                Tables\Columns\TextColumn::make('unidadeAtualRel.Setor')
                    ->label('Destino'),

                Tables\Columns\TextColumn::make('status_termo')
                    ->label('Status do Termo')
                    ->getStateUsing(function ($record) {
                        $arquivo = DB::connection('egap')
                            ->table('mat_arquivodigital')
                            ->join('mat_termos', 'mat_arquivodigital.termo', '=', 'mat_termos.id')
                            ->where('mat_arquivodigital.termo', $record->Termo)
                            ->select('mat_arquivodigital.situacao', 'mat_termos.num_termo')
                            ->first();

                        if (!$arquivo || empty($arquivo->num_termo)) return 'Sem Movimentação Real';

                        return $arquivo->situacao == 1 ? '✅ Assinado' : '⏳ Aguardando Assinatura/Validação';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '✅ Assinado' => 'success',
                        '⏳ Aguardando Assinatura/Validação' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Data')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Visualizar'),

                    Action::make('encaminhar_logistica')
                        ->label('Encaminhar para Logística')
                        ->icon('heroicon-s-bolt')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Encaminhar para Logística')
                        ->modalDescription('Deseja gerar a solicitação de transporte para a Seção de Patrimônio recolher/enviar este bem?')
                        ->action(function ($record) {
                            $userid = auth()->id();

                            try {
                                DB::connection('egap')->transaction(function () use ($record, $userid) {
                                    $dadosOrigem = DB::connection('egap')->table('mat_termos')
                                        ->join('mat_transferencia', 'mat_termos.id', '=', 'mat_transferencia.Termo')
                                        ->leftJoin('age_regiao', 'mat_transferencia.UnidadeAtual', '=', 'age_regiao.unidade')
                                        ->where('mat_termos.id', $record->Termo)
                                        ->select([
                                            'mat_termos.atualizado_por',
                                            'mat_transferencia.UnidadeAtual',
                                            'mat_transferencia.SetorAtual',
                                            'mat_transferencia.pedido_no',
                                            'age_regiao.id as regiao_id'
                                        ])
                                        ->first();

                                    if (!$dadosOrigem) {
                                        throw new \Exception("Dados do termo legados não encontrados para originar o transporte.");
                                    }

                                    $id_solicitacao = DB::connection('egap')->table('age_solicitacao')->insertGetId([
                                        'date_time' => now(),
                                        'id_user' => $userid,
                                        'tipo' => 2,
                                        'id_situacao' => 6,
                                        'id_solicitante' => $dadosOrigem->atualizado_por,
                                        'unidade_solicitante' => $dadosOrigem->UnidadeAtual,
                                        'setor_solicitante' => $dadosOrigem->SetorAtual,
                                        'regiao' => $dadosOrigem->regiao_id,
                                        'justificativa' => 'Solicitação de transporte gerada via e-GAP Laravel.',
                                        'local_saida' => 'Seção de Patrimônio'
                                    ]);

                                    DB::connection('egap')->table('age_materiais')->insert([
                                        'date_time' => now(),
                                        'id_pedido' => $dadosOrigem->pedido_no,
                                        'id_termo' => $record->Termo,
                                        'id_user' => $userid,
                                        'id_solicitacao' => $id_solicitacao
                                    ]);
                                });

                                Notification::make()->title('Solicitação encaminhada corretamente!')->success()->send();

                            } catch (\Exception $e) {
                                Notification::make()->title('Erro ao encaminhar')->body($e->getMessage())->danger()->send();
                            }
                        }),

                    Action::make('atualizar_dados')
                        ->label('Atualizar dados')
                        ->icon('heroicon-m-chevron-double-up')
                        ->color('gray')
                        ->form([
                            Grid::make(2)->schema([
                                Select::make('elemento')
                                    ->label('Elemento')
                                    ->options([
                                        'UnidadeAtual' => 'Unidade Destino',
                                        'SetorAtual' => 'Setor Destino',
                                    ])->required()->live(),

                                Select::make('valor')
                                    ->label('Valor')
                                    ->options(function (Forms\Get $get) {
                                        $elemento = $get('elemento');
                                        if (!$elemento) return [];
                                        return match ($elemento) {
                                            'UnidadeAtual' => DB::connection('egap')->table('mat_setores')->whereRaw('id = CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'),
                                            'SetorAtual' => DB::connection('egap')->table('mat_setores')->whereRaw('id != CodigoPai')->orderBy('Setor')->pluck('Setor', 'id'),
                                            default => [],
                                        };
                                    })->searchable()->required(),
                            ]),
                        ])
                        ->action(function ($record, array $data) {
                            DB::connection('egap')
                                ->table('mat_transferencia')
                                ->where('id', $record->id)
                                ->update([
                                    $data['elemento'] => $data['valor']
                                ]);

                            $record->refresh();
                            Notification::make()->title('Dados da movimentação atualizados com sucesso!')->success()->send();
                        }),

                    Action::make('imprimir_termo')
                        ->label('Imprimir termo')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->disabled(function ($record) {
                            return !DB::connection('egap')
                                ->table('mat_transferencia')
                                ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                                ->join('mat_termos', 'mat_transferencia.Termo', '=', 'mat_termos.id')
                                ->where('mat_transferencia.Termo', $record->Termo)
                                ->where('mat_arquivodigital.situacao', 1)
                                ->whereNotNull('mat_termos.num_termo')
                                ->exists();
                        })
                        ->url(function ($record) {
                            return $record->NumPatrimonio ? route('termo.imprimir.dinamico', ['id' => $record->NumPatrimonio]) : null;
                        })
                        ->openUrlInNewTab(),
                ])->label('Opções')->button(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => BensMoveis\TransferenciaBemResource\Pages\ListTransferenciaBems::route('/'),
        ];
    }
}
