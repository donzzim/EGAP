<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\InventarioResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\Inventario;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;
use Throwable;

class InventarioResource extends Resource
{
    protected static ?string $model = Inventario::class;

    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Gestão do Inventário';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $pluralModelLabel = 'Gestão do Inventário';

    protected static ?string $modelLabel = 'Inventário';

    protected static ?int $navigationSort = 12;

    protected static ?string $slug = 'bens-moveis/inventarios';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Inventário')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tabs\Tab::make('Dados gerais')
                            ->icon('heroicon-m-adjustments-horizontal')
                            ->schema([
                                Section::make('Identificação e período')
                                    ->description('Defina a numeração, vigência e situação do inventário.')
                                    ->icon('heroicon-o-calendar-days')
                                    ->schema([
                                        Grid::make(5)->schema([
                                            TextInput::make('num_inventario')
                                                ->label('Inventário Nº')
                                                ->required()
                                                ->maxLength(50),

                                            TextInput::make('ano_inventario')
                                                ->label('Ano')
                                                ->numeric()
                                                ->minValue(2000)
                                                ->maxValue((int) date('Y') + 1)
                                                ->default(date('Y'))
                                                ->required(),

                                            DatePicker::make('inicio_inventario')
                                                ->label('Início')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->live()
                                                ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => self::calcularDiasInventario($get, $set)),

                                            DatePicker::make('termino_inventario')
                                                ->label('Término')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->live()
                                                ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => self::calcularDiasInventario($get, $set)),

                                            TextInput::make('dias')
                                                ->label('Dias')
                                                ->numeric()
                                                ->suffix('dias')
                                                ->readOnly(),

                                            Select::make('situacao')
                                                ->label('Situação')
                                                ->options(Inventario::situacoes())
                                                ->native(false)
                                                ->default(Inventario::SITUACAO_A_INVENTARIAR)
                                                ->required()
                                                ->columnSpanFull(),
                                        ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Unidades')
                            ->icon('heroicon-m-building-office')
                            ->schema([
                                Section::make('Unidades inventariadas')
                                    ->description('Vincule as unidades que compõem este inventário e acompanhe o período por unidade.')
                                    ->icon('heroicon-o-building-office')
                                    ->schema([
                                        Repeater::make('unidadesInventariadas')
                                            ->relationship('unidadesInventariadas')
                                            ->schema([
                                                Select::make('unidades')
                                                    ->label('Unidade')
                                                    ->relationship(
                                                        'unidade',
                                                        'UnidadeOrganizacional',
                                                        modifyQueryUsing: fn ($query) => $query
                                                            ->unidadesRaiz()
                                                            ->orderBy('UnidadeOrganizacional')
                                                    )
                                                    ->getOptionLabelFromRecordUsing(fn ($record): string => $record->UnidadeOrganizacional ?: $record->Setor ?: "Unidade {$record->id}")
                                                    ->searchable()
                                                    ->preload()
                                                    ->native(false)
                                                    ->required()
                                                    ->columnSpan(2),

                                                DatePicker::make('data_inicio')
                                                    ->label('Início')
                                                    ->displayFormat('d/m/Y')
                                                    ->native(false)
                                                    ->live()
                                                    ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => self::calcularDiasUnidade($get, $set)),

                                                DatePicker::make('data_termino')
                                                    ->label('Término')
                                                    ->displayFormat('d/m/Y')
                                                    ->native(false)
                                                    ->live()
                                                    ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => self::calcularDiasUnidade($get, $set)),

                                                TextInput::make('dias')
                                                    ->label('Dias')
                                                    ->numeric()
                                                    ->suffix('dias')
                                                    ->readOnly(),

                                                Select::make('situacao')
                                                    ->label('Situação')
                                                    ->options(Inventario::situacoes())
                                                    ->native(false)
                                                    ->default(Inventario::SITUACAO_A_INVENTARIAR)
                                                    ->required(),
                                            ])
                                            ->columns([
                                                'default' => 1,
                                                'lg' => 6,
                                            ])
                                            ->defaultItems(0)
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): string => filled($state['unidades'] ?? null)
                                                ? "Unidade {$state['unidades']}"
                                                : 'Unidade inventariada')
                                            ->addActionLabel('Adicionar unidade'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Comissões')
                            ->icon('heroicon-m-users')
                            ->schema([
                                Section::make('Comissão do inventário')
                                    ->description('Informe os membros responsáveis pelo inventário.')
                                    ->icon('heroicon-o-users')
                                    ->schema([
                                        Repeater::make('comissoes')
                                            ->relationship('comissoes')
                                            ->schema([
                                                Select::make('comissao')
                                                    ->label('Comissão')
                                                    ->options([
                                                        'Permanente' => 'Permanente',
                                                        'Especial' => 'Especial',
                                                    ])
                                                    ->native(false)
                                                    ->required(),

                                                Select::make('funcao')
                                                    ->label('Função')
                                                    ->options([
                                                        'Presidente' => 'Presidente',
                                                        'Membro' => 'Membro',
                                                        'Secretário' => 'Secretário',
                                                    ])
                                                    ->native(false)
                                                    ->required(),

                                                Select::make('nome')
                                                    ->label('Membro')
                                                    ->relationship('membroRef', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->native(false)
                                                    ->required(),
                                            ])
                                            ->columns([
                                                'default' => 1,
                                                'lg' => 3,
                                            ])
                                            ->defaultItems(0)
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): string => collect([
                                                $state['comissao'] ?? null,
                                                $state['funcao'] ?? null,
                                            ])->filter()->join(' - ') ?: 'Membro da comissão')
                                            ->addActionLabel('Adicionar membro'),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('num_inventario', 'Inventário Nº', isFirstColumn: true)
                    ->badge()
                    ->weight('medium'),
                TableColumns::text('ano_inventario', 'Ano'),
                TableColumns::date('inicio_inventario', 'Início'),
                TableColumns::date('termino_inventario', 'Término'),
                TableColumns::text('dias', 'Dias')->suffix(' dias'),
                TableColumns::text('situacao', 'Situação')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Inventario::rotuloSituacao($state))
                    ->color(fn (?string $state): string => Inventario::corSituacao($state)),

                TableColumns::text('unidades_inventariadas_count', 'Unidades')
                    ->counts('unidadesInventariadas')
                    ->searchable(false)
                    ->sortable(false)
                    ->icon('heroicon-m-building-office')
                    ->iconColor('primary')
                    ->badge()
                    ->color('gray')
                    ->tooltip('Visualizar unidades')
                    ->action(self::unidadesInventariadasTableAction()),
                TableColumns::text('comissoes_count', 'Comissões')
                    ->counts('comissoes')
                    ->searchable(false)
                    ->sortable(false)
                    ->icon('heroicon-m-users')
                    ->iconColor('success')
                    ->badge()
                    ->color('gray')
                    ->tooltip('Visualizar comissões')
                    ->action(self::comissoesTableAction()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('situacao')
                    ->columnSpan(2)
                    ->label('Situação')
                    ->options(Inventario::situacoes()),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('id', 'desc')
            ->actions([
                ...TableDefaults::actions(),
                self::atualizarSituacaoTableAction()
            ]);
    }

    private static function atualizarSituacaoTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('atualizar_situacao')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray')
                ->requiresConfirmation()
                ->hiddenLabel()
                ->modalHeading('Atualizar situação do inventário')
                ->modalDescription('Os bens vinculados aos setores/unidades deste inventário serão marcados como A INVENTARIAR.')
                ->action(function (Inventario $record): void {
                    try {
                        $totalAtualizado = self::atualizarSituacaoBensInventario($record);
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Não foi possível atualizar a situação.')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $notification = Notification::make()
                        ->title('Situação atualizada.')
                        ->body("{$totalAtualizado} bem(ns) marcado(s) como A INVENTARIAR.");

                    $totalAtualizado > 0
                        ? $notification->success()
                        : $notification->warning();

                    $notification->send();
                })
                ->tooltip('Atualizar situação');
    }

    private static function unidadesInventariadasTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('visualizar_unidades_inventario')
            ->modalHeading(fn (Inventario $record): string => "Unidades do Inventário {$record->num_inventario}/{$record->ano_inventario}")
            ->modalWidth('full')
            ->extraModalWindowAttributes([
                'class' => 'egap-modal-window',
                'style' => 'width: calc(100vw - 2rem); max-width: 96rem; height: min(82dvh, 860px); overflow: hidden;',
            ])
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (Inventario $record): HtmlString => new HtmlString(
                Livewire::mount(
                    'patrimonio.inventario-unidades-modal',
                    ['inventarioId' => (int) $record->getKey()],
                    "inventario-unidades-{$record->getKey()}",
                )
            ));
    }

    private static function comissoesTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('visualizar_comissoes_inventario')
            ->modalHeading(fn (Inventario $record): string => "Comissões do Inventário {$record->num_inventario}/{$record->ano_inventario}")
            ->modalWidth('full')
            ->extraModalWindowAttributes([
                'class' => 'egap-modal-window',
                'style' => 'width: calc(100vw - 2rem); max-width: 96rem; height: min(82dvh, 860px); overflow: hidden;',
            ])
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (Inventario $record): HtmlString => new HtmlString(
                Livewire::mount(
                    'patrimonio.inventario-comissoes-modal',
                    ['inventarioId' => (int) $record->getKey()],
                    "inventario-comissoes-{$record->getKey()}",
                )
            ));
    }

    private static function atualizarSituacaoBensInventario(Inventario $inventario): int
    {
        $unidades = $inventario->unidadesInventariadas()
            ->pluck('unidades')
            ->filter()
            ->unique()
            ->values();

        if ($unidades->isEmpty()) {
            return 0;
        }

        return BemMovel::query()
            ->where(function ($query) use ($unidades): void {
                $query
                    ->whereIn('Setor', $unidades)
                    ->orWhereIn('UnidadeJudiciaria', $unidades);
            })
            ->update([
                'sit_inventario' => 'A INVENTARIAR',
                'id_inventario' => $inventario->getKey(),
            ]);
    }

    private static function calcularDiasInventario(Forms\Get $get, Forms\Set $set): void
    {
        self::calcularDias($get('inicio_inventario'), $get('termino_inventario'), $set);
    }

    private static function calcularDiasUnidade(Forms\Get $get, Forms\Set $set): void
    {
        self::calcularDias($get('data_inicio'), $get('data_termino'), $set);
    }

    private static function calcularDias(mixed $inicio, mixed $termino, Forms\Set $set): void
    {
        if (! $inicio || ! $termino) {
            $set('dias', null);

            return;
        }

        $set('dias', Carbon::parse($inicio)->diffInDays(Carbon::parse($termino)));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarios::route('/'),
            'create' => Pages\CreateInventario::route('/create'),
            'edit' => Pages\EditInventario::route('/{record}/edit'),
        ];
    }
}
