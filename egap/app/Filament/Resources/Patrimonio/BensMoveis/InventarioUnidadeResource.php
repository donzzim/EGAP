<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Resources\Patrimonio\BensMoveis\InventarioUnidadeResource\Pages;
use App\Filament\Clusters\PatrimonioCluster;
use App\Models\Patrimonio\BensMoveis\InventarioUnidade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Repeater, Tabs, Grid};
use Filament\Pages\SubNavigationPosition;
use Carbon\Carbon;

class InventarioUnidadeResource extends Resource
{
    protected static ?string $model = InventarioUnidade::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $slug = 'inventario-unidades';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Unidades Inventariadas';

    protected static ?string $pluralModelLabel = 'Unidades Inventariadas';

    protected static ?int $navigationSort = 11;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Unidades Inventariadas')
                            ->icon('heroicon-m-building-office')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('id_inventario')
                                        ->label('Inventário No')
                                        ->relationship('inventario', 'num_inventario')
                                        ->required()
                                        ->columnSpanFull(),

                                    Select::make('unidades')
                                        ->label('Unidades')
                                        ->relationship('unidade', 'Setor')
                                        ->searchable()
                                        ->required()
                                        ->columnSpanFull(),

                                    DatePicker::make('data_inicio')
                                        ->label('Data Início')
                                        ->live()
                                        ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => self::calcularDias($get, $set)),

                                    DatePicker::make('data_termino')
                                        ->label('Data Término')
                                        ->live()
                                        ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => self::calcularDias($get, $set)),

                                    TextInput::make('dias')
                                        ->label('Dias')
                                        ->numeric()
                                        ->readOnly()
                                        ->placeholder('Calculado automaticamente'),

                                    Select::make('situacao')
                                        ->label('Situação')
                                        ->options([
                                            'A inventariar' => 'A inventariar',
                                            'Em andamento' => 'Em andamento',
                                            'Concluído' => 'Concluído',
                                            'carga efetuada' => 'carga efetuada',
                                        ])->default('A inventariar'),
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
                                                ->required(),

                                            Select::make('funcao')
                                                ->label('Função')
                                                ->options(['Líder' => 'Líder', 'Membro' => 'Membro'])
                                                ->required(),

                                            Select::make('integrante')
                                                ->label('Integrante')
                                                ->relationship('membroRef', 'name')
                                                ->searchable()
                                                ->required(),
                                        ]),
                                    ])
                                    ->createItemButtonLabel('Adicionar Integrante'),
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
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('unidade.Setor')
                    ->label('Unidade')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Data Início')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('data_termino')
                    ->label('Data Término')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('dias')
                    ->label('Dias'),

                Tables\Columns\TextColumn::make('situacao')
                    ->label('Situação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Concluído', 'carga efetuada' => 'success',
                        'Em andamento' => 'info',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('equipes.membroRef.name')
                    ->label('Integrantes')
                    ->listWithLineBreaks()
                    ->bulleted(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()->label('Gerenciar'),
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
