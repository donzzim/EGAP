<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Almoxarifado\FasePedido;
use App\Models\Patrimonio\BensMoveis\ArquivoDigital;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\Termo;
use App\Models\Patrimonio\BensMoveis\TransferenciaBemMovel;
use Filament\Forms;
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
use Illuminate\Support\Collection;

class ValidarTermoResource extends Resource
{
    protected static ?string $model = ArquivoDigital::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $slug = 'bens-moveis/validar-termos';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Validar Termos';

    protected static ?string $modelLabel = 'Validação de Termo';

    protected static ?string $pluralModelLabel = 'Validação de Termos';

    protected static ?int $navigationSort = 6;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Section::make('Documento do Termo')
                    ->description('Vincule o termo e mantenha o arquivo digital usado na validação.')
                    ->icon('heroicon-o-document-check')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('termo')
                            ->label('Termo de Responsabilidade')
                            ->relationship('termoRel', 'num_termo')
                            ->getOptionLabelFromRecordUsing(
                                fn (Termo $record): string => "{$record->num_termo}/{$record->ano_termo} - ID {$record->id}"
                            )
                            ->searchable(['id', 'num_termo', 'ano_termo'])
                            ->optionsLimit(50)
                            ->placeholder('Pesquise pelo número, ano ou ID do termo')
                            ->native(false)
                            ->required()
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->dehydrated()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('arquivo_digital')
                            ->label('Arquivo Digital')
                            ->helperText(fn (string $operation): string => $operation === 'edit'
                                ? 'Use a ação "Upload do Termo" para substituir o PDF e retornar a situação para Pendente.'
                                : 'Envie somente arquivos PDF de até 10 MB.')
                            ->disk('public')
                            ->directory('images/termos')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('observacao')
                            ->label('Observação')
                            ->placeholder('Registre informações relevantes sobre o envio ou a validação do documento.')
                            ->rows(5)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Validação')
                    ->description('Situação atual do arquivo digital.')
                    ->icon('heroicon-o-shield-check')
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\Select::make('situacao')
                            ->label('Situação')
                            ->options(ArquivoDigital::situacaoOptions())
                            ->default(ArquivoDigital::SITUACAO_PENDENTE)
                            ->native(false)
                            ->required()
                            ->dehydrated(),

