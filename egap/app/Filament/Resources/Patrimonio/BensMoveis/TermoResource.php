<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\TermoResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Agendamento\Solicitacao;
use App\Models\Patrimonio\BensMoveis\ArquivoDigital;
use App\Models\Patrimonio\BensMoveis\Termo;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;

class TermoResource extends Resource
{
    protected static ?string $model = Termo::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Termos de Responsabilidade';

    protected static ?string $modelLabel = 'Termo de Responsabilidade';

    protected static ?string $pluralModelLabel = 'Termos de Responsabilidade';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'bens-moveis/termos';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Termos de Responsabilidade')
                    ->description('Identificação, vínculo com pedido e situação atual do termo.')
                    ->icon('heroicon-o-document-text')
                    ->columns(3)
                    ->schema([
                        TextInput::make('num_termo')
                            ->label('Num. Termo')
                            ->placeholder('Informe o número')
                            ->required(),

                        TextInput::make('ano_termo')
                            ->label('Ano Termo')
                            ->numeric()
                            ->default(now()->year)
                            ->placeholder((string) now()->year)
                            ->required(),

                        Grid::make(1)->columnSpan(1)->schema([
                            Placeholder::make('atualizado_em_display')
                                ->label('Atualizado em')
                                ->content(fn ($record) => $record?->updated_at?->format('d/m/Y H:i') ?? '-'),

                            Placeholder::make('atualizado_por_display')
                                ->label('Atualizado por')
                                ->content(fn ($record) => $record?->atualizado_por ?? 'Sistema'),
                        ]),

                        Select::make('pedido_no')
                            ->label('Pedido No')
                            ->placeholder('Selecione o pedido')
                            ->searchable()
                            ->native(false)
                            ->options(function () {
                                return Solicitacao::select('id', 'date_time')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        $ano = Carbon::parse($item->date_time)->format('Y');

                                        return [$item->id => "{$item->id}/{$ano} - Prot. {$item->id}"];
                                    });
                            })
                            ->getSearchResultsUsing(fn (string $search): array => Solicitacao::where('id', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    $ano = Carbon::parse($item->date_time)->format('Y');

                                    return [$item->id => "{$item->id}/{$ano} - Prot. {$item->id}"];
                                })
                                ->toArray()),

                        Select::make('situacao_entrega')
                            ->label('Situação Entrega')
                            ->options([
                                'Reservado' => 'Reservado',
                                'Em rota' => 'Em rota',
                                'Entregue' => 'Entregue',
                                'Encaminhado para Logística' => 'Encaminhado para Logística',
                            ])
                            ->native(false),
                    ]),

                Section::make('Anexos do Termo')
                    ->description('Anexe o documento e registre informações complementares.')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Grid::make(4)->schema([
                            FileUpload::make('arquivo_digital')
                                ->label('Arquivo Digital')
                                ->directory('termos-patrimonio')
                                ->acceptedFileTypes(['application/pdf'])
                                ->columnSpan(2),

                            Select::make('situacao')
                                ->label('Situação')
                                ->options([
                                    'Validado' => 'Validado',
                                    'Pendente' => 'Pendente',
                                ])
                                ->native(false)
                                ->default('Validado'),

                            TextInput::make('web_status')
                                ->label('WEB')
                                ->numeric()
                                ->default(0),
                        ]),

                        Textarea::make('observacao')
                            ->label('Observação')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['arquivoDigital.validadoPor', 'responsavelRef'])
                ->withCount('transferencias'))
            ->columns([
                TableColumns::text('id', '#', isFirstColumn: true),

                TableColumns::text('pedido_no', 'Pedido No'),

                TableColumns::text('situacao_entrega', 'Situação Entrega')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Validado', 'Entregue' => 'success',
                        'Em rota' => 'info',
                        'Cancelado' => 'danger',
                        default => 'warning',
                    }),

                TableColumns::text('termo_completo', 'Termo')
                    ->searchable(['num_termo', 'ano_termo'])
                    ->weight('medium')
                    ->color('primary')
                    ->url(fn (Termo $record): string => route('termo.imprimir', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                TableColumns::text('arquivoDigital.arquivo_digital', 'Arquivo Digital')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? basename($state) : '-')
                    ->color('primary')
                    ->limit(50)
                    ->weight('medium')
                    ->url(function ($record) {
                        return 'https://sistemas.tjes.jus.br/patrimonio/images/termos/'.$record->arquivoDigital->arquivo_digital;
                    })
                    ->openUrlInNewTab(),

                TableColumns::text('responsavelRef.name', 'Atualizado por')
                    ->description(fn (Termo $record): string => $record->atualizado_em?->format('d/m/Y H:i') ?? '-'),

                TableColumns::text('arquivoDigital.validadoPor.name', 'Analisado por'),

                TableColumns::text('arquivoDigital.observacao', 'Observação')
                    ->limit(50)
                    ->tooltip(fn (Termo $record): ?string => $record->arquivoDigital?->observacao)
                    ->wrap(),

                TableColumns::text('arquivoDigital.situacao', 'Situação')
                    ->formatStateUsing(fn ($state): string => match ($state === null ? null : (int) $state) {
                        null => 'Indefinido',
                        0 => 'Pendente',
                        1 => 'Validado',
                        2 => 'Rejeitado',
                        4 => 'Cancelado',
                        default => 'Indefinido',
                    })
                    ->badge()
                    ->color(fn ($state): string => match ($state === null ? null : (int) $state) {
                        1 => 'success',
                        2, 4 => 'danger',
                        0 => 'warning',
                        default => 'gray',
                    }),

                TableColumns::text('arquivoDigital.web', 'WEB')
                    ->formatStateUsing(fn ($state): string => match ($state === null ? null : (int) $state) {
                        1 => 'Sim',
                        0 => 'Não',
                        default => '-',
                    })
                    ->badge()
                    ->color(fn ($state): string => (int) $state === 1 ? 'success' : 'gray'),

                TableColumns::text('transferencias_count', 'Materiais')
                    ->searchable(false)
                    ->color('primary')
                    ->weight('bold')
                    ->tooltip('Clique para visualizar os materiais deste termo')
                    ->extraAttributes([
                        'class' => 'cursor-pointer underline decoration-dotted underline-offset-4',
                    ])
                    ->action(self::materiaisTableAction()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('situacao_entrega')
                    ->label('Situação Entrega')
                    ->options([
                        'Reservado' => 'Reservado',
                        'Em rota' => 'Em rota',
                        'Entregue' => 'Entregue',
                        'Validado' => 'Validado',
                    ]),
                Tables\Filters\Filter::make('num_termo')
                    ->form([
                        TextInput::make('num_termo')
                            ->label('Termo')
                            ->placeholder('Informe o número do termo'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['num_termo'] ?? null),
                            fn (Builder $query): Builder => $query->where(
                                'num_termo',
                                'like',
                                '%'.trim($data['num_termo']).'%',
                            ),
                        )),

                Tables\Filters\SelectFilter::make('situacao_arquivo_filter')
                    ->label('Situação')
                    ->options([
                        0 => 'Pendente',
                        1 => 'Validado',
                        2 => 'Invalidado',
                        3 => 'Cancelado',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $situacao = $data['value'] ?? null;

                        if ($situacao === null || $situacao === '') {
                            return $query;
                        }

                        return $query->whereHas(
                            'arquivoDigital',
                            fn (Builder $query): Builder => $query->where('situacao', (int) $situacao),
                        );
                    }),

                Tables\Filters\SelectFilter::make('web')
                    ->label('WEB')
                    ->options([
                        1 => 'Sim',
                        0 => 'Não',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['value'] ?? null),
                            fn (Builder $query): Builder => $query->whereHas(
                                'arquivoDigital',
                                fn (Builder $query): Builder => $query->where('web', $data['value']),
                            ),
                        )),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                ...TableDefaults::actions(),
                ActionGroup::make([
                    self::imprimirTermoTableAction(),
                    self::novoTermoTableAction(),
                    self::gerarNovoArquivoTableAction(),
                    self::corrigirTermoTableAction(),
                    self::encaminharLogisticaTableAction(),
                ])
                    ->hiddenLabel()
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->defaultSort('id', 'desc');
    }

    private static function materiaisTableAction(): Action
    {
        return Action::make('visualizar_materiais')
            ->modalHeading(fn (Termo $record): string => "Materiais do termo {$record->termo_completo}")
            ->modalWidth('full')
            ->extraModalWindowAttributes([
                'style' => 'width: calc(100vw - 2rem); max-width: 96rem;',
            ])
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (Termo $record): HtmlString => new HtmlString(
                Livewire::mount(
                    'patrimonio.materiais-termo-modal',
                    ['termoId' => (int) $record->getKey()],
                    "materiais-termo-{$record->getKey()}",
                )
            ));
    }

    private static function imprimirTermoTableAction(): Action
    {
        return Action::make('imprimir_termo')
            ->label('Imprimir termo')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(fn (Termo $record): string => route('termo.imprimir', ['id' => $record->id]))
            ->openUrlInNewTab();
    }

    private static function encaminharLogisticaTableAction(): Action
    {
        return Action::make('encaminhar_logistica')
            ->label('Encaminhar para logística')
            ->icon('heroicon-o-arrow-up-on-square')
            ->color('gray');
    }

    private static function novoTermoTableAction(): Action
    {
        return Action::make('novo_termo_corretivo')
            ->label('Criar termo corretivo')
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Criar termo corretivo')
            ->modalDescription('Cria um novo termo invertendo a origem e o destino de todos os bens deste termo.')
            ->action(function (Termo $record): void {
                try {
                    $userId = auth()->id();

                    if (! $userId) {
                        throw new \RuntimeException('Usuário autenticado não identificado.');
                    }

                    $novoTermo = $record->getConnection()->transaction(function () use ($record, $userId): Termo {
                        $transferencias = $record->transferencias()
                            ->lockForUpdate()
                            ->get();

                        if ($transferencias->isEmpty()) {
                            throw new \RuntimeException('O termo não possui materiais para movimentar.');
                        }

                        $ano = (int) now()->year;
                        $ultimoTermo = Termo::query()
                            ->where('ano_termo', $ano)
                            ->orderByDesc('num_termo')
                            ->lockForUpdate()
                            ->first(['num_termo']);

                        $novoTermo = Termo::query()->create([
                            'num_termo' => ((int) $ultimoTermo?->num_termo) + 1,
                            'ano_termo' => $ano,
                            'situacao_entrega' => 'Reservado',
                        ]);

                        $novoTermo->arquivoDigital()->create([
                            'situacao' => 0,
                            'web' => 1,
                        ]);

                        $novoTermo->transferencias()->createMany(
                            $transferencias->map(fn ($transferencia): array => [
                                'NumPatrimonio' => $transferencia->NumPatrimonio,
                                'UnidadeAtual' => $transferencia->UnidadeAnterior,
                                'SetorAtual' => $transferencia->SetorAnterior,
                                'ComplementoAtual' => $transferencia->ComplementoAnterior,
                                'AndarAtual' => $transferencia->AndarAnterior,
                                'UnidadeAnterior' => $transferencia->UnidadeAtual,
                                'SetorAnterior' => $transferencia->SetorAtual,
                                'ComplementoAnterior' => $transferencia->ComplementoAtual,
                                'AndarAnterior' => $transferencia->AndarAtual,
                                'Usuario' => $userId,
                            ])->all(),
                        );

                        return $novoTermo;
                    });

                    Notification::make()
                        ->title("Termo {$novoTermo->num_termo}/{$novoTermo->ano_termo} criado corretamente.")
                        ->success()
                        ->send();
                } catch (\Throwable $exception) {
                    self::sendActionError('Erro ao criar o termo corretivo', $exception);
                }
            });
    }

    private static function gerarNovoArquivoTableAction(): Action
    {
        return Action::make('gerar_novo_arquivo')
            ->label('Gerar novo arquivo')
            ->icon('heroicon-o-document-plus')
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription('Regenera o arquivo HTML do termo com os dados atuais.')
            ->action(fn (Termo $record) => self::storeTermoDocument(
                $record,
                preserveValidation: false,
                successMessage: 'Arquivo gerado corretamente.',
            ))
            ->visible(fn (Termo $record): bool => (int) ($record->arquivoDigital?->situacao ?? 0) !== 1);
    }

    private static function corrigirTermoTableAction(): Action
    {
        return Action::make('corrigir_termo')
            ->label('Corrigir arquivo validado')
            ->icon('heroicon-o-wrench-screwdriver')
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription('Regenera o arquivo do termo validado, mantendo os dados de assinatura e validação.')
            ->action(fn (Termo $record) => self::storeTermoDocument(
                $record,
                preserveValidation: true,
                successMessage: 'Arquivo validado corrigido corretamente.',
            ))
            ->visible(fn (Termo $record): bool => (int) ($record->arquivoDigital?->situacao ?? 0) === 1);
    }

    private static function storeTermoDocument(
        Termo $record,
        bool $preserveValidation,
        string $successMessage,
    ): void {
        $absolutePath = null;
        $fileAlreadyExisted = false;

        try {
            if (! auth()->id()) {
                throw new \RuntimeException('Usuário autenticado não identificado.');
            }

            $record->getConnection()->transaction(function () use (
                $record,
                $preserveValidation,
                &$absolutePath,
                &$fileAlreadyExisted,
            ): void {
                $arquivoDigital = $record->arquivoDigital()
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                if ($preserveValidation && (int) ($arquivoDigital?->situacao ?? 0) !== 1) {
                    throw new \RuntimeException('O termo não possui um arquivo validado para correção.');
                }

                if (! $preserveValidation && (int) ($arquivoDigital?->situacao ?? 0) === 1) {
                    throw new \RuntimeException('O termo já foi validado. Utilize a ação de correção.');
                }

                $arquivoDigital ??= $record->arquivoDigital()->make([
                    'situacao' => 0,
                    'web' => 1,
                ]);

                $html = self::renderTermoDocument($record, $arquivoDigital, $preserveValidation);
                $year = (int) ($record->ano_termo ?: now()->year);
                $directory = public_path("images/termos/{$year}");
                $number = preg_replace('/[^0-9_-]/', '_', "{$record->num_termo}_{$year}");
                $filename = "termo_{$number}_".md5($html).'.html';
                $relativePath = "/images/termos/{$year}/{$filename}";
                $absolutePath = "{$directory}/{$filename}";

                File::ensureDirectoryExists($directory);
                $fileAlreadyExisted = File::exists($absolutePath);

                if (File::put($absolutePath, $html) === false) {
                    throw new \RuntimeException('Não foi possível gravar o arquivo do termo.');
                }

                $arquivoDigital->fill([
                    'arquivo_digital' => $relativePath,
                    'atualizado_em' => now(),
                    'atualizado_por' => auth()->id(),
                ]);

                if ($arquivoDigital->exists) {
                    // O evento saving legado altera date_time, que pode conter a data de validação histórica.
                    $arquivoDigital->saveQuietly();
                } else {
                    $record->arquivoDigital()->save($arquivoDigital);
                }
            });

            Notification::make()->title($successMessage)->success()->send();
        } catch (\Throwable $exception) {
            if ($absolutePath && ! $fileAlreadyExisted) {
                File::delete($absolutePath);
            }

            self::sendActionError('Erro ao gerar o arquivo do termo', $exception);
        }
    }

    private static function renderTermoDocument(
        Termo $record,
        ArquivoDigital $arquivoDigital,
        bool $preserveValidation,
    ): string {
        $record->load([
            'ultimaTransferencia.setorAtualRel',
            'ultimaTransferencia.complementoAtualRel',
            'ultimaTransferencia.usuarioRef.infoUser',
            'transferencias' => fn ($query) => $query
                ->select(['id', 'Termo', 'NumPatrimonio'])
                ->orderBy('id'),
            'transferencias.bem' => fn ($query) => $query
                ->select([
                    'id',
                    'NumPatrimonio',
                    'Descricao',
                    'Marca',
                    'Modelo',
                    'EstadodeConservacao',
                    'ValorAquisicao',
                    'ValordaReavaliacao',
                    'DatadeIncorporacao',
                ]),
            'transferencias.bem.marcaRef:id,descricao',
            'transferencias.bem.modeloRef:id,descricao',
        ]);

        $ultimaTransferencia = $record->ultimaTransferencia;
        $usuarioEmitente = $ultimaTransferencia?->usuarioRef;
        $infoEmitente = $usuarioEmitente?->infoUser;
        $usuarioAutenticado = auth()->user();
        $usuarioDestinatario = $preserveValidation
            ? $arquivoDigital->validadoPor()->with('infoUser')->first()
            : $usuarioEmitente;
        $infoDestinatario = $usuarioDestinatario?->infoUser;

        $bens = $record->transferencias
            ->map(function ($transferencia) {
                $bem = $transferencia->bem;

                if (! $bem) {
                    return null;
                }

                $bem->setAttribute('marca_desc', $bem->marcaRef?->descricao ?? $bem->marcaRef?->Descricao);
                $bem->setAttribute('modelo_desc', $bem->modeloRef?->descricao);
                $bem->setAttribute(
                    'ValorCalculado',
                    optional($bem->DatadeIncorporacao)->lt('2015-01-01')
                        ? $bem->ValordaReavaliacao
                        : $bem->ValorAquisicao
                );

                return $bem;
            })
            ->filter()
            ->unique('NumPatrimonio')
            ->values();

        if ($bens->isEmpty()) {
            throw new \RuntimeException('O termo não possui materiais para gerar o arquivo.');
        }

        if ($preserveValidation && ! $usuarioDestinatario) {
            throw new \RuntimeException('O usuário que validou o termo não foi encontrado.');
        }

        $dataEmissao = Carbon::parse($record->date_time ?? now())->format('d/m/Y');
        $dataAssinatura = $preserveValidation
            ? Carbon::parse($arquivoDigital->data_validacao ?? $arquivoDigital->date_time ?? now())->format('d/m/Y')
            : $dataEmissao;

        return view('patrimonio.termo_impresso', [
            'termo' => $record,
            'arquivoDigital' => $arquivoDigital,
            'bens' => $bens,
            'unidade' => $ultimaTransferencia?->setorAtualRel?->UnidadeOrganizacional,
            'setor' => $ultimaTransferencia?->setorAtualRel?->Setor,
            'complemento' => $ultimaTransferencia?->complementoAtualRel?->descricao,
            'usuarioEmitente' => $usuarioEmitente?->name ?? $usuarioAutenticado?->name,
            'cargoEmitente' => $infoEmitente?->cargo ?? $usuarioAutenticado?->cargo,
            'cpfEmitente' => self::formatCpf($infoEmitente?->cpf ?? $usuarioAutenticado?->cpf),
            'usuarioDestinatario' => $usuarioDestinatario?->name,
            'cargoDestinatario' => $infoDestinatario?->cargo,
            'cpfDestinatario' => self::formatCpf($infoDestinatario?->cpf),
            'dataEmissao' => $dataEmissao,
            'dataAssinatura' => $dataAssinatura,
            'assinaturaEletronica' => true,
        ])->render();
    }

    private static function formatCpf(?string $cpf): string
    {
        if (blank($cpf)) {
            return '';
        }

        $digits = str_pad(preg_replace('/\D/', '', $cpf), 11, '0', STR_PAD_LEFT);

        return substr($digits, 0, 3).'.'.substr($digits, 3, 3).'.'.substr($digits, 6, 3).'-'.substr($digits, 9, 2);
    }

    private static function sendActionError(string $title, \Throwable $exception): void
    {
        report($exception);

        Notification::make()
            ->title($title)
            ->body($exception->getMessage())
            ->danger()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTermos::route('/'),
            'create' => Pages\CreateTermo::route('/create'),
            'edit' => Pages\EditTermo::route('/{record}/edit'),
        ];
    }
}
