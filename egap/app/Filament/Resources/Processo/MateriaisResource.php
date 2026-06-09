<?php

namespace App\Filament\Resources\Processo;

use App\Filament\Resources\Processo\MateriaisResource\Pages;
use App\Models\Cadastro\DescricaoDetalhada;
use App\Models\Patrimonio\BensImoveis\Processo;
use App\Models\Processo\ProMaterial;
use App\Models\UserEgap;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class MateriaisResource extends Resource
{
    protected static ?string $model = ProMaterial::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Processos';

    protected static ?string $navigationLabel = 'Materiais/Serviços';

    protected static ?string $modelLabel = 'Material do Processo';

    protected static ?string $pluralModelLabel = 'Materiais do Processo';

    protected static ?string $slug = 'processos/materiais';

    protected static ?string $recordTitleAttribute = 'lote';

    protected static ?int $navigationSort = 4;

//    public static function form(Form $form): Form
//    {
//        return $form
//            ->schema([
//                Forms\Components\Section::make('Vinculo e rastreabilidade')
//                    ->description('Associe o item ao processo e mantenha o histórico de atualização.')
//                    ->schema([
//                        Forms\Components\Select::make('processo')
//                            ->label('Processo')
//                            ->required()
//                            ->native(false)
//                            ->searchable()
//                            ->placeholder('Selecione o processo')
//                            ->options(fn (): array => static::getProcessoOptions())
//                            ->getSearchResultsUsing(fn (string $search): array => static::getProcessoOptions($search))
//                            ->getOptionLabelUsing(fn ($value): ?string => static::findProcessoLabel($value)),
//
//                        Forms\Components\Select::make('material')
//                            ->label('Material')
//                            ->required()
//                            ->native(false)
//                            ->searchable()
//                            ->placeholder('Selecione o material')
//                            ->options(fn (): array => static::getMaterialOptions())
//                            ->getSearchResultsUsing(fn (string $search): array => static::getMaterialOptions($search))
//                            ->getOptionLabelUsing(fn ($value): ?string => static::findMaterialLabel($value)),
//
//                        Forms\Components\Select::make('atualizado_por')
//                            ->label('Atualizado por')
//                            ->native(false)
//                            ->searchable()
//                            ->preload()
//                            ->placeholder('Selecione o usuário responsável')
//                            ->options(fn (): array => UserEgap::query()
//                                ->orderBy('name')
//                                ->pluck('name', 'id')
//                                ->all()
//                            )
//                            ->default(fn (): ?int => auth()->id()),
//
//                        Forms\Components\DateTimePicker::make('date_time')
//                            ->label('Data da atualização')
//                            ->seconds(false)
//                            ->native(false)
//                            ->default(now())
//                            ->required(),
//                    ])
//                    ->columns(2),
//
//                Forms\Components\Section::make('Controle do material')
//                    ->description('Organize faixa de estoque, preço de referencia e identificação do lote.')
//                    ->schema([
//                        Forms\Components\TextInput::make('lote')
//                            ->label('Lote')
//                            ->maxLength(255)
//                            ->placeholder('Ex: LT-2026-001')
//                            ->columnSpan(2),
//
//                        Forms\Components\TextInput::make('preco')
//                            ->label('Preço')
//                            ->numeric()
//                            ->inputMode('decimal')
//                            ->prefix('R$')
//                            ->step('0.01')
//                            ->minValue(0)
//                            ->placeholder('0,00'),
//
//                        Forms\Components\TextInput::make('qtde_min')
//                            ->label('Quantidade minima')
//                            ->numeric()
//                            ->inputMode('decimal')
//                            ->step('0.01')
//                            ->minValue(0)
//                            ->placeholder('0,00'),
//
//                        Forms\Components\TextInput::make('qtde_max')
//                            ->label('Quantidade maxima')
//                            ->numeric()
//                            ->inputMode('decimal')
//                            ->step('0.01')
//                            ->minValue(0)
//                            ->placeholder('0,00'),
//
//                        Forms\Components\TextInput::make('saldo_atual')
//                            ->label('Saldo atual')
//                            ->numeric()
//                            ->inputMode('decimal')
//                            ->step('0.01')
//                            ->placeholder('0,00')
//                            ->helperText('Comparado automaticamente com os limites informados.'),
//
//                        Forms\Components\Placeholder::make('status_faixa')
//                            ->label('Status da faixa')
//                            ->content(fn ($get): string => static::buildFaixaStatus(
//                                $get('qtde_min'),
//                                $get('qtde_max'),
//                                $get('saldo_atual'),
//                            )),
//                    ])
//                    ->columns(3),
//            ]);
//    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'processoRelacaoRef',
                'materialRelacaoRef',
                'atualizadoPorRelacaoRef',
            ]))
            ->defaultSort('date_time', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('processoRelacaoRef.num_processo')
                    ->label('Processo')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('materialRelacaoRef.descricao_detalhada')
                    ->label('Material')
                    ->searchable()
                    ->wrap()
                    ->limit(70),

                Tables\Columns\TextColumn::make('lote')
                    ->label('Lote')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('saldo_atual')
                    ->label('Saldo')
                    ->numeric(thousandsSeparator: '.')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (ProMaterial $record): string => static::resolveSaldoColor($record))
                    ->description(fn (ProMaterial $record): ?string => static::buildSaldoResumo($record))
                    ->sortable(),

                Tables\Columns\TextColumn::make('qtde_min')
                    ->label('Min.')
                    ->numeric(thousandsSeparator: '.')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('qtde_max')
                    ->label('Max.')
                    ->numeric(thousandsSeparator: '.')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('preco')
                    ->label('Preço')
                    ->money('BRL', true)
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizadoPorRelacaoRef.name')
                    ->label('Atualizado por')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('processo')
                    ->label('Processo')
                    ->searchable()
                    ->options(fn (): array => static::getProcessoOptions(limit: 150)),

                Tables\Filters\SelectFilter::make('material')
                    ->label('Material')
                    ->searchable()
                    ->options(fn (): array => static::getMaterialOptions(limit: 150)),

                Tables\Filters\SelectFilter::make('atualizado_por')
                    ->label('Atualizado por')
                    ->searchable()
                    ->options(fn (): array => UserEgap::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()
                    ),

                Tables\Filters\Filter::make('periodo')
                    ->label('Periodo')
                    ->form([
                        Forms\Components\DatePicker::make('de')
                            ->label('De')
                            ->native(false),
                        Forms\Components\DatePicker::make('ate')
                            ->label('Ate')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['de'] ?? null, fn (Builder $subQuery, $date) => $subQuery->whereDate('date_time', '>=', $date))
                            ->when($data['ate'] ?? null, fn (Builder $subQuery, $date) => $subQuery->whereDate('date_time', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                Tables\Actions\ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Excluir')
                    ->hiddenLabel()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Excluir selecionados'),
                ]),
            ])
            ->searchPlaceholder('Busque por processo, material ou lote')
            ->paginated([25, 50, 100, 200])
            ->defaultPaginationPageOption(25)
            ->striped()
            ->deferLoading()
            ->emptyStateHeading('Nenhum material cadastrado')
            ->emptyStateDescription('Cadastre o primeiro material vinculado a um processo para iniciar o controle.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMateriais::route('/'),
        ];
    }

    protected static function getProcessoOptions(?string $search = null, int $limit = 50): array
    {
        return Processo::query()
            ->when($search, function (Builder $query, string $searchTerm) {
                $query->where(function (Builder $subQuery) use ($searchTerm) {
                    $subQuery
                        ->where('num_processo', 'like', "%{$searchTerm}%")
                        ->orWhere('no_processo_sei', 'like', "%{$searchTerm}%");
                });
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->mapWithKeys(fn (Processo $processo): array => [
                $processo->id => static::formatProcessoLabel($processo),
            ])
            ->all();
    }

    protected static function getMaterialOptions(?string $search = null, int $limit = 50): array
    {
        return DescricaoDetalhada::query()
            ->when($search, fn (Builder $query, string $searchTerm) => $query->where('descricao_detalhada', 'like', "%{$searchTerm}%"))
            ->orderBy('descricao_detalhada')
            ->limit($limit)
            ->get()
            ->mapWithKeys(fn (DescricaoDetalhada $material): array => [
                $material->id => static::formatMaterialLabel($material),
            ])
            ->all();
    }

    protected static function findProcessoLabel($value): ?string
    {
        if (! $value) {
            return null;
        }

        $processo = Processo::query()->find($value);

        return $processo ? static::formatProcessoLabel($processo) : null;
    }

    protected static function findMaterialLabel($value): ?string
    {
        if (! $value) {
            return null;
        }

        $material = DescricaoDetalhada::query()->find($value);

        return $material ? static::formatMaterialLabel($material) : null;
    }

    protected static function formatProcessoLabel(?Processo $processo): string
    {
        if (! $processo) {
            return 'Sem processo vinculado';
        }

        if ($processo->no_processo_sei && $processo->num_processo) {
            return "{$processo->no_processo_sei} | {$processo->num_processo}";
        }

        return $processo->no_processo_sei ?: ($processo->num_processo ?: "Processo #{$processo->id}");
    }

    protected static function formatMaterialLabel(DescricaoDetalhada $material): string
    {
        return Str::limit(trim((string) $material->descricao_detalhada), 100);
    }

    protected static function buildFaixaStatus($qtdeMin, $qtdeMax, $saldoAtual): string
    {
        $min = static::normalizeDecimal($qtdeMin);
        $max = static::normalizeDecimal($qtdeMax);
        $saldo = static::normalizeDecimal($saldoAtual);

        if ($saldo === null) {
            return 'Informe o saldo atual para acompanhar a faixa de controle.';
        }

        if ($min !== null && $saldo < $min) {
            return 'Saldo abaixo da quantidade minima.';
        }

        if ($max !== null && $saldo > $max) {
            return 'Saldo acima da quantidade maxima.';
        }

        if ($min !== null || $max !== null) {
            return 'Saldo dentro da faixa configurada.';
        }

        return 'Faixa de controle ainda nao configurada.';
    }

    protected static function buildSaldoResumo(ProMaterial $record): ?string
    {
        $min = $record->qtde_min;
        $max = $record->qtde_max;

        if ($min === null && $max === null) {
            return null;
        }

        return 'Faixa: '
            . ($min !== null ? number_format($min, thousands_separator: '.') : '-')
            . ' ate '
            . ($max !== null ? number_format($max, thousands_separator: '.') : '-');
    }

    protected static function resolveSaldoColor(ProMaterial $record): string
    {
        $min = static::normalizeDecimal($record->qtde_min);
        $max = static::normalizeDecimal($record->qtde_max);
        $saldo = static::normalizeDecimal($record->saldo_atual);

        if ($saldo === null) {
            return 'gray';
        }

        if ($min !== null && $saldo < $min) {
            return 'danger';
        }

        if ($max !== null && $saldo > $max) {
            return 'warning';
        }

        if ($min !== null || $max !== null) {
            return 'success';
        }

        return 'gray';
    }

    protected static function normalizeDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(',', '.', (string) $value);
    }
}
