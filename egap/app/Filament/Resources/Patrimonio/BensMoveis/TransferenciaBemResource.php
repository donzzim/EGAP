<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\TransferenciaBemResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Almoxarifado\Pedidos;
use App\Models\Cadastro\Setores;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\TransferenciaBemMovel;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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

    protected static ?string $pluralModelLabel = 'Histórico das Movimentações';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'bens-moveis/movimentacoes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identificação da movimentação')
                    ->description('Selecione o bem e, quando houver, vincule o termo e o pedido.')
                    ->icon('heroicon-o-arrows-right-left')
                    ->columns(3)
                    ->schema([
                        Select::make('NumPatrimonio')
                            ->label('Nº Patrimônio')
                            ->relationship('bem', 'NumPatrimonio')
                            ->getOptionLabelFromRecordUsing(fn (BemMovel $record): string => filled($record->Descricao)
                                ? "{$record->NumPatrimonio} - {$record->Descricao}"
                                : (string) $record->NumPatrimonio)
                            ->searchable(['NumPatrimonio', 'Descricao'])
                            ->optionsLimit(50)
                            ->native(false)
                            ->placeholder('Selecione o patrimônio')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set): void {
                                $bem = filled($state)
                                    ? BemMovel::query()->where('NumPatrimonio', $state)->first()
                                    : null;

                                $set('UnidadeAnterior', $bem?->UnidadeJudiciaria);
                                $set('SetorAnterior', $bem?->Setor);
                                $set('ComplementoAnterior', $bem?->ComplementoSetor);
                                $set('AndarAnterior', $bem?->AndarSetor);
                            }),

                        Select::make('Termo')
                            ->label('Termo')
                            ->relationship('termoRel', 'num_termo')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => $record->termo_completo)
                            ->searchable(['num_termo', 'ano_termo'])
                            ->optionsLimit(50)
                            ->native(false)
                            ->placeholder('Selecione o termo'),

                        Select::make('pedido_no')
                            ->label('Pedido Nº')
                            ->relationship('pedido', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Pedidos $record): string => filled($record->num_protocolo)
                                ? "{$record->id} - Protocolo {$record->num_protocolo}"
                                : (string) $record->id)
                            ->searchable(['id', 'num_protocolo'])
                            ->optionsLimit(50)
                            ->native(false)
                            ->placeholder('Selecione o pedido'),
                    ]),

                Section::make('Localização anterior')
                    ->description('A origem é preenchida conforme a localização atual do patrimônio selecionado e pode ser corrigida quando necessário.')
                    ->icon('heroicon-o-map-pin')
                    ->columns(2)
                    ->schema([
                        Select::make('UnidadeAnterior')
                            ->label('Unidade Judiciária Anterior')
                            ->options(fn (): array => self::unidadesJudiciariasOptions())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->live()
                            ->placeholder('Selecione a unidade anterior')
                            ->afterStateUpdated(function (Forms\Set $set): void {
                                $set('SetorAnterior', null);
                            }),

                        Select::make('SetorAnterior')
                            ->label('Setor Anterior')
                            ->options(fn (Forms\Get $get): array => self::setoresOptions($get('UnidadeAnterior')))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->disabled(fn (Forms\Get $get): bool => blank($get('UnidadeAnterior')))
                            ->placeholder('Selecione o setor anterior'),

                        Select::make('ComplementoAnterior')
                            ->label('Complemento Anterior')
                            ->relationship('complementoAnteriorRel', 'descricao')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Selecione o complemento anterior'),

                        TextInput::make('AndarAnterior')
                            ->label('Andar Anterior')
                            ->maxLength(255)
                            ->placeholder('Informe o andar anterior'),
                    ]),

                Section::make('Localização atual')
                    ->description('Informe a unidade e o setor de destino do patrimônio.')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(2)
                    ->schema([
                        Select::make('UnidadeAtual')
                            ->label('Unidade Judiciária Atual')
                            ->options(fn (): array => self::unidadesJudiciariasOptions())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->live()
                            ->placeholder('Selecione a unidade atual')
                            ->afterStateUpdated(function (Forms\Set $set): void {
                                $set('SetorAtual', null);
                            }),

                        Select::make('SetorAtual')
                            ->label('Setor Atual')
                            ->options(fn (Forms\Get $get): array => self::setoresOptions($get('UnidadeAtual')))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->disabled(fn (Forms\Get $get): bool => blank($get('UnidadeAtual')))
                            ->placeholder('Selecione o setor atual'),

                        Select::make('ComplementoAtual')
                            ->label('Complemento Atual')
                            ->relationship('complementoAtualRel', 'descricao')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Selecione o complemento atual'),

                        TextInput::make('AndarAtual')
                            ->label('Andar Atual')
                            ->maxLength(255)
                            ->placeholder('Informe o andar atual'),
                    ]),
            ]);
    }

    private static function unidadesJudiciariasOptions(): array
    {
        return Setores::query()
            ->whereColumn('id', 'CodigoPai')
            ->orderBy('Setor')
            ->pluck('Setor', 'id')
            ->toArray();
    }

    private static function setoresOptions(mixed $unidadeId): array
    {
        if (blank($unidadeId)) {
            return [];
        }

        return Setores::query()
            ->where('CodigoPai', $unidadeId)
            ->orderBy('Setor')
            ->pluck('Setor', 'id')
            ->toArray();
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->deferLoading()
            ->columns([
                TableColumns::text('NumPatrimonio', 'Nº Patrimônio', isFirstColumn: true)
                    ->badge(),
                TableColumns::text('unidadeAnteriorRel.Setor', 'Unidade Judiciária Anterior')
                    ->description(fn (TransferenciaBemMovel $record): ?string => $record->setorAnteriorRel?->Setor)
                    ->wrap(),
                TableColumns::text('complementoAnteriorRel.descricao', 'Complemento Anterior')
                    ->wrap(),
                TableColumns::text('unidadeAtualRel.Setor', 'Unidade Judiciária Atual')
                    ->description(fn (TransferenciaBemMovel $record): ?string => $record->setorAtualRel?->Setor)
                    ->wrap(),
                TableColumns::text('complementoAtualRel.descricao', 'Complemento Atual')
                    ->wrap(),
                TableColumns::dateTime('date_time', 'Atualizado em')
                    ->description(fn (TransferenciaBemMovel $record): ?string => $record->usuarioRef?->name),
                TableColumns::text('termoRel.num_termo', 'Termo')
                    ->formatStateUsing(fn (TransferenciaBemMovel $record): string => $record->termoRel?->termo_completo ?? '-'),
                TableColumns::text('pedido_no', 'Pedido Nº'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                ...TableDefaults::actions(),
                ActionGroup::make([
                    self::encaminharLogisiticaTableAction(),
                    self::atualizarDadosTableAction(),
                ])
                    ->hiddenLabel()
                    ->icon('heroicon-m-ellipsis-vertical'),
            ]);
    }

    public static function encaminharLogisiticaTableAction(): Action
    {
        return Action::make('encaminhar_logistica')
            ->label('Encaminhar para logística')
            ->icon('heroicon-s-bolt')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Encaminhar para logística')
            ->modalDescription('Deseja gerar a solicitação de transporte para a Seção de Patrimônio recolher/enviar este bem?')
            ->action(function (TransferenciaBemMovel $record): void {
                $userId = auth()->id();

                try {
                    DB::connection('egap')->transaction(function () use ($record, $userId): void {
                        $dadosOrigem = DB::connection('egap')
                            ->table('mat_termos')
                            ->join('mat_transferencia', 'mat_termos.id', '=', 'mat_transferencia.Termo')
                            ->leftJoin('age_regiao', 'mat_transferencia.UnidadeAtual', '=', 'age_regiao.unidade')
                            ->where('mat_transferencia.id', $record->id)
                            ->select([
                                'mat_termos.atualizado_por',
                                'mat_transferencia.UnidadeAtual',
                                'mat_transferencia.SetorAtual',
                                'mat_transferencia.pedido_no',
                                'age_regiao.id as regiao_id',
                            ])
                            ->first();

                        if (! $dadosOrigem) {
                            throw new \RuntimeException('Dados do termo não encontrados para originar o transporte.');
                        }

                        $solicitacaoId = DB::connection('egap')
                            ->table('age_solicitacao')
                            ->insertGetId([
                                'date_time' => now(),
                                'id_user' => $userId,
                                'tipo' => 2,
                                'id_situacao' => 6,
                                'id_solicitante' => $dadosOrigem->atualizado_por,
                                'unidade_solicitante' => $dadosOrigem->UnidadeAtual,
                                'setor_solicitante' => $dadosOrigem->SetorAtual,
                                'regiao' => $dadosOrigem->regiao_id,
                                'justificativa' => 'Solicitação de transporte gerada via e-GAP Laravel.',
                                'local_saida' => 'Seção de Patrimônio',
                            ]);

                        DB::connection('egap')
                            ->table('age_materiais')
                            ->insert([
                                'date_time' => now(),
                                'id_pedido' => $dadosOrigem->pedido_no,
                                'id_termo' => $record->Termo,
                                'id_user' => $userId,
                                'id_solicitacao' => $solicitacaoId,
                            ]);
                    });

                    Notification::make()
                        ->title('Solicitação encaminhada corretamente!')
                        ->success()
                        ->send();
                } catch (\Throwable $exception) {
                    Notification::make()
                        ->title('Erro ao encaminhar')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function atualizarDadosTableAction(): Action
    {
        return Action::make('atualizar_dados')
            ->label('Atualizar dados')
            ->icon('heroicon-m-chevron-double-up')
            ->color('gray')
            ->form([
                Grid::make(2)->schema([
                    Select::make('elemento')
                        ->label('Elemento')
                        ->options([
                            'UnidadeAtual' => 'Unidade Judiciária Atual',
                            'SetorAtual' => 'Setor Atual',
                        ])
                        ->required()
                        ->live(),
                    Select::make('valor')
                        ->label('Valor')
                        ->options(function (Forms\Get $get) {
                            return match ($get('elemento')) {
                                'UnidadeAtual' => DB::connection('egap')
                                    ->table('mat_setores')
                                    ->whereRaw('id = CodigoPai')
                                    ->orderBy('Setor')
                                    ->pluck('Setor', 'id'),
                                'SetorAtual' => DB::connection('egap')
                                    ->table('mat_setores')
                                    ->whereRaw('id != CodigoPai')
                                    ->orderBy('Setor')
                                    ->pluck('Setor', 'id'),
                                default => [],
                            };
                        })
                        ->searchable()
                        ->required(),
                ]),
            ])
            ->action(function (TransferenciaBemMovel $record, array $data): void {
                $record->update([
                    $data['elemento'] => $data['valor'],
                ]);

                Notification::make()
                    ->title('Dados da movimentação atualizados com sucesso!')
                    ->success()
                    ->send();
            });
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'complementoAnteriorRel',
            'complementoAtualRel',
            'setorAnteriorRel',
            'setorAtualRel',
            'termoRel',
            'unidadeAnteriorRel',
            'unidadeAtualRel',
            'usuarioRef',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransferenciaBems::route('/'),
            'create' => Pages\CreateTransferenciaBem::route('/create'),
            'edit' => Pages\EditTransferenciaBem::route('/{record}/edit'),
        ];
    }
}
