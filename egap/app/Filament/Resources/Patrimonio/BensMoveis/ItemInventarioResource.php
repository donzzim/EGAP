<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\ItemInventarioResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Cadastro\Setores;
use App\Models\Patrimonio\BensMoveis\ItemInventario;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ItemInventarioResource extends Resource
{
    protected static ?string $model = ItemInventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Materiais Inventariados';

    protected static ?string $pluralModelLabel = 'Materiais Inventariados';

    protected static ?string $modelLabel = 'Material Inventariado';

    protected static ?int $navigationSort = 13;

    protected static ?string $slug = 'bens-moveis/materiais-inventariados';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Itens Inventariados')
                    ->description('Registre a identificação, localização e conferência física do material.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('id_inventario')
                                ->label('Inventário'),

                            TextInput::make('id_bem')
                                ->label('Material'),

                            TextInput::make('unidade_localizado')
                                ->label('Unidade Localizado'),

                            Select::make('unidades')
                                ->label('Unidades')
                                ->options(fn () => Setores::pluck('Setor', 'id'))
                                ->searchable()
                                ->preload()
                                ->native(false),

                            TextInput::make('setor')
                                ->label('Setor'),

                            TextInput::make('setor_localizado')
                                ->label('Setor Localizado'),

                            TextInput::make('id_complementosetor')
                                ->label('Complemento Setor'),

                            TextInput::make('complemento_localizado')
                                ->label('Complemento Localizado'),

                            TextInput::make('num_patrimonio')
                                ->label('Patrimônio Nº'),

                            TextInput::make('num_patrimonioantigo')
                                ->label('Patrimônio (sem cód. barras)'),

                            TextInput::make('num_serie')
                                ->label('Nº de Série'),

                            TextInput::make('estado_conservacao')
                                ->label('Estado de Conservação'),

                            TextInput::make('descricao_resumida')
                                ->label('Descrição Resumida')
                                ->columnSpan(1),

                            TextInput::make('marca')
                                ->label('Marca'),

                            TextInput::make('modelo')
                                ->label('Modelo'),
                        ]),

                        Grid::make(3)->schema([
                            Textarea::make('descricao_detalhada')
                                ->label('Descrição Detalhada')
                                ->rows(3)
                                ->columnSpan(2),
                            Textarea::make('observacao')
                                ->label('Observação')
                                ->rows(3),
                            TextInput::make('situacao')
                                ->label('Situação'),
                        ]),

                        Section::make('Dados e-GAP (Controle)')
                            ->description('Campos técnicos de integração e auditoria.')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->collapsed()
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('atualizado_por')->label('Atualizado por'),
                                    TextInput::make('num_serie_egap')->label('Num Serie eGAP'),
                                    TextInput::make('descricao_detalhada_egap')->label('Descricao Detalhada eGAP'),

                                    TextInput::make('marca_egap')->label('Marca eGAP'),
                                    TextInput::make('modelo_egap')->label('Modelo eGAP'),
                                    TextInput::make('termo')->label('Termo'),

                                    DatePicker::make('transferido_em')->label('Transferido em')->displayFormat('d/m/Y')->native(false),
                                    TextInput::make('conciliado_patrimonio')->label('Conciliado (Patrimônio)'),
                                    TextInput::make('imagem_enviada')->label('Imagem Enviada'),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('num_patrimonio', 'Patrimônio Nº', isFirstColumn: true)
                    ->badge(),
                TableColumns::text('descricao_resumida', 'Descrição')
                    ->limit(40)
                    ->tooltip(fn ($record): ?string => $record->descricao_resumida),
                TableColumns::text('inventario.num_inventario', 'Inventário'),
                TableColumns::text('estado_conservacao', 'Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'ÓTIMO', 'BOM' => 'success',
                        'REGULAR' => 'warning',
                        'RUIM', 'SUCATA' => 'danger',
                        default => 'gray',
                    }),
                TableColumns::text('situacao', 'Situação')->badge(),
                TableColumns::dateTime('date_time', 'Atualizado em')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_inventario')
                    ->label('Inventário')
                    ->relationship('inventario', 'num_inventario')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('estado_conservacao')
                    ->label('Estado de Conservação')
                    ->options(['ÓTIMO' => 'ÓTIMO', 'BOM' => 'BOM', 'REGULAR' => 'REGULAR', 'RUIM' => 'RUIM', 'SUCATA' => 'SUCATA']),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemInventarios::route('/'),
            'create' => Pages\CreateItemInventario::route('/create'),
            'edit' => Pages\EditItemInventario::route('/{record}/edit'),
        ];
    }
}
