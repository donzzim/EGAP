<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\AtividadeInventarioResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Cadastro\Setores;
use App\Models\Patrimonio\BensMoveis\AtividadeInventario;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AtividadeInventarioResource extends Resource
{
    protected static ?string $model = AtividadeInventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    /** ✅ Vincula ao Cluster para aparecer nas abas superiores */
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
                Section::make('Atividades do Inventário')
                    ->description('Registro de progresso por unidade e setor conforme o sistema legado.')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('id_inventario')
                                ->label('Inventário No')
                                ->relationship('inventario', 'num_inventario')
                                ->placeholder('Selecione o inventário')
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->required(),

                            Select::make('id_unidade')
                                ->label('Unidade')
                                ->relationship('unidadeRel', 'Setor')
                                ->placeholder('Selecione a unidade')
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->required()
                                ->live(),

                            Select::make('setor')
                                ->label('Setor')
                                ->options(fn (Forms\Get $get) => Setores::where('CodigoPai', $get('id_unidade'))
                                    ->pluck('Setor', 'id')
                                )
                                ->placeholder('Selecione o setor')
                                ->searchable()
                                ->native(false),

                            TextInput::make('complemento')
                                ->label('Complemento')
                                ->placeholder('Informe o complemento'),

                            DatePicker::make('inicio')
                                ->label('Início')
                                ->displayFormat('d/m/Y')
                                ->native(false),
                            DatePicker::make('termino')
                                ->label('Término')
                                ->displayFormat('d/m/Y')
                                ->native(false),

                            TextInput::make('dupla')
                                ->label('Dupla')
                                ->placeholder('Digite os nomes dos integrantes'),

                            Select::make('situacao')
                                ->label('Situação')
                                ->options([
                                    'Aberto' => 'Aberto',
                                    'Em andamento' => 'Em andamento',
                                    'Finalizado' => 'Finalizado',
                                    'carga efetuada' => 'carga efetuada',
                                ])
                                ->native(false)
                                ->default('Aberto'),

                            TextInput::make('qtde_inventariada')
                                ->label('Qtde Inventariada')
                                ->numeric()
                                ->minValue(0)
                                ->placeholder('0'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('inventario.num_inventario', 'Inventário No', isFirstColumn: true)
                    ->badge(),
                TableColumns::text('unidadeRel.Setor', 'Unidade')
                    ->wrap(),
                TableColumns::text('setorRel.Setor', 'Setor')
                    ->wrap(),
                TableColumns::date('inicio', 'Início'),
                TableColumns::date('termino', 'Término')
                    ->toggleable(isToggledHiddenByDefault: true),
                TableColumns::text('dupla', 'Dupla')
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
                TableColumns::text('complemento', 'Complemento')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_inventario')
                    ->label('Inventário')
                    ->relationship('inventario', 'num_inventario')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('situacao')
                    ->label('Situação')
                    ->options([
                        'Aberto' => 'Aberto',
                        'Em andamento' => 'Em andamento',
                        'Finalizado' => 'Finalizado',
                        'carga efetuada' => 'Carga efetuada',
                    ]),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
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