                        Forms\Components\Toggle::make('web')
                            ->label('Disponível na WEB')
                            ->default(false)
                            ->inline(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'termoRel',
                'atualizadoPor',
                'validadoPor',
            ]))
            ->columns([
                TableColumns::text('id', '#', isFirstColumn: true),

                TableColumns::text('termoRel.num_termo', 'Termo')
                    ->formatStateUsing(fn ($state, ArquivoDigital $record): string => $record->termoRel
                        ? "{$record->termoRel->num_termo}/{$record->termoRel->ano_termo}"
                        : '-')
                    ->badge()
                    ->color('primary')
                    ->url(fn (ArquivoDigital $record): ?string => $record->termo
                        ? route('termo.imprimir', ['id' => $record->termo])
                        : null)
                    ->openUrlInNewTab(),

                TableColumns::text('arquivo_digital', 'Arquivo Digital')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? basename($state) : '-')
                    ->color('primary')
                    ->limit(50)
                    ->weight('medium')
                    ->url(function ($record) {
                        return config('app.egap').$record->arquivo_digital;
                    })
                    ->openUrlInNewTab(),

                TableColumns::text('atualizadoPor.name', 'Atualizado por')
                    ->description(fn ($record): string => $record->atualizado_em?->format('d/m/Y H:i') ?? '-'),

                TableColumns::text('observacao', 'Observação')
                    ->limit(80)
                    ->tooltip(fn ($record): ?string => $record->observacao)
                    ->wrap(),

                TableColumns::text('situacao', 'Situação')
                    ->formatStateUsing(fn ($state): string => ArquivoDigital::situacaoLabel($state))
                    ->badge()
                    ->color(fn ($state): string => ArquivoDigital::situacaoColor($state)),

                TableColumns::text('web', 'WEB')
                    ->formatStateUsing(fn ($state): string => match ($state === null ? null : (int) $state) {
                        1 => 'Sim',
                        0 => 'Não',
                        default => '-',
                    })
                    ->badge()
                    ->color(fn ($state): string => (int) $state === 1 ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\Filter::make('termo_filter')
                    ->form([
                        TextInput::make('termo')
                            ->label('Termo')
                            ->placeholder('Informe número do termo'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => self::aplicarFiltroTermo(
                        $query,
                        $data['termo'] ?? null,
                    )),

                Tables\Filters\SelectFilter::make('situacao')
                    ->label('Situação')
                    ->options(ArquivoDigital::situacaoOptions())
                    ->native(false),

                Tables\Filters\SelectFilter::make('web')
                    ->label('WEB')
                    ->options([
                        1 => 'Sim',
                        0 => 'Não',
                    ])
                    ->native(false),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                ...TableDefaults::actions(),
                ActionGroup::make([
                    self::uploadTermoTableAction(),
                    self::validarTermoTableAction(),
                    self::invalidarTermoTableAction(),
                ])
                    ->hiddenLabel()
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('validar_termos_em_lote')
                    ->label('Validar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $validados = 0;
                        $userid = (int) auth()->id();

                        foreach ($records as $record) {
                            if ($record instanceof ArquivoDigital && self::validarArquivoDigital($record, $userid)) {
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
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('atualizado_em', 'desc');
    }

    private static function aplicarFiltroTermo(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        [$numero, $ano] = array_pad(preg_split('/\s*\/\s*/', $search, 2), 2, null);

        return $query->whereHas('termoRel', function (Builder $query) use ($search, $numero, $ano): void {
            if (filled($ano)) {
                $query
                    ->where('num_termo', 'like', '%'.$numero.'%')
                    ->where('ano_termo', 'like', '%'.$ano.'%');

                return;
            }

            $query->where(function (Builder $query) use ($search): void {
                $query->where('num_termo', 'like', '%'.$search.'%');

                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }
            });
        });
    }

    private static function uploadTermoTableAction(): Action
    {
        return Action::make('upload_termo')
            ->label('Upload do Termo')
            ->icon('heroicon-o-document-arrow-up')
            ->color('gray')
            ->form([
                Forms\Components\FileUpload::make('arquivo')
                    ->label('Selecione o Termo em PDF')
                    ->required()
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('images/termos')
                    ->maxSize(10240)
                    ->getUploadedFileNameForStorageUsing(
                        fn (ArquivoDigital $record): string => 'termo_'.$record->termo.'_'.date('YmdHis').'.pdf'
                    ),
            ])
            ->action(function (ArquivoDigital $record, array $data): void {
                $observacao = 'Arquivo Digital anexado. <br />Aguardando validação do Setor de Patrimônio.';

                $record->getConnection()->transaction(function () use ($record, $data, $observacao): void {
                    $record->fill([
                        'arquivo_digital' => $data['arquivo'],
                        'situacao' => ArquivoDigital::SITUACAO_PENDENTE,
                        'observacao' => $observacao,
                        'validado_por' => null,
                        'data_validacao' => null,
                    ])->save();
                });

                Notification::make()->title('Arquivo anexado! Aguardando validação.')->success()->send();
            });
    }

    private static function invalidarTermoTableAction(): Action
    {
        return Action::make('invalidar_termo')
            ->label('Invalidar/Cancelar Termo')
            ->icon('heroicon-o-hand-thumb-down')
            ->color('gray')
            ->form([
                Forms\Components\Select::make('situacao')
                    ->label('Situação')
                    ->options([
                        ArquivoDigital::SITUACAO_INVALIDADO => 'Invalidado',
                        ArquivoDigital::SITUACAO_CANCELADO => 'Cancelado',
                    ])
                    ->native(false)
                    ->required(),
                Forms\Components\Textarea::make('observacao')
                    ->label('Observação')
                    ->required(),
            ])
            ->action(function (ArquivoDigital $record, array $data): void {
                $record->getConnection()->transaction(function () use ($record, $data): void {
                    $record->fill([
                        'data_validacao' => now(),
                        'validado_por' => auth()->id(),
                        'situacao' => (int) $data['situacao'],
                        'observacao' => $data['observacao'],
                    ])->save();
                });

                Notification::make()
                    ->title(ArquivoDigital::situacaoLabel($data['situacao']).' com sucesso.')
                    ->danger()
                    ->send();
            })
            ->visible(fn (ArquivoDigital $record): bool => $record->situacao !== ArquivoDigital::SITUACAO_CANCELADO);
    }

    private static function validarTermoTableAction(): Action
    {
        return Action::make('validar_termo_novo')
            ->label('Validar Termo')
            ->icon('heroicon-o-hand-thumb-up')
            ->color('gray')
            ->requiresConfirmation()
            ->action(function (ArquivoDigital $record): void {
                $validado = self::validarArquivoDigital($record, (int) auth()->id());

                if (! $validado) {
                    Notification::make()
                        ->title('Não foi possível validar o termo.')
                        ->body('Verifique se existe um termo relacionado e transferencias associadas.')
                        ->warning()
                        ->send();

                    return;
                }

                Notification::make()->title('Termo Validado e Patrimônios Atualizados!')->success()->send();
            })
            ->visible(fn (ArquivoDigital $record): bool => $record->situacao === ArquivoDigital::SITUACAO_PENDENTE);
    }

    private static function validarArquivoDigital(ArquivoDigital $arquivoDigital, int $userid): bool
    {
        if ($arquivoDigital->situacao !== ArquivoDigital::SITUACAO_PENDENTE || blank($arquivoDigital->termo)) {
            return false;
        }

        $transferencias = TransferenciaBemMovel::query()
            ->where('Termo', $arquivoDigital->termo)
            ->get([
                'Termo',
                'NumPatrimonio',
                'UnidadeAtual',
                'SetorAtual',
                'ComplementoAtual',
            ]);

        if ($transferencias->isEmpty()) {
            return false;
        }

        $arquivoDigital->getConnection()->transaction(function () use ($arquivoDigital, $transferencias, $userid) {
            foreach ($transferencias as $transferencia) {
                BemMovel::query()
                    ->whereKey($transferencia->NumPatrimonio)
                    ->update([
                        'UnidadeJudiciaria' => $transferencia->UnidadeAtual,
                        'Setor' => $transferencia->SetorAtual,
                        'ComplementoSetor' => $transferencia->ComplementoAtual,
                    ]);

                FasePedido::query()
                    ->where('id_termo', $transferencia->Termo)
                    ->update(['idSituacao' => 3]);
            }

            $arquivoDigital->fill([
                'atualizado_em' => now(),
                'data_validacao' => now(),
                'observacao' => null,
                'situacao' => ArquivoDigital::SITUACAO_VALIDADO,
                'validado_por' => $userid,
            ])->save();
        });

        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListValidarTermos::route('/'),
            'create' => Pages\CreateValidarTermo::route('/create'),
            'edit' => Pages\EditValidarTermo::route('/{record}/edit'),
        ];
    }
}
