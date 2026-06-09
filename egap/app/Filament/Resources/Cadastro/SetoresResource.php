<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\SetoresResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Cadastro\Setores;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SetoresResource extends Resource
{
    protected static ?string $model = Setores::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Setores';
    protected static ?string $modelLabel = 'Setor';
    protected static ?string $pluralModelLabel = 'Setores';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identificação')
                    ->description('Dados principais usados para identificar e organizar o setor.')
                    ->schema([
                        Grid::make(12)->schema([
                            Select::make('unidade_organizacional_id')
                                ->label('Unidade Organizacional')
                                ->searchable()
                                ->required()
                                ->afterStateHydrated(function (Select $component, ?Setores $record): void {
                                    if ($record?->CodigoPai !== null) {
                                        $component->state($record->CodigoPai);
                                    }
                                })
                                ->options(fn () => Setores::query()
                                    ->unidadesOrganizacionais()
                                    ->orderBy('UnidadeOrganizacional')
                                    ->pluck('UnidadeOrganizacional', 'id')
                                    ->toArray())
                                ->columnSpanFull(),

                            TextInput::make('Setor')
                                ->label('Setor')
                                ->required()
                                ->placeholder('Informe o nome do Setor')
                                ->maxLength(255)
                                ->columnSpan(6),

                            TextInput::make('SetorDescricao')
                                ->label('Descrição do Setor')
                                ->maxLength(255)
                                ->columnSpan(6),
                        ]),
                    ]),

                Section::make('Códigos e Controle')
                    ->description('Campos numéricos de integração, ordenação e classificação.')
                    ->schema([
                        Grid::make(12)->schema([
                            TextInput::make('cd_orgao')
                                ->label('Código do Órgão')
                                ->integer()
                                ->minValue(0)
                                ->columnSpan(4),

                            TextInput::make('ordem')
                                ->label('Ordem')
                                ->integer()
                                ->minValue(0)
                                ->columnSpan(4),

                            Select::make('centrocusto')
                                ->label('Centro de Custo')
                                ->relationship('centroCustoRef', 'descricao')
                                ->columnSpan(4),

                            TextInput::make('cns')
                                ->label('CNS')
                                ->maxLength(255)
                                ->columnSpan(4),

                            TextInput::make('email')
                                ->label('E-mail')
                                ->email()
                                ->maxLength(255)
                                ->columnSpan(8),
                        ]),
                    ])
                    ->collapsible(),

                Section::make('Localização e Complementos')
                    ->schema([
                        Grid::make(12)->schema([
                            Radio::make('funcao')
                                ->columnSpan(6)
                                ->label('Função')
                                ->live()
                                ->afterStateHydrated(function (Radio $component, ?Setores $record, Set $set): void {
                                    if (! $record) {
                                        return;
                                    }

                                    $funcao = static::resolveFuncaoFromFields($record->comarca, $record->vara);

                                    $component->state($funcao);
                                    static::setFuncaoFields($funcao, $set);
                                })
                                ->afterStateUpdated(fn (?string $state, Set $set): mixed => static::setFuncaoFields($state, $set))
                                ->inline()
                                ->inlineLabel(false)
                                ->options([
                                    'V' => 'Vara',
                                    'C' => 'Comarca',
                                    'all' => 'Ambas'
                                ]),
                            Toggle::make('presidencia')
                                ->label('Presidência')
                                ->inline(false)
                                ->onIcon('heroicon-m-user')
                                ->offIcon('heroicon-m-user')
                                ->columnSpan(6),
                            Hidden::make('comarca'),
                            Hidden::make('vara'),
                        ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function normalizeUnidadeOrganizacionalData(array $data): array
    {
        $data = static::normalizeFuncaoData($data);
        $data = static::normalizePresidenciaData($data);
        $unidadeOrganizacionalId = $data['unidade_organizacional_id'] ?? null;

        unset($data['unidade_organizacional_id']);

        if (blank($unidadeOrganizacionalId)) {
            return $data;
        }

        $unidadeOrganizacional = Setores::query()
            ->unidadesOrganizacionais()
            ->find($unidadeOrganizacionalId);

        if (! $unidadeOrganizacional) {
            return $data;
        }

        $data['CodigoPai'] = $unidadeOrganizacional->id;
        $data['CodigodaUO'] = $unidadeOrganizacional->id;
        $data['UnidadeOrganizacional'] = $unidadeOrganizacional->UnidadeOrganizacional;

        return $data;
    }

    public static function normalizePresidenciaData(array $data): array
    {
        $data['presidencia'] = (int) (bool) ($data['presidencia'] ?? false);

        return $data;
    }

    public static function normalizeFuncaoData(array $data): array
    {
        $funcao = $data['funcao'] ?? null;

        unset($data['funcao']);

        [$comarca, $vara] = static::resolveFuncaoFields($funcao);

        $data['comarca'] = $comarca;
        $data['vara'] = $vara;

        return $data;
    }

    private static function setFuncaoFields(?string $funcao, Set $set): null
    {
        [$comarca, $vara] = static::resolveFuncaoFields($funcao);

        $set('comarca', $comarca);
        $set('vara', $vara);

        return null;
    }

    private static function resolveFuncaoFields(?string $funcao): array
    {
        return match ($funcao) {
            'V' => [null, 'V'],
            'C' => ['C', null],
            'all' => ['C', 'V'],
            default => [null, null],
        };
    }

    private static function resolveFuncaoFromFields(?string $comarca, ?string $vara): ?string
    {
        return match (true) {
            $comarca === 'C' && $vara === 'V' => 'all',
            $vara === 'V' => 'V',
            $comarca === 'C' => 'C',
            default => null,
        };
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('id', '#', isFirstColumn: true),
                TableColumns::text('CodigoPai', 'Código Pai'),
                TableColumns::text('Setor', 'Setor')
                    ->wrap(),
                TableColumns::text('SetorDescricao', 'Descrição')
                    ->wrap(),
                TableColumns::dateTime('date_time', 'Atualizado em'),
                TableColumns::text('atualizado_por.name', 'Atualizado por'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unidade_organizacional')
                    ->label('Unidade Organizacional')
                    ->attribute('CodigoPai')
                    ->columnSpan(3)
                    ->searchable()
                    ->preload()
                    ->options(fn (): array => Setores::query()
                        ->unidadesOrganizacionais()
                        ->orderBy('UnidadeOrganizacional')
                        ->pluck('UnidadeOrganizacional', 'id')
                        ->toArray()),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('UnidadeOrganizacional');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSetores::route('/'),
            'create' => Pages\CreateSetores::route('/create'),
            'edit' => Pages\EditSetores::route('/{record}/edit'),
        ];
    }
}
