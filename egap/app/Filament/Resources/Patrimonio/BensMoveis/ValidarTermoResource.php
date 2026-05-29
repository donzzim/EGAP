<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource\Pages;
use App\Models\Almoxarifado\FasePedido;
use App\Models\Patrimonio\BensMoveis\ArquivoDigital;
use App\Models\Patrimonio\BensMoveis\Termo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

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

    public static function canCreate(): bool
    {
        return false;
    }

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
                    ->getStateUsing(fn ($record) => $record->situacao_entrega === 'Validado' ? 'Abrir Documento' : 'Pendente')
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

                    // Upload do termo.
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
                                    // Padrao do TJES: termo_ID_YYYYMMDDHHMMSS.pdf
                                    return 'termo_'.$record->id.'_'.date('YmdHis').'.pdf';
                                }),
                        ])
                        ->action(function (Termo $record, array $data) {
                            $userid = auth()->id();
                            $observacao = 'Arquivo Digital anexado. <br />Aguardando validação do Setor de Patrimônio.';
                            $pathUrl = '/images/termos/'.basename($data['arquivo']);

                            $record->getConnection()->transaction(function () use ($record, $pathUrl, $observacao, $userid) {
                                $arquivoDigital = self::getArquivoDigital($record) ?? $record->arquivoDigital()->make();

                                $arquivoDigital->fill([
                                    'arquivo_digital' => $pathUrl,
                                    'atualizado_em' => now(),
                                    'atualizado_por' => $userid,
                                    'situacao' => 0, // Reseta para pendente
                                    'observacao' => $observacao,
                                    'validado_por' => null,
                                    'data_validacao' => null,
                                ])->save();

                                $record->update(['situacao_entrega' => 'Em rota']);
                            });

                            Notification::make()->title('Arquivo anexado! Aguardando validação.')->success()->send();
                        }),

                    // Invalidar / cancelar termo.
                    Action::make('invalidar_termo')
                        ->label('Invalidar/Cancelar Termo')
                        ->icon('heroicon-o-hand-thumb-down')
                        ->color('danger')
                        ->form([
                            Forms\Components\Select::make('situacao')
                                ->label('Nova Situação')
                                ->options([
                                    '2' => 'Recusado pelo Destinatário',
                                    '4' => 'Cancelado pelo Patrimônio',
                                ])->required(),
                            Forms\Components\Textarea::make('observacao')
                                ->label('Motivo / Observação')
                                ->required(),
                        ])
                        ->action(function (Termo $record, array $data) {
                            $userid = auth()->id();

                            $arquivoDigital = self::getArquivoDigital($record);

                            if (! $arquivoDigital) {
                                Notification::make()->title('Arquivo digital não encontrado para este termo.')->warning()->send();

                                return;
                            }

                            $record->getConnection()->transaction(function () use ($record, $arquivoDigital, $data, $userid) {
                                $arquivoDigital->fill([
                                    'atualizado_em' => now(),
                                    'data_validacao' => now(),
                                    'validado_por' => $userid,
                                    'situacao' => $data['situacao'],
                                    'observacao' => $data['observacao'],
                                ])->save();

                                $record->update(['situacao_entrega' => 'Cancelado']);
                            });

                            Notification::make()->title('Termo Invalidado/Cancelado!')->danger()->send();
                        }),

                    // Validar termo.
                    Action::make('validar_termo_novo')
                        ->label('Validar Termo [Novo]')
                        ->icon('heroicon-o-hand-thumb-up')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Termo $record) {
                            $userid = auth()->id();

                            $validado = self::validarTermo($record, $userid);

                            if (! $validado) {
                                Notification::make()->title('Nenhum arquivo digital ou bem associado a este termo para transferir.')->warning()->send();

                                return;
                            }

                            Notification::make()->title('Termo Validado e Patrimônios Atualizados!')->success()->send();
                        })->visible(fn ($record) => $record->situacao_entrega !== 'Validado'),

                ])->label('Opções')->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Validar em massa.
                    Tables\Actions\BulkAction::make('validar_termos_em_lote')
                        ->label('Validar Selecionados')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $validados = 0;
                            $userid = auth()->id();

                            foreach ($records as $record) {
                                if (self::validarTermo($record, $userid)) {
                                    $validados++;
                                }
                            }

                            if ($validados === 0) {
                                Notification::make()->title('Nenhum termo selecionado tinha arquivo digital e bens associados.')->warning()->send();

                                return;
                            }

                            Notification::make()
                                ->title($validados === 1 ? '1 termo validado com sucesso!' : "{$validados} termos validados com sucesso!")
                                ->success()
                                ->send();
                        }),
                ])->label('Ações em Grupo'),
            ])
            ->defaultSort('id', 'desc');
    }

    private static function getArquivoDigital(Termo $termo): ?ArquivoDigital
    {
        return $termo->arquivoDigital()
            ->latest('id')
            ->first();
    }

    private static function validarTermo(Termo $termo, int $userid): bool
    {
        $arquivoDigital = self::getArquivoDigital($termo);
        $transferencias = $termo->transferencias()->with('bem')->get();

        if (! $arquivoDigital || $transferencias->isEmpty()) {
            return false;
        }

        $termo->getConnection()->transaction(function () use ($termo, $arquivoDigital, $transferencias, $userid) {
            foreach ($transferencias as $transferencia) {
                $transferencia->bem?->update([
                    'UnidadeJudiciaria' => $transferencia->UnidadeAtual,
                    'Setor' => $transferencia->SetorAtual,
                    'ComplementoSetor' => $transferencia->ComplementoAtual,
                    'date_time' => now(),
                    'Usuario' => $userid,
                ]);
            }

            FasePedido::query()
                ->where('id_termo', $termo->id)
                ->update(['idSituacao' => 3]);

            $arquivoDigital->fill([
                'atualizado_em' => now(),
                'data_validacao' => now(),
                'observacao' => null,
                'situacao' => 1,
                'validado_por' => $userid,
            ])->save();

            $termo->update(['situacao_entrega' => 'Validado']);
        });

        return true;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListValidarTermos::route('/')];
    }
}
