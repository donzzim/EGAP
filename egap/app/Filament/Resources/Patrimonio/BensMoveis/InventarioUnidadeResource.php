<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\InventarioUnidadeResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\InventarioUnidade;
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
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventarioUnidadeResource extends Resource
{
    protected static ?string $model = InventarioUnidade::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $slug = 'bens-moveis/unidades-inventariadas';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Unidades Inventariadas';

    protected static ?string $pluralModelLabel = 'Unidades Inventariadas';

    protected static ?string $modelLabel = 'Unidade Inventariada';

    protected static ?int $navigationSort = 11;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tabs\Tab::make('Unidades Inventariadas')
                            ->icon('heroicon-m-building-office')
                            ->schema([
                                Section::make('Planejamento da Unidade')
                                    ->description('Vincule a unidade ao inventário e informe o período de execução.')
                                    ->icon('heroicon-o-building-office')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('id_inventario')
                                                ->label('Inventário No')
                                                ->relationship('inventario', 'num_inventario')
                                                ->placeholder('Selecione o inventário')
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->required()
                                                ->columnSpanFull(),

                                            Select::make('unidades')
                                                ->label('Unidades')
                                                ->relationship('unidade', 'Setor')
                                                ->placeholder('Selecione a unidade')
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->required()
                                                ->columnSpanFull(),

                                            DatePicker::make('data_inicio')
                                                ->label('Data Início')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->live()
                                                ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => self::calcularDias($get, $set)),

                                            DatePicker::make('data_termino')
                                                ->label('Data Término')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->live()
                                                ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => self::calcularDias($get, $set)),

                                            TextInput::make('dias')
                                                ->label('Dias')
                                                ->numeric()
                                                ->suffix('dias')
                                                ->readOnly()
                                                ->placeholder('Calculado automaticamente'),

                                            Select::make('situacao')
                                                ->label('Situação')
                                                ->options([
                                                    'A inventariar' => 'A inventariar',
                                                    'Em andamento' => 'Em andamento',
                                                    'Concluído' => 'Concluído',
                                                    'carga efetuada' => 'carga efetuada',
                                                ])->native(false)->default('A inventariar'),
                                        ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Equipes de Campo')
                            ->icon('heroicon-m-users')
                            ->schema([
                                Repeater::make('equipes')
                                    ->relationship('equipes')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('id_inventario')
                                                ->label('Inventário No')
                                                ->relationship('unidadeInventariada.inventario', 'num_inventario')
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->required(),

                                            Select::make('funcao')
                                                ->label('Função')
                                                ->options(['Líder' => 'Líder', 'Membro' => 'Membro'])
                                                ->native(false)
                                                ->required(),

                                            Select::make('integrante')
                                                ->label('Integrante')
                                                ->relationship('membroRef', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->required(),
                                        ]),
                                    ])
                                    ->addActionLabel('Adicionar Integrante'),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function calcularDias(Forms\Get $get, Forms\Set $set): void
    {
        $inicio = $get('data_inicio');
        $termino = $get('data_termino');

        if ($inicio && $termino) {
            $diff = Carbon::parse($inicio)->diffInDays(Carbon::parse($termino));
            $set('dias', $diff);
        }
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('unidade.Setor', 'Unidade', isFirstColumn: true)
                    ->icon('heroicon-o-building-office')
                    ->weight('medium')
                    ->wrap(),
                TableColumns::text('inventario.num_inventario', 'Inventário')->badge(),
                TableColumns::date('data_inicio', 'Data Início'),
                TableColumns::date('data_termino', 'Data Término'),
                TableColumns::text('dias', 'Dias')->suffix(' dias'),
                TableColumns::text('situacao', 'Situação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Concluído', 'carga efetuada' => 'success',
                        'Em andamento' => 'info',
                        default => 'warning',
                    }),

                TableColumns::text('equipes.membroRef.name', 'Integrantes')
                    ->listWithLineBreaks()
                    ->bulleted(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_inventario')
                    ->label('Inventário')
                    ->relationship('inventario', 'num_inventario')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('situacao')
                    ->label('Situação')
                    ->options(['A inventariar' => 'A inventariar', 'Em andamento' => 'Em andamento', 'Concluído' => 'Concluído', 'carga efetuada' => 'Carga efetuada']),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()->label('Gerenciar')->icon('heroicon-o-cog-6-tooth'),
                Tables\Actions\ViewAction::make()->tooltip('Visualizar')->hiddenLabel(),
                Tables\Actions\DeleteAction::make()->tooltip('Excluir')->hiddenLabel(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarioUnidades::route('/'),
            'create' => Pages\CreateInventarioUnidade::route('/create'),
            'edit' => Pages\EditInventarioUnidade::route('/{record}/edit'),
        ];
    }
}
