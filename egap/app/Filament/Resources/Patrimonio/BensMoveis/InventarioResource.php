<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Resources\Patrimonio\BensMoveis\InventarioResource\Pages;
use App\Filament\Clusters\PatrimonioCluster;
use App\Models\Patrimonio\BensMoveis\Inventario;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Tabs, Grid, Repeater};
use Carbon\Carbon;

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
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Abas do Inventário')
                    ->tabs([
                        Tabs\Tab::make('Gestão do Inventário')
                            ->icon('heroicon-m-adjustments-horizontal')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextInput::make('num_inventario')->label('Inventário Nº')->required(),
                                    TextInput::make('ano_inventario')->label('Ano')->numeric()->default(date('Y')),
                                    DatePicker::make('inicio_inventario')->label('Início')->live(),
                                    DatePicker::make('termino_inventario')->label('Término')->live()
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            $inicio = $get('inicio_inventario');
                                            if ($inicio && $state) {
                                                $dias = Carbon::parse($inicio)->diffInDays(Carbon::parse($state));
                                                $set('dias', $dias);
                                            }
                                        }),
                                    TextInput::make('dias')->label('Dias')->numeric()->readOnly(),
                                    Select::make('situacao')->label('Situação')
                                        ->options(['A inventariar' => 'A inventariar', 'Em andamento' => 'Em andamento', 'Finalizado' => 'Finalizado'])
                                        ->default('A inventariar'),
                                ]),
                            ]),

                        Tabs\Tab::make('Comissões')
                            ->icon('heroicon-m-users')
                            ->schema([
                                Repeater::make('comissoes')
                                    ->relationship('comissoes')
                                    ->schema([
                                        Select::make('comissao')->label('Comissão')->options(['Permanente' => 'Permanente', 'Especial' => 'Especial'])->required(),
                                        Select::make('funcao')->label('Função')->options(['Presidente' => 'Presidente', 'Membro' => 'Membro', 'Secretário' => 'Secretário'])->required(),
                                        Select::make('nome')->label('Membro')->relationship('membroRef', 'name')->searchable()->required(),
                                    ])->columns(3)->createItemButtonLabel('Adicionar Membro à Comissão')
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
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(2),
                                        Select::make('estado_conservacao')->label('Estado')->options(['BOM' => 'BOM', 'REGULAR' => 'REGULAR', 'RUIM' => 'RUIM', 'SUCATA' => 'SUCATA']),
                                        TextInput::make('observacao')->label('Observação'),
                                    ])->columns(4)->createItemButtonLabel('Vincular Patrimônio')
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('num_inventario')
                    ->label('Inventário Nº')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('ano_inventario')
                    ->label('Ano')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('inicio_inventario')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('termino_inventario')
                    ->label('Término')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dias')
                    ->label('Dias')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('situacao')
                    ->label('Situação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A inventariar' => 'warning',
                        'Em andamento' => 'info',
                        'Finalizado' => 'success',
                        default => 'gray',
                    }),

                /** ✅ CAMPOS SOLICITADOS: Unidades e Comissões (com contagem e ícones) */
                Tables\Columns\TextColumn::make('itens_count')
                    ->counts('itens')
                    ->label('Unidades')
                    ->icon('heroicon-m-archive-box')
                    ->iconColor('primary')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('comissoes_count')
                    ->counts('comissoes')
                    ->label('Comissões')
                    ->icon('heroicon-m-users')
                    ->iconColor('success')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()->label('Gerenciar'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
