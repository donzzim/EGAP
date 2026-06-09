<?php

namespace App\Filament\Resources\Pedidos;

use App\Filament\Clusters\PedidosCluster;
use App\Filament\Resources\Pedidos\ValidarPedidoResource\Pages;
use App\Models\Almoxarifado\ItemPedido;
use App\Models\Almoxarifado\SituacaoPedido;
use App\Models\Cadastro\DescricaoDetalhada;
use App\Models\Cadastro\DescricaoResumida;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use Filament\Forms\Components\DateTimePicker;
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
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Throwable;

class ValidarPedidoResource extends Resource
{
    protected static ?string $model = ItemPedido::class;

    protected static ?string $cluster = PedidosCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'Requisição';

    protected static ?string $navigationLabel = 'Validar Pedidos';

    protected static ?string $title = 'Itens do Pedido';

    protected static ?string $modelLabel = 'item do pedido';

    protected static ?string $pluralModelLabel = 'itens do pedido';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Item do pedido')
                    ->description('Dados principais do material e quantidades do item.')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Select::make('material')
                            ->label('Material')
                            ->relationship('materialRel', 'Descricao')
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecione o material')
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('DescricaoDetalhada', null))
                            ->native(false),

                        Select::make('DescricaoDetalhada')
                            ->label('Descrição detalhada')
                            ->searchable()
                            ->preload()
                            ->options(fn (Get $get): array => DescricaoDetalhada::query()
                                ->when(
                                    filled($get('material')),
                                    fn ($query) => $query->where('descricao_resumida', $get('material')),
                                    fn ($query) => $query->whereRaw('1 = 0')
                                )
                                ->orderBy('descricao_detalhada')
                                ->pluck('descricao_detalhada', 'id')
                                ->toArray())
                            ->placeholder('Selecione a descrição detalhada')
                            ->disabled(fn (Get $get): bool => blank($get('material')))
                            ->native(false),

                        TextInput::make('QuantidadeMaterial')
                            ->label('Qtde Solicitada')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->placeholder('0')
                            ->prefixIcon('heroicon-o-shopping-cart'),

                        TextInput::make('quantidade_validada')
                            ->label('Qtde Validada')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('0')
                            ->prefixIcon('heroicon-o-check-circle'),

                        TextInput::make('QuantidadeMaterialAtendida')
                            ->label('Qtde Atendida')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('0')
                            ->prefixIcon('heroicon-o-inbox-stack'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),

                Section::make('Validação e andamento')
                    ->description('Controle de status, responsáveis, datas e observações do item.')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Select::make('situacao')
                            ->label('Situação Material')
                            ->relationship('situacaoRef', 'Descricao')
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecione a situação')
                            ->native(false),

                        Select::make('validado_por')
                            ->label('Validado por')
                            ->relationship('validadoPor', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecione o responsável')
                            ->native(false),

                        DateTimePicker::make('data_validacao')
                            ->label('Data Validação')
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        Select::make('cancelado_por')
                            ->label('Cancelado por')
                            ->relationship('canceladoPor', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecione o responsável')
                            ->native(false),

                        DateTimePicker::make('data_cancelado')
                            ->label('Data Cancelamento')
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        Textarea::make('ObservacaoItem')
                            ->label('Observação')
                            ->rows(4)
                            ->placeholder('Detalhes complementares sobre a validação do item.')
                            ->columnSpanFull(),

                        Textarea::make('justificativa')
                            ->label('Justificativa')
                            ->rows(4)
                            ->placeholder('Registre o motivo da validação, cancelamento ou ajuste.')
                            ->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No. Pedido/Solicitante')
                    ->description(fn (ItemPedido $record): string => (string) data_get($record, 'pedido.solicitante_get.name', '-'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('material_nome')
                    ->label('Material')
                    ->limit(50)
                    ->wrap()
                    ->state(fn (ItemPedido $record): string => $record->material_nome)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $builder) use ($search): void {
                            $builder
                                ->whereHas('descricaoDetalhadaRel', function (Builder $materialQuery) use ($search): void {
                                    $materialQuery->where('descricao_detalhada', 'like', "%{$search}%");
                                })
                                ->orWhereHas('materialRel', function (Builder $materialQuery) use ($search): void {
                                    $materialQuery->where('Descricao', 'like', "%{$search}%");
                                });
                        });
                    }),

                Tables\Columns\TextColumn::make('justificativa')
                    ->label('Justificativa')
                    ->formatStateUsing(fn (mixed $state): string => self::extractJustificativaText($state) ?: '-')
                    ->default('-')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('QuantidadeMaterial')
                    ->label('Qtde Solicitada')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantidade_validada')
                    ->label('Qtde Validada')
                    ->alignCenter()
                    ->default(0)
                    ->sortable(),

                Tables\Columns\TextColumn::make('QuantidadeMaterialAtendida')
                    ->label('Qtde Atendida')
                    ->alignCenter()
                    ->default(0)
                    ->sortable(),

                Tables\Columns\TextColumn::make('situacaoRef.Descricao')
                    ->label('Situação Material')
                    ->default('-')
                    ->alignCenter()
                    ->badge()
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('situacaoRef', function (Builder $situacaoQuery) use ($search): void {
                            $situacaoQuery->where('Descricao', 'like', "%{$search}%");
                        })),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ObservacaoItem')
                    ->label('Observação')
                    ->default('-')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('validadoPor.name')
                    ->label('Validado por')
                    ->default('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('data_validacao')
                    ->label('Data Validação')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('no_pedido')
                    ->label('No Pedido')
                    ->form([
                        TextInput::make('value')
                            ->label('No Pedido')
                            ->placeholder('Digite o número do pedido'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['value'] ?? null),
                            fn (Builder $builder): Builder => $builder->where('idPedido', 'like', '%' . $data['value'] . '%')
                        )),

                Tables\Filters\SelectFilter::make('material')
                    ->label('Material')
                    ->searchable()
                    ->options(self::getMaterialOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        [$type, $id] = array_pad(explode(':', (string) $value, 2), 2, null);

                        return match ($type) {
                            'detalhado' => $query->where('DescricaoDetalhada', $id),
                            'resumido' => $query->where('material', $id),
                            default => $query,
                        };
                    }),

                Tables\Filters\SelectFilter::make('situacao')
                    ->label('Situação Material')
                    ->searchable()
                    ->options(self::getSituacaoMaterialOptions()),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->color('gray'),
                    Tables\Actions\DeleteAction::make()
                        ->color('gray'),
                    self::makeStatusRecordAction(
                        name: 'validar_materiais',
                        label: 'Validar Materiais',
                        icon: 'heroicon-o-check-badge',
                        payloadFactory: fn (): array => [
                            'situacao' => 7,
                            'data_validacao' => now(),
                            'validado_por' => self::getCurrentUserId(),
                        ],
                        successMessage: 'Material validado com sucesso.',
                    ),
                    self::makeStatusRecordAction(
                        name: 'invalidar_pedidos',
                        label: 'Invalidar Pedidos',
                        icon: 'heroicon-o-x-circle',
                        payloadFactory: fn (): array => [
                            'situacao' => 5,
                            'data_validacao' => now(),
                            'validado_por' => self::getCurrentUserId(),
                        ],
                        successMessage: 'Material invalidado com sucesso.',
                    ),
                    self::makeStatusRecordAction(
                        name: 'cancelar_materiais',
                        label: 'Cancelar Materiais',
                        icon: 'heroicon-o-no-symbol',
                        payloadFactory: fn (): array => [
                            'situacao' => 4,
                            'data_cancelado' => now(),
                            'cancelado_por' => self::getCurrentUserId(),
                        ],
                        successMessage: 'Material cancelado com sucesso.',
                    ),
                    self::makeSendEmailRecordAction(),
                    self::makeMateriaisDoSetorRecordAction(),
                    self::makeStatusRecordAction(
                        name: 'suspender_pedido',
                        label: 'Suspender Pedido',
                        icon: 'heroicon-o-pause-circle',
                        payloadFactory: fn (): array => [
                            'situacao' => 10,
                            'date_time' => now(),
                            'validado_por' => self::getCurrentUserId(),
                        ],
                        successMessage: 'Pedido suspenso com sucesso.',
                    ),
                    self::makeUpdateDataRecordAction(),
                ])
                    ->label('Ações')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->button(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->color('gray'),
                    self::makeStatusBulkAction(
                        name: 'validar_itens',
                        label: 'Validar Materiais',
                        icon: 'heroicon-o-check-badge',
                        payloadFactory: fn (): array => [
                            'situacao' => 7,
                            'data_validacao' => now(),
                            'validado_por' => self::getCurrentUserId(),
                        ],
                        successMessage: 'Materiais validados com sucesso.',
                    ),
                    self::makeStatusBulkAction(
                        name: 'invalidar_itens',
                        label: 'Invalidar Pedidos',
                        icon: 'heroicon-o-x-circle',
                        payloadFactory: fn (): array => [
                            'situacao' => 5,
                            'data_validacao' => now(),
                            'validado_por' => self::getCurrentUserId(),
                        ],
                        successMessage: 'Materiais invalidados com sucesso.',
                    ),
                    self::makeStatusBulkAction(
                        name: 'cancelar_itens',
                        label: 'Cancelar Materiais',
                        icon: 'heroicon-o-no-symbol',
                        payloadFactory: fn (): array => [
                            'situacao' => 4,
                            'data_cancelado' => now(),
                            'cancelado_por' => self::getCurrentUserId(),
                        ],
                        successMessage: 'Materiais cancelados com sucesso.',
                    ),
                    self::makeSendEmailBulkAction(),
                    self::makeMateriaisDoSetorBulkAction(),
                    self::makeStatusBulkAction(
                        name: 'suspender_itens',
                        label: 'Suspender Pedido',
                        icon: 'heroicon-o-pause-circle',
                        payloadFactory: fn (): array => [
                            'situacao' => 10,
                            'date_time' => now(),
                            'validado_por' => self::getCurrentUserId(),
                        ],
                        successMessage: 'Pedidos suspensos com sucesso.',
                    ),
                    self::makeUpdateDataBulkAction(),
                ]),
            ])
            ->defaultSort('id', 'desc')
            ->selectCurrentPageOnly()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped()
            ->deferLoading()
            ->recordUrl(null);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'pedido',
            'pedido.solicitante_get',
            'pedido.setor_get',
            'materialRel',
            'descricaoDetalhadaRel',
            'situacaoRef',
            'validadoPor',
        ]);
    }

    protected static function makeStatusRecordAction(
        string $name,
        string $label,
        string $icon,
        callable $payloadFactory,
        string $successMessage,
        bool $requiresConfirmation = true,
        string $color = 'gray',
    ): Action {
        $action = Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->action(function (ItemPedido $record) use ($payloadFactory, $successMessage): void {
                self::updateRecords(
                    records: self::freshRecords([$record->getKey()]),
                    attributes: $payloadFactory(),
                    successMessage: $successMessage,
                );
            });

        if ($requiresConfirmation) {
            $action->requiresConfirmation();
        }

        return $action;
    }

    protected static function makeStatusBulkAction(
        string $name,
        string $label,
        string $icon,
        callable $payloadFactory,
        string $successMessage,
        bool $requiresConfirmation = true,
        string $color = 'gray',
    ): BulkAction {
        $action = BulkAction::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) use ($payloadFactory, $successMessage): void {
                self::updateRecords(
                    records: self::freshRecords($records->modelKeys()),
                    attributes: $payloadFactory(),
                    successMessage: $successMessage,
                );
            });

        if ($requiresConfirmation) {
            $action->requiresConfirmation();
        }

        return $action;
    }

    protected static function makeSendEmailRecordAction(): Action
    {
        return Action::make('enviar_email')
            ->label('Enviar email')
            ->icon('heroicon-o-envelope')
            ->color('gray')
            ->action(fn (ItemPedido $record) => self::sendEmails(self::freshRecords([$record->getKey()])));
    }

    protected static function makeSendEmailBulkAction(): BulkAction
    {
        return BulkAction::make('enviar_email_itens')
            ->label('Enviar email')
            ->icon('heroicon-o-envelope')
            ->color('gray')
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records) => self::sendEmails(self::freshRecords($records->modelKeys())));
    }

    protected static function makeMateriaisDoSetorRecordAction(): Action
    {
        return Action::make('materiais_do_setor')
            ->label('Materiais do Setor')
            ->icon('heroicon-o-building-office-2')
            ->color('gray')
            ->modalHeading('Materiais do Setor')
            ->modalWidth(MaxWidth::FiveExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (ItemPedido $record): HtmlString => new HtmlString(
                self::renderMateriaisDoSetorHtml(self::freshRecords([$record->getKey()])->first())
            ));
    }

    protected static function makeMateriaisDoSetorBulkAction(): BulkAction
    {
        return BulkAction::make('materiais_setor_itens')
            ->label('Materiais do Setor')
            ->icon('heroicon-o-building-office-2')
            ->color('gray')
            ->deselectRecordsAfterCompletion()
            ->modalHeading('Materiais do Setor')
            ->modalWidth(MaxWidth::FiveExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (Collection $records): HtmlString => new HtmlString(
                self::renderMateriaisDoSetorBulkHtml(self::freshRecords($records->modelKeys()))
            ))
            ->action(static function (): void {
            });
    }

    protected static function makePendingRecordAction(
        string $name,
        string $label,
        string $icon,
        bool $requiresConfirmation = false,
        string $color = 'gray',
    ): Action {
        $action = Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->action(fn (ItemPedido $record) => self::notifyPendingRecordAction($label, $record));

        if ($requiresConfirmation) {
            $action->requiresConfirmation();
        }

        return $action;
    }

    protected static function makeUpdateDataRecordAction(): Action
    {
        return Action::make('atualizar_dados')
            ->label('Atualizar Dados')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->modalHeading('Atualizar Dados')
            ->fillForm(fn (ItemPedido $record): array => [
                'quantidade_validada' => $record->quantidade_validada,
                'ObservacaoItem' => $record->ObservacaoItem,
                'data_validacao' => $record->data_validacao,
            ])
            ->form(self::getUpdateDataFormSchema())
            ->action(function (ItemPedido $record, array $data): void {
                self::updateRecords(
                    records: self::freshRecords([$record->getKey()]),
                    attributes: self::mutateQuickUpdateData($data),
                    successMessage: 'Dados atualizados com sucesso.',
                );
            });
    }

    protected static function makePendingBulkAction(
        string $name,
        string $label,
        string $icon,
        bool $requiresConfirmation = false,
        string $color = 'gray',
    ): BulkAction {
        $action = BulkAction::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records) => self::notifyPendingBulkAction($label, $records));

        if ($requiresConfirmation) {
            $action->requiresConfirmation();
        }

        return $action;
    }

    protected static function makeUpdateDataBulkAction(): BulkAction
    {
        return BulkAction::make('atualizar_itens')
            ->label('Atualizar Dados')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->deselectRecordsAfterCompletion()
            ->modalHeading('Atualizar Dados')
            ->form(self::getUpdateDataFormSchema())
            ->action(function (Collection $records, array $data): void {
                self::updateRecords(
                    records: self::freshRecords($records->modelKeys()),
                    attributes: self::mutateQuickUpdateData($data),
                    successMessage: 'Dados atualizados com sucesso.',
                );
            });
    }

    protected static function updateRecords(Collection $records, array $attributes, string $successMessage): void
    {
        if ($records->isEmpty()) {
            Notification::make()
                ->title('Nenhum item selecionado.')
                ->warning()
                ->send();

            return;
        }

        ItemPedido::query()
            ->whereKey($records->modelKeys())
            ->update($attributes);

        Notification::make()
            ->title($successMessage)
            ->success()
            ->send();
    }

    protected static function sendEmails(Collection $records): void
    {
        if ($records->isEmpty()) {
            Notification::make()
                ->title('Nenhum item selecionado.')
                ->warning()
                ->send();

            return;
        }

        $sent = 0;
        $failed = [];

        foreach ($records as $record) {
            $email = self::resolveSolicitanteEmail($record);

            if ($email === null) {
                $failed[] = 'Item ' . $record->id . ' sem email valido do solicitante.';

                continue;
            }

            try {
                self::sendPedidoStatusEmail($record, $email);

                $sent++;
            } catch (Throwable $exception) {
                Log::error('Falha ao enviar email de validacao do pedido.', [
                    'item_pedido_id' => $record->id,
                    'pedido_id' => $record->idPedido,
                    'email' => $email,
                    'exception' => $exception,
                ]);

                $failed[] = 'Item ' . $record->id . ' nao enviado.';
            }
        }

        $notification = Notification::make()
            ->title('Enviar email')
            ->body(self::buildEmailNotificationBody($sent, $failed));

        if ($sent > 0 && $failed === []) {
            $notification->success();
        } elseif ($sent > 0) {
            $notification->warning();
        } else {
            $notification->danger();
        }

        $notification->send();
    }

    protected static function getUpdateDataFormSchema(): array
    {
        return [
            TextInput::make('quantidade_validada')
                ->label('Qtde Validada')
                ->numeric(),

            DateTimePicker::make('data_validacao')
                ->label('Data Validação')
                ->seconds(false),

            Textarea::make('ObservacaoItem')
                ->label('Observação')
                ->rows(3)
                ->columnSpanFull(),
        ];
    }

    protected static function mutateQuickUpdateData(array $data): array
    {
        $data['date_time'] = now();

        if (blank($data['validado_por'] ?? null) && filled($data['data_validacao'] ?? null) && filled(self::getCurrentUserId())) {
            $data['validado_por'] = self::getCurrentUserId();
        }

        return $data;
    }

    protected static function extractJustificativaText(mixed $value): string
    {
        if (blank($value)) {
            return '';
        }

        $decoded = $value;

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return self::extractJustificativaFromStructuredString($value);
            }
        }

        if (is_array($decoded)) {
            return trim((string) ($decoded['Justificativa'] ?? $decoded['justificativa'] ?? ''));
        }

        return '';
    }

    protected static function extractJustificativaFromStructuredString(string $value): string
    {
        $text = trim($value, "{} \t\n\r\0\x0B");

        foreach (explode(';', $text) as $part) {
            $part = trim($part);

            if (str_starts_with(mb_strtolower($part), 'justificativa:')) {
                return trim(substr($part, strlen('Justificativa:')));
            }
        }

        return trim($value);
    }

    protected static function sendPedidoStatusEmail(ItemPedido $record, string $email): void
    {
        Mail::to($email)->send(new PedidoStatusMail(self::buildEmailPayload($record)));
    }

    protected static function buildEmailSubject(): string
    {
        return 'Pedido Online - Patrimonio';
    }

    protected static function resolveSolicitanteEmail(ItemPedido $record): ?string
    {
        $email = trim((string) data_get($record, 'pedido.solicitante_get.email', ''));

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        return $email;
    }

    protected static function buildEmailPayload(ItemPedido $record): array
    {
        return [
            'subject' => self::buildEmailSubject(),
            'pedido_numero' => self::formatPedidoNumero($record),
            'solicitante' => (string) data_get($record, 'pedido.solicitante_get.name', 'Solicitante'),
            'situacao' => self::resolveSituacaoLabel($record),
            'material' => $record->material_nome,
            'validado_em' => $record->data_validacao?->format('d/m/Y H:i'),
            'observacao' => (string) ($record->ObservacaoItem ?: ''),
        ];
    }

    protected static function buildEmailNotificationBody(int $sent, array $failed): string
    {
        $parts = [];

        if ($sent > 0) {
            $parts[] = $sent . ' email(ns) enviado(s).';
        }

        if ($failed !== []) {
            $parts[] = count($failed) . ' falha(s): ' . implode(' ', array_slice($failed, 0, 3));
        }

        return implode(' ', $parts) ?: 'Nenhum email foi enviado.';
    }

    protected static function renderMateriaisDoSetorHtml(?ItemPedido $record): string
    {
        if (! $record instanceof ItemPedido) {
            return self::renderEmptyHtml('Nao foi possivel localizar o item selecionado.');
        }

        $setorId = data_get($record, 'pedido.Setor');
        $materialId = $record->material;

        if (blank($materialId) || blank($setorId)) {
            return self::renderEmptyHtml('O item nao possui material resumido ou setor suficiente para consulta.');
        }

        $bens = self::queryMateriaisDoSetor($materialId, $setorId);
        $material = e($record->material_nome);
        $setor = e((string) data_get($record, 'pedido.setor_get.Setor', 'Setor nao informado'));

        $html = '<div style="display:grid;gap:12px;">';
        $html .= '<div><strong>Material:</strong> ' . $material . '<br /><strong>Setor:</strong> ' . $setor . '</div>';
        $html .= self::renderBensTableHtml($bens);
        $html .= '</div>';

        return $html;
    }

    protected static function renderMateriaisDoSetorBulkHtml(Collection $records): string
    {
        if ($records->isEmpty()) {
            return self::renderEmptyHtml('Nenhum item selecionado.');
        }

        $groups = $records
            ->filter(fn (ItemPedido $record): bool => filled($record->material) && filled(data_get($record, 'pedido.Setor')))
            ->groupBy(fn (ItemPedido $record): string => $record->material . '|' . data_get($record, 'pedido.Setor'));

        if ($groups->isEmpty()) {
            return self::renderEmptyHtml('Nenhum item selecionado possui material resumido e setor para consulta.');
        }

        $html = '<div style="display:grid;gap:20px;">';

        foreach ($groups as $group) {
            /** @var ItemPedido $reference */
            $reference = $group->first();
            $material = e($reference->material_nome);
            $setor = e((string) data_get($reference, 'pedido.setor_get.Setor', 'Setor nao informado'));
            $bens = self::queryMateriaisDoSetor($reference->material, data_get($reference, 'pedido.Setor'));

            $html .= '<section style="display:grid;gap:12px;">';
            $html .= '<div><strong>Material:</strong> ' . $material . '<br /><strong>Setor:</strong> ' . $setor . '<br /><strong>Itens selecionados:</strong> ' . $group->count() . '</div>';
            $html .= self::renderBensTableHtml($bens);
            $html .= '</section>';
        }

        $html .= '</div>';

        return $html;
    }

    protected static function queryMateriaisDoSetor(int|string $materialId, int|string $setorId): Collection
    {
        return BemMovel::query()
            ->with(['setorRef', 'unidadeJudiciariaRef', 'descricaoResumidaBemRef'])
            ->where('DescricaoResumidadoBem', $materialId)
            ->where('Setor', $setorId)
            ->orderBy('NumPatrimonio')
            ->limit(100)
            ->get();
    }

    protected static function renderBensTableHtml(Collection $bens): string
    {
        if ($bens->isEmpty()) {
            return self::renderEmptyHtml('Nenhum material localizado para o setor informado.');
        }

        $rows = $bens->map(function (BemMovel $bem): string {
            $patrimonio = e((string) ($bem->NumPatrimonio ?: '-'));
            $descricao = e((string) ($bem->Descricao ?: $bem->descricaoResumidaBemRef?->Descricao ?: '-'));
            $unidade = e((string) ($bem->unidadeJudiciariaRef?->Setor ?: '-'));
            $setor = e((string) ($bem->setorRef?->Setor ?: '-'));

            return '<tr>'
                . '<td style="padding:8px;border:1px solid #d1d5db;">' . $patrimonio . '</td>'
                . '<td style="padding:8px;border:1px solid #d1d5db;">' . $descricao . '</td>'
                . '<td style="padding:8px;border:1px solid #d1d5db;">' . $unidade . '</td>'
                . '<td style="padding:8px;border:1px solid #d1d5db;">' . $setor . '</td>'
                . '</tr>';
        })->implode('');

        return '<div style="overflow:auto;">'
            . '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
            . '<thead>'
            . '<tr style="background:#f3f4f6;">'
            . '<th style="padding:8px;border:1px solid #d1d5db;text-align:left;">Patrimonio</th>'
            . '<th style="padding:8px;border:1px solid #d1d5db;text-align:left;">Descricao</th>'
            . '<th style="padding:8px;border:1px solid #d1d5db;text-align:left;">Unidade</th>'
            . '<th style="padding:8px;border:1px solid #d1d5db;text-align:left;">Setor</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>'
            . '</div>';
    }

    protected static function renderEmptyHtml(string $message): string
    {
        return '<div style="padding:16px;border:1px solid #d1d5db;border-radius:6px;background:#f9fafb;">'
            . e($message)
            . '</div>';
    }

    protected static function resolveSituacaoLabel(ItemPedido $record): string
    {
        return e((string) (
            $record->situacaoRef?->Descricao
            ?? match ((int) $record->situacao) {
            3 => 'Atendido',
            4 => 'Cancelado',
            5 => 'Invalidado',
            6 => 'Em analise',
            7 => 'Validado',
            10 => 'Suspenso',
            default => 'Nao informado',
        }
        ));
    }

    protected static function formatPedidoNumero(ItemPedido $record): string
    {
        $ano = data_get($record, 'pedido.date_time')?->format('Y') ?? now()->format('Y');

        return e($record->idPedido . '/' . $ano);
    }

    protected static function getCurrentUserId(): int|string|null
    {
        if (function_exists('filament') && filament()->auth()->check()) {
            return filament()->auth()->id();
        }

        return auth()->id();
    }

    protected static function freshRecords(array $ids): Collection
    {
        if ($ids === []) {
            return new Collection();
        }

        return ItemPedido::query()
            ->with([
                'pedido',
                'pedido.solicitante_get',
                'pedido.setor_get',
                'materialRel',
                'descricaoDetalhadaRel',
                'situacaoRef',
                'validadoPor',
            ])
            ->whereKey($ids)
            ->get();
    }

    protected static function notifyPendingRecordAction(string $label, ItemPedido $record): void
    {
        Notification::make()
            ->title($label)
            ->body("Acao ainda nao implementada para o item {$record->id}.")
            ->info()
            ->send();
    }

    protected static function notifyPendingBulkAction(string $label, Collection $records): void
    {
        Notification::make()
            ->title($label)
            ->body('Acao em lote ainda nao implementada para ' . $records->count() . ' item(ns).')
            ->info()
            ->send();
    }

    protected static function getMaterialOptions(): array
    {
        $detalhados = DescricaoDetalhada::query()
            ->whereIn('id', ItemPedido::query()->select('DescricaoDetalhada')->whereNotNull('DescricaoDetalhada'))
            ->orderBy('descricao_detalhada')
            ->pluck('descricao_detalhada', 'id')
            ->mapWithKeys(fn (string $label, int|string $id): array => ["detalhado:{$id}" => $label])
            ->all();

        $resumidos = DescricaoResumida::query()
            ->whereIn('id', ItemPedido::query()->select('material')->whereNotNull('material'))
            ->orderBy('Descricao')
            ->pluck('Descricao', 'id')
            ->mapWithKeys(fn (string $label, int|string $id): array => ["resumido:{$id}" => $label])
            ->all();

        return $detalhados + $resumidos;
    }

    protected static function getSituacaoMaterialOptions(): array
    {
        return SituacaoPedido::query()
            ->whereIn('id', ItemPedido::query()->select('situacao')->whereNotNull('situacao'))
            ->orderBy('Descricao')
            ->pluck('Descricao', 'id')
            ->all();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Pedidos\ValidarPedidoResource\Pages\ListValidarPedidos::route('/'),
            'create' => \App\Filament\Resources\Pedidos\ValidarPedidoResource\Pages\CreateValidarPedido::route('/create'),
            'edit' => \App\Filament\Resources\Pedidos\ValidarPedidoResource\Pages\EditValidarPedido::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}

