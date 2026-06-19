<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\AtividadeInventarioResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Cadastro\Setores;
use App\Models\Patrimonio\BensMoveis\AtividadeInventario;
use App\Models\Patrimonio\BensMoveis\Inventario;
use App\Models\UserEgap;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AtividadeInventarioResource extends Resource
{
    protected static ?string $model = AtividadeInventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $slug = 'bens-moveis/atividades-inventario';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Atividades do Inventário';

    protected static ?string $pluralModelLabel = 'Atividades do Inventário';

    protected static ?string $modelLabel = 'Atividade';

    protected static ?int $navigationSort = 14;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Atividade do Inventário')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tabs\Tab::make('Dados da Atividade')
                            ->icon('heroicon-m-clipboard-document-list')
                            ->schema([
                                Section::make('Inventário e Localização')
                                    ->description('Vincule a atividade ao inventário e informe a unidade, setor e complemento.')
                                    ->icon('heroicon-o-map-pin')
                                    ->schema([
                                        Grid::make(12)->schema([
                                            Select::make('id_inventario')
                                                ->label('Inventário No')
                                                ->options(fn (): array => Inventario::query()
                                                    ->selectRaw("id, CONCAT(num_inventario, '/', ano_inventario) as inventario")
                                                    ->orderByDesc('ano_inventario')
                                                    ->orderByDesc('num_inventario')
                                                    ->pluck('inventario', 'id')
                                                    ->toArray())
                                                ->placeholder('Selecione o inventário')
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->required()
                                                ->columnSpanFull(),

                                            Select::make('id_unidade')
                                                ->label('Unidade Judiciária')
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->live()
                                                ->options(fn () => Setores::query()
                                                    ->whereColumn('id', 'CodigodaUO')
                                                    ->orderBy('UnidadeOrganizacional')
                                                    ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                                    ->toArray()
                                                )
                                                ->afterStateUpdated(fn (Set $set) => $set('setor', null))
                                                ->columnSpan(4),

                                            Select::make('setor')
                                                ->label('Setor')
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->options(fn (Get $get) => Setores::query()
                                                    ->when(
                                                        $get('id_unidade'),
                                                        fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                                                    )
                                                    ->orderBy('Setor')
                                                    ->pluck('Setor', 'id')
                                                    ->toArray()
                                                )
                                                ->disabled(fn (Get $get) => blank($get('id_unidade')))
                                                ->columnSpan(4),

                                            Select::make('complemento')
                                                ->label('Complemento')
                                                ->relationship('complementoRef', 'descricao')
                                                ->placeholder('Selecione o complemento')
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->columnSpan(4),
                                        ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Execução')
                            ->icon('heroicon-m-calendar-days')
                            ->schema([
                                Section::make('Período e Situação')
                                    ->description('Registre o período de trabalho, a dupla responsável e o andamento.')
                                    ->icon('heroicon-o-clock')
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 12,
                                        ])->schema([
                                            DatePicker::make('inicio')
                                                ->label('Início')
                                                ->required()
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->columnSpan([
                                                    'default' => 'full',
                                                    'md' => 1,
                                                    'xl' => 3,
                                                ]),

                                            DatePicker::make('termino')
                                                ->label('Término')
                                                ->required()
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->columnSpan([
                                                    'default' => 'full',
                                                    'md' => 1,
                                                    'xl' => 3,
                                                ]),

                                            TextInput::make('situacao')
                                                ->label('Situação')
                                                ->columnSpan([
                                                    'default' => 'full',
                                                    'md' => 1,
                                                    'xl' => 3,
                                                ]),

                                            TextInput::make('qtde_inventariada')
                                                ->label('Qtde Inventariada')
                                                ->numeric()
                                                ->minValue(0)
                                                ->placeholder('0')
                                                ->columnSpan([
                                                    'default' => 'full',
                                                    'md' => 1,
                                                    'xl' => 3,
                                                ]),

                                            Hidden::make('dupla'),

                                            Select::make('dupla_integrante_1')
                                                ->label('Integrante 1')
                                                ->placeholder('Selecione o primeiro integrante')
                                                ->options(fn (): array => self::opcoesIntegrantesDupla())
                                                ->searchable()

                                                ->required()
                                                ->preload()
                                                ->native(false)
                                                ->live()
                                                ->dehydrated(false)
                                                ->afterStateHydrated(function (Select $component, ?AtividadeInventario $record): void {
                                                    $component->state(self::integranteDaDupla($record?->dupla, 0));
                                                })
                                                ->afterStateUpdated(fn (Get $get, Set $set) => self::atualizarDupla($get, $set))
                                                ->columnSpan([
                                                    'default' => 'full',
                                                    'md' => 1,
                                                    'xl' => 6,
                                                ]),

                                            Select::make('dupla_integrante_2')
                                                ->label('Integrante 2')
                                                ->placeholder('Selecione o segundo integrante, se houver')
                                                ->options(fn (): array => self::opcoesIntegrantesDupla())
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->live()
                                                ->dehydrated(false)
                                                ->afterStateHydrated(function (Select $component, ?AtividadeInventario $record): void {
                                                    $component->state(self::integranteDaDupla($record?->dupla, 1));
                                                })
                                                ->afterStateUpdated(fn (Get $get, Set $set) => self::atualizarDupla($get, $set))
                                                ->columnSpan([
                                                    'default' => 'full',
                                                    'md' => 1,
                                                    'xl' => 6,
                                                ]),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function opcoesIntegrantesDupla(): array
    {
        return UserEgap::query()
            ->orderBy('name')
            ->pluck('name', 'name')
            ->toArray();
    }

    private static function integranteDaDupla(?string $dupla, int $index): ?string
    {
        $integrantes = array_map('trim', explode(',', (string) $dupla, 2));

        return filled($integrantes[$index] ?? null) ? $integrantes[$index] : null;
    }

    private static function atualizarDupla(Get $get, Set $set): void
    {
        $integrantes = array_filter([
            trim((string) $get('dupla_integrante_1')),
            trim((string) $get('dupla_integrante_2')),
        ]);

        $set('dupla', implode(',', $integrantes));
    }

    private static function formatarDuplaTabela(?string $dupla): string
    {
        if (blank($dupla)) {
            return '-';
        }

        $integrantes = array_values(array_filter(
            array_map('trim', explode(',', $dupla)),
            fn (string $integrante): bool => filled($integrante)
        ));

        if (count($integrantes) < 2) {
            return e(trim($dupla));
        }

        return implode('<br>', array_map(e(...), $integrantes));
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('inventario.num_inventario', 'Inventário No', isFirstColumn: true)
                    ->formatStateUsing(fn (AtividadeInventario $record): string => $record->inventario
                        ? "{$record->inventario->num_inventario}/{$record->inventario->ano_inventario}"
                        : '-')
                    ->badge(),
                TableColumns::text('unidadeRel.Setor', 'Unidade')
                    ->wrap(),
                TableColumns::text('setorRel.Setor', 'Setor')
                    ->wrap(),
                TableColumns::text('complemento', 'Complemento'),
                TableColumns::date('inicio', 'Início'),
                TableColumns::date('termino', 'Término'),
                TableColumns::text('dupla', 'Dupla')
                    ->formatStateUsing(fn (?string $state): string => self::formatarDuplaTabela($state))
                    ->html()
                    ->wrap(),
                TableColumns::text('situacao', 'Situação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Finalizado', 'carga efetuada' => 'success',
                        'Em andamento', 'Aberto' => 'info',
                        default => 'warning',
                    }),
                TableColumns::text('qtde_inventariada', 'Qtd.')
                    ->numeric()
                    ->badge()
                    ->color('gray'),
            ])
            ->defaultSort('id', 'desc')
            ->recordTitleAttribute('dupla');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAtividadeInventarios::route('/'),
            'create' => Pages\CreateAtividadeInventario::route('/create'),
            'edit' => Pages\EditAtividadeInventario::route('/{record}/edit'),
        ];
    }
}
