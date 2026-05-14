<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\ItemInventarioResource\Pages;
use App\Filament\Egap\Clusters\PatrimonioCluster; 
use App\Models\Egap\Patrimonio\BensMoveis\ItemInventario;
use App\Models\Egap\Cadastro\Setores;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Textarea, Grid, Section, Toggle};
use Filament\Pages\SubNavigationPosition;

class ItemInventarioResource extends Resource
{
    protected static ?string $model = ItemInventario::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $cluster = PatrimonioCluster::class;
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Materiais Inventariados';
    protected static ?string $pluralModelLabel = 'Materiais Inventariados';
    protected static ?int $navigationSort = 13;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Itens Inventariados')
                    ->schema([
                        Grid::make(3)->schema([
                            /** ✅ TextInput conforme o original */
                            TextInput::make('id_inventario')
                                ->label('Inventário'),
                            
                            TextInput::make('id_bem')
                                ->label('Material'),

                            TextInput::make('unidade_localizado')
                                ->label('Unidade Localizado'),

                            // LINHA 2
                            /** ✅ CORREÇÃO: Removido o filtro restritivo para que todas as unidades/comarcas apareçam */
                            Select::make('unidades')
                                ->label('Unidades')
                                ->options(fn() => Setores::pluck('Setor', 'id'))
                                ->searchable()
                                ->preload(), // Carrega os primeiros itens para facilitar a vista
                            
                            TextInput::make('setor')
                                ->label('Setor'),
                            
                            TextInput::make('setor_localizado')
                                ->label('Setor Localizado'),

                            // LINHA 3
                            TextInput::make('id_complementosetor')
                                ->label('Complemento Setor'),

                            TextInput::make('complemento_localizado')
                                ->label('Complemento Localizado'),

                            TextInput::make('num_patrimonio')
                                ->label('Patrimônio Nº'),

                            // LINHA 4
                            TextInput::make('num_patrimonioantigo')
                                ->label('Patrimônio (sem cód. barras)'),

                            TextInput::make('num_serie')
                                ->label('Nº de Série'),

                            TextInput::make('estado_conservacao')
                                ->label('Estado de Conservação'),

                            // LINHA 5
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
                                ->rows(3),
                            TextInput::make('observacao')
                                ->label('Observação'),
                            TextInput::make('situacao')
                                ->label('Situação'),
                        ]),

                        Section::make('Dados e-GAP (Controle)')
                            ->collapsed()
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('atualizado_por')->label('Atualizado por'),
                                    TextInput::make('num_serie_egap')->label('Num Serie eGAP'),
                                    TextInput::make('descricao_detalhada_egap')->label('Descricao Detalhada eGAP'),
                                    
                                    TextInput::make('marca_egap')->label('Marca eGAP'),
                                    TextInput::make('modelo_egap')->label('Modelo eGAP'),
                                    TextInput::make('termo')->label('Termo'),

                                    DatePicker::make('transferido_em')->label('Transferido em'),
                                    TextInput::make('conciliado_patrimonio')->label('Conciliado (Patrimônio)'),
                                    TextInput::make('imagem_enviada')->label('Imagem Enviada'),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Por favor, selecione ao menos um filtro.')
            ->columns([
                Tables\Columns\TextColumn::make('num_patrimonio')->label('Patrimônio Nº')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('descricao_resumida')->label('Descrição')->limit(30),
                Tables\Columns\TextColumn::make('estado_conservacao')->label('Estado'),
                Tables\Columns\TextColumn::make('date_time')->label('Atualizado em')->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ]);
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