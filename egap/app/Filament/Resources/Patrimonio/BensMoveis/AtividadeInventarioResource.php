<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Resources\Patrimonio\BensMoveis\AtividadeInventarioResource\Pages;
use App\Filament\Clusters\PatrimonioCluster;
use App\Models\Patrimonio\BensMoveis\AtividadeInventario;
use App\Models\Cadastro\Setores;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Grid, Section};
use Filament\Pages\SubNavigationPosition;

class AtividadeInventarioResource extends Resource
{
    protected static ?string $model = AtividadeInventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    /** ✅ Vincula ao Cluster para aparecer nas abas superiores */
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $slug = 'atividades-do-inventario';

    /** ✅ CORREÇÃO CRÍTICA:
     * Alterado de 'Patrimônio - Bens Móveis' para apenas 'Bens Móveis'.
     * Isso faz com que ele entre na mesma aba do BemMovelResource, sumindo com aquela aba extra.
     */
    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Atividades do Inventário';

    protected static ?string $pluralModelLabel = 'Atividades do Inventário';

    protected static ?string $modelLabel = 'Atividade';

    /** ✅ ORDEM: Aparece logo após a Administração e Incorporação */
    protected static ?int $navigationSort = 3;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Atividades do Inventário')
                    ->description('Registro de progresso por unidade e setor conforme o sistema legado.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('id_inventario')
                                ->label('Inventário No')
                                ->relationship('inventario', 'num_inventario')
                                ->required(),

                            Select::make('id_unidade')
                                ->label('Unidade')
                                ->relationship('unidadeRel', 'Setor')
                                ->searchable()
                                ->required()
                                ->live(),

                            Select::make('setor')
                                ->label('Setor')
                                ->options(fn (Forms\Get $get) =>
                                    Setores::where('CodigoPai', $get('id_unidade'))->pluck('Setor', 'id')
                                )
                                ->searchable(),

                            TextInput::make('complemento')
                                ->label('Complemento'),

                            DatePicker::make('inicio')->label('Início'),
                            DatePicker::make('termino')->label('Término'),

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
                                ])->default('Aberto'),

                            TextInput::make('qtde_inventariada')
                                ->label('Qtde Inventariada')
                                ->numeric(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('inventario.num_inventario')
                    ->label('Inventário No')
                    ->sortable(),

                Tables\Columns\TextColumn::make('id_unidade')
                    ->label('Unidade (Cód.)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unidadeRel.Setor')
                    ->label('Setor')
                    ->searchable(),

                Tables\Columns\TextColumn::make('complemento')
                    ->label('Complemento'),

                Tables\Columns\TextColumn::make('inicio')
                    ->label('Início')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('dupla')
                    ->label('Dupla')
                    ->wrap(),

                Tables\Columns\TextColumn::make('situacao')
                    ->label('Situação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Finalizado', 'carga efetuada' => 'success',
                        'Em andamento', 'Aberto' => 'info',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('qtde_inventariada')
                    ->label('Qtd.')
                    ->alignCenter(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel(),
                Tables\Actions\ViewAction::make()
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel(),
            ]);
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
