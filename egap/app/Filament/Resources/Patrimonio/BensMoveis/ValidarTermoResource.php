<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource\Pages;
use App\Models\Patrimonio\BensMoveis\Termo;
use App\Filament\Clusters\PatrimonioCluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\{TextInput, Textarea, Grid, Section, Placeholder};
use Illuminate\Support\Facades\Auth;

class ValidarTermoResource extends Resource
{
    protected static ?string $model = Termo::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $cluster = PatrimonioCluster::class;
    protected static ?string $slug = 'validar-termos';
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Validar Termos';
    protected static ?int $navigationSort = 4;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Visualizar Detalhes do Termo')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('id')->label('ID do Registro')->readOnly(),
                            Forms\Components\TextInput::make('num_termo')->label('Número do Termo')->readOnly(),
                            Forms\Components\TextInput::make('ano_termo')->label('Ano do Termo')->readOnly(),
                            Forms\Components\TextInput::make('situacao_entrega')->label('Situação Atual')->readOnly(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('termo_completo')->label('Termo')->searchable(['num_termo', 'ano_termo'])->weight('bold'),

                Tables\Columns\TextColumn::make('status_virtual')
                    ->label('Link do Arquivo')
                    ->getStateUsing(fn ($record) => $record->situacao_entrega === 'Validado' ? "Abrir Documento" : "Pendente")
                    ->color(fn ($state) => $state === 'Abrir Documento' ? 'primary' : 'gray')
                    ->weight('bold')
                    ->url(fn ($record) => $record->situacao_entrega === 'Validado' ? route('termo.imprimir.dinamico', ['id' => $record->id]) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('situacao_entrega')
                    ->label('Situação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Validado' => 'success',
                        'Em rota' => 'info',
                        'Cancelado' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Visualizar'),

                    // 1️⃣ UPLOAD DO TERMO (Fiel ao JFile::move e salvando em /images/termos/)
                    Action::make('upload_termo')
                        ->label('Upload do Termo')
                        ->icon('heroicon-o-document-arrow-up')
                        ->form([
                            Forms\Components\FileUpload::make('arquivo')
                                ->label('Selecione o Termo em PDF')
                                ->required()
                                ->acceptedFileTypes(['application/pdf'])
                                ->disk('public') // Certifique-se de apontar pro diretório correto em config/filesystems.php
                                ->directory('images/termos')
                                ->getUploadedFileNameForStorageUsing(function ($record) {
                                    // Padrão do TJES: termo_ID_YYYYMMDDHHMMSS.pdf
                                    return 'termo_' . $record->id . '_' . date('YmdHis') . '.pdf';
                                }),
                        ])
                        ->action(function (Termo $record, array $data) {
                            $userid = auth()->id();
                            $observacao = 'Arquivo Digital anexado. <br />Aguardando validação do Setor de Patrimônio.';
                            $pathUrl = '/images/termos/' . basename($data['arquivo']);

                            DB::connection('egap')->table('mat_arquivodigital')
                                ->where('id', $record->id)
                                ->update([
                                    'arquivo_digital' => $pathUrl,
                                    'atualizado_em' => now(),
                                    'atualizado_por' => $userid,
                                    'situacao' => 0, // Reseta para pendente
                                    'observacao' => $observacao,
                                    'validado_por' => null,
                                    'data_validacao' => null
                                ]);

                            $record->update(['situacao_entrega' => 'Em rota']);

                            Notification::make()->title('Arquivo anexado! Aguardando validação.')->success()->send();
                        }),

                    // 2️⃣ INVALIDAR / CANCELAR TERMO
                    Action::make('invalidar_termo')
                        ->label('Invalidar/Cancelar Termo')
                        ->icon('heroicon-o-hand-thumb-down')
                        ->color('danger')
                        ->form([
                            Forms\Components\Select::make('situacao')
                                ->label('Nova Situação')
                                ->options([
                                    '2' => 'Recusado pelo Destinatário',
                                    '4' => 'Cancelado pelo Patrimônio'
                                ])->required(),
                            Forms\Components\Textarea::make('observacao')
                                ->label('Motivo / Observação')
                                ->required(),
                        ])
                        ->action(function (Termo $record, array $data) {
                            $userid = auth()->id();

                            DB::connection('egap')->table('mat_arquivodigital')
                                ->where('id', $record->id)
                                ->update([
                                    'atualizado_em' => now(),
                                    'data_validacao' => now(),
                                    'validado_por' => $userid,
                                    'situacao' => $data['situacao'],
                                    'observacao' => $data['observacao']
                                ]);

                            $record->update(['situacao_entrega' => 'Cancelado']);

                            Notification::make()->title('Termo Invalidado/Cancelado!')->danger()->send();
                        }),

                    // 3️⃣ VALIDAR TERMO (O motor original com a query correta)
                    Action::make('validar_termo_novo')
                        ->label('Validar Termo [Novo]')
                        ->icon('heroicon-o-hand-thumb-up')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Termo $record) {
                            $userid = auth()->id();

                            // 1. Captura os dados de amarração da mat_transferencia igual ao script
                            $rows = DB::connection('egap')
                                ->table('mat_arquivodigital as arq')
                                ->join('mat_transferencia as tra', 'arq.termo', '=', 'tra.Termo')
                                ->where('arq.id', $record->id)
                                ->select(['tra.Termo', 'tra.NumPatrimonio', 'tra.UnidadeAtual', 'tra.SetorAtual', 'tra.ComplementoAtual'])
                                ->get();

                            if ($rows->isEmpty()) {
                                Notification::make()->title('Nenhum bem associado a este termo para transferir.')->wrapper()->send();
                                return;
                            }

                            DB::connection('egap')->transaction(function () use ($record, $rows, $userid) {
                                foreach ($rows as $row) {
                                    // Passo A: Dá o UPDATE na tabela mat_patrimonio efetivamente
                                    DB::connection('egap')
                                        ->table('mat_patrimonio')
                                        ->where('id', $row->NumPatrimonio)
                                        ->update([
                                            'UnidadeJudiciaria' => $row->UnidadeAtual,
                                            'Setor' => $row->SetorAtual,
                                            'ComplementoSetor' => $row->ComplementoAtual,
                                            'date_time' => now(),
                                            'Usuario' => $userid
                                        ]);

                                    // Passo B: Atualiza o andamento do processo na ped_fases (idSituacao = 3)
                                    DB::connection('egap')
                                        ->table('ped_fases')
                                        ->where('id_termo', $row->Termo)
                                        ->update(['idSituacao' => 3]);
                                }

                                // Passo C: Atualiza a mat_arquivodigital carimbando o sucesso
                                DB::connection('egap')
                                    ->table('mat_arquivodigital')
                                    ->where('id', $record->id)
                                    ->update([
                                        'atualizado_em' => now(),
                                        'data_validacao' => now(),
                                        'observacao' => null,
                                        'situacao' => 1,
                                        'validado_por' => $userid
                                    ]);

                                // Sincroniza o status do model local do Filament
                                $record->update(['situacao_entrega' => 'Validado']);
                            });

                            Notification::make()->title('Termo Validado e Patrimônios Atualizados!')->success()->send();
                        })->visible(fn ($record) => $record->situacao_entrega !== 'Validado'),

                ])->label('Opções')->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // 🌟 VALIDAR EM MASSA (Adaptado para ler o array de IDs selecionados na Grid)
                    Tables\Actions\BulkAction::make('validar_termos_em_lote')
                        ->label('Validar Selecionados')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $ids = $records->pluck('id')->toArray();
                            $userid = auth()->id();

                            $rows = DB::connection('egap')
                                ->table('mat_arquivodigital as arq')
                                ->join('mat_transferencia as tra', 'arq.termo', '=', 'tra.Termo')
                                ->whereIn('arq.id', $ids)
                                ->select(['tra.Termo', 'tra.NumPatrimonio', 'tra.UnidadeAtual', 'tra.SetorAtual', 'tra.ComplementoAtual'])
                                ->get();

                            if ($rows->isEmpty()) {
                                Notification::make()->title('Nenhum registro divergente encontrado.')->warning()->send();
                                return;
                            }

                            DB::connection('egap')->transaction(function () use ($records, $rows, $ids, $userid) {
                                foreach ($rows as $row) {
                                    DB::connection('egap')
                                        ->table('mat_patrimonio')
                                        ->where('id', $row->NumPatrimonio)
                                        ->update([
                                            'UnidadeJudiciaria' => $row->UnidadeAtual,
                                            'Setor' => $row->SetorAtual,
                                            'ComplementoSetor' => $row->ComplementoAtual,
                                            'date_time' => now(),
                                            'Usuario' => $userid
                                        ]);

                                    DB::connection('egap')
                                        ->table('ped_fases')
                                        ->where('id_termo', $row->Termo)
                                        ->update(['idSituacao' => 3]);
                                }

                                DB::connection('egap')
                                    ->table('mat_arquivodigital')
                                    ->whereIn('id', $ids)
                                    ->update([
                                        'atualizado_em' => now(),
                                        'data_validacao' => now(),
                                        'observacao' => null,
                                        'situacao' => 1,
                                        'validado_por' => $userid
                                    ]);

                                foreach ($records as $record) {
                                    $record->update(['situacao_entrega' => 'Validado']);
                                }
                            });

                            Notification::make()->title('Termos validados em lote com sucesso!')->success()->send();
                        }),
                ])->label('Ações em Grupo'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array { return ['index' => Pages\ListValidarTermos::route('/')]; }
}