class PedidoStatusMail extends Mailable
{
    public function __construct(
        protected array $payload,
    ) {
    }

    public function build()
    {
        return $this
            ->subject($this->payload['subject'])
            ->html(Blade::render(<<<'BLADE'
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>{{ $payload['subject'] }}</title>
    </head>
    <body style="margin:0;padding:24px;font-family:Arial,Helvetica,sans-serif;color:#111827;background:#f9fafb;">
        <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;padding:24px;">
            <p style="margin:0 0 16px;">Ola, <strong>{{ $payload['solicitante'] }}</strong>.</p>

            <p style="margin:0 0 16px;">
                Informamos que o pedido foi analisado e teve seu status alterado, conforme informações abaixo.
            </p>

            <p style="margin:0 0 8px;">Pedido No: <strong>{{ $payload['pedido_numero'] }}</strong></p>
            <p style="margin:0 0 8px;">Situação: <strong>{{ $payload['situacao'] }}</strong></p>
            <p style="margin:0 0 8px;">Material: <strong>{{ $payload['material'] }}</strong></p>

            @if(filled($payload['validado_em']))
                <p style="margin:0 0 16px;">Validado em: <strong>{{ $payload['validado_em'] }}</strong></p>
            @endif

            @if(filled($payload['observacao']))
                <div style="margin:16px 0;padding:16px;border:1px solid #e5e7eb;background:#f9fafb;">
                    <strong>Observação:</strong><br>
                    {!! nl2br(e($payload['observacao'])) !!}
                </div>
            @endif

            <p style="margin:16px 0 0;"><strong>Seção de Patrimonio do TJES</strong></p>
        </div>
    </body>
</html>
BLADE, [
                'payload' => $this->payload,
            ]));
    }
}
