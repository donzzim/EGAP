<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\InventarioResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
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
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventarioResource extends Resource
{
    protected static ?string $model = Inventario::class;

    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Gestão do Inventário';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $pluralModelLabel = 'Gestão do Inventário';

    protected static ?string $modelLabel = 'Inventário';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'bens-moveis/inventarios';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Abas do Inventário')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tabs\Tab::make('Gestão do Inventário')
                            ->icon('heroicon-m-adjustments-horizontal')
                            ->schema([
                                Section::make('Dados do Inventário')
                                    ->description('Defina o período e acompanhe a situação do inventário.')
                                    ->icon('heroicon-o-calendar-days')
                                    ->schema([
                                        Grid::make(4)->schema([
                                            TextInput::make('num_inventario')->label('Inventário Nº')->required(),
                                            TextInput::make('ano_inventario')->label('Ano')->numeric()->default(date('Y')),
                                            DatePicker::make('inicio_inventario')->label('Início')->displayFormat('d/m/Y')->native(false)->live(),
                                            DatePicker::make('termino_inventario')->label('Término')->displayFormat('d/m/Y')->native(false)->live()
                                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                                    $inicio = $get('inicio_inventario');
                                                    if ($inicio && $state) {
                                                        $dias = Carbon::parse($inicio)->diffInDays(Carbon::parse($state));
                                                        $set('dias', $dias);
                                                    }
                                                }),
                                            TextInput::make('dias')->label('Dias')->numeric()->suffix('dias')->readOnly(),
                                            Select::make('situacao')->label('Situação')
                                                ->options(['A inventariar' => 'A inventariar', 'Em andamento' => 'Em andamento', 'Finalizado' => 'Finalizado'])
                                                ->native(false)
                                                ->default('A inventariar'),
                                        ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Comissões')
                            ->icon('heroicon-m-users')
                            ->schema([
                                Repeater::make('comissoes')
                                    ->relationship('comissoes')
                                    ->schema([
                                        Select::make('comissao')->label('Comissão')->options(['Permanente' => 'Permanente', 'Especial' => 'Especial'])->native(false)->required(),
                                        Select::make('funcao')->label('Função')->options(['Presidente' => 'Presidente', 'Membro' => 'Membro', 'Secretário' => 'Secretário'])->native(false)->required(),
                                        Select::make('nome')->label('Membro')->relationship('membroRef', 'name')->searchable()->preload()->native(false)->required(),
                                    ])->columns(3)->addActionLabel('Adicionar Membro à Comissão'),
                            ]),

                        Tabs\Tab::make('Materiais Inventariados')
                            ->icon('heroicon-m-archive-box')
                            ->schema([
                                Repeater::make('itens')
                                    ->relationship('itens')
                                    ->schema([
                                        Select::make('id_bem')
                                            ->label('Patrimônio')
                                            ->relationship('bem', 'NumPatrimonio')
                                            ->placeholder('Busque pelo patrimônio')
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required()
                                            ->columnSpan(2),
                                        Select::make('estado_conservacao')->label('Estado')->options(['BOM' => 'BOM', 'REGULAR' => 'REGULAR', 'RUIM' => 'RUIM', 'SUCATA' => 'SUCATA'])->native(false),
                                        TextInput::make('observacao')->label('Observação'),
                                    ])->columns(4)->addActionLabel('Vincular Patrimônio'),
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
                    ->color(fn (string $state): string => match ($state) {
                        'A inventariar' => 'warning',
                        'Em andamento' => 'info',
                        'Finalizado' => 'success',
                        default => 'gray',
                    }),

                TableColumns::text('itens_count', 'Materiais')
                    ->counts('itens')
                    ->searchable(false)
                    ->icon('heroicon-m-archive-box')
                    ->iconColor('primary')
                    ->badge()
                    ->color('gray'),
                TableColumns::text('comissoes_count', 'Comissões')
                    ->counts('comissoes')
                    ->searchable(false)
                    ->icon('heroicon-m-users')
                    ->iconColor('success')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('situacao')
                    ->label('Situação')
                    ->options(['A inventariar' => 'A inventariar', 'Em andamento' => 'Em andamento', 'Finalizado' => 'Finalizado']),
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
            'index' => Pages\ListInventarios::route('/'),
            'create' => Pages\CreateInventario::route('/create'),
            'edit' => Pages\EditInventario::route('/{record}/edit'),
        ];
    }
}
