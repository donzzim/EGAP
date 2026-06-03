<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\CedidoResource\Pages;
use App\Models\Patrimonio\BensImoveis\Cedido;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class CedidoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Cedido::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Imóveis Ocupados por Terceiros';
    protected static ?string $modelLabel = 'Imóvel Ocupado por Terceiro';
    protected static ?string $pluralModelLabel = 'Imóveis Ocupados por Terceiros';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'bens-imoveis/ocupados-por-terceiros';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('1. Imóveis')
                            ->schema([
                                Forms\Components\Select::make('id_imovel')
                                    ->label('Imóvel')
                                    ->relationship('imovelRelacaoref', 'descricao')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Textarea::make('resumo')
                                    ->label('Partes/Terceiros')
                                    ->rows(4),

                                Forms\Components\Textarea::make('proprietario_responsavel')
                                    ->label('Proprietário/Responsável')
                                    ->rows(4),

                                Forms\Components\Textarea::make('condicao_uso')
                                    ->label('Condição de Uso')
                                    ->rows(4),

                                Forms\Components\TextInput::make('num_processo')
                                    ->label('Núm. Processo'),

                                Forms\Components\FileUpload::make('termo_digital')
                                    ->label('Termo Digital'),

                                Forms\Components\Textarea::make('objeto')
                                    ->label('Objeto')
                                    ->rows(4),

                                Forms\Components\Textarea::make('fiscais')
                                    ->label('Fiscais')
                                    ->rows(4),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('2. Situação')
                            ->schema([
                                Forms\Components\Select::make('situacao')
                                    ->label('Situação')
                                    ->options([
                                        'Vigente' => 'Vigente',
                                    ]),

                                Forms\Components\Select::make('atualizado_por')
                                    ->label('Atualizado por')
                                    ->relationship('atualizadoPorRelacaoref', 'name')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Textarea::make('observacao')
                                    ->label('Observação')
                                    ->columnSpanFull()
                                    ->rows(4),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('3. Publicações')
                            ->schema([
                                Forms\Components\DatePicker::make('data_assinatura')
                                    ->label('Data Assinatura')
                                    ->displayFormat('d/m/Y'),

                                Forms\Components\TextInput::make('ato_diario')
                                    ->label('Ato Diário'),

                                Forms\Components\DatePicker::make('data_publicacao')
                                    ->label('Data Publicação')
                                    ->displayFormat('d/m/Y'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('4. Vigência')
                            ->schema([
                                Forms\Components\DatePicker::make('vencimento')
                                    ->label('Vencimento')
                                    ->displayFormat('d/m/Y'),

                                Forms\Components\TextInput::make('vigencia')
                                    ->label('Vigência'),

                                Forms\Components\DatePicker::make('aditivo_vigencia')
                                    ->label('Aditivo Data Vigência')
                                    ->displayFormat('d/m/Y'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('5. Ocupação do espaço')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Radio::make('retribuicao')
                                            ->label('Retribuição')
                                            ->options([
                                                'Oneroso' => 'Oneroso',
                                                'Gratuito' => 'Gratuito',
                                            ])
                                            ->columnSpan(1),

                                        Forms\Components\CheckboxList::make('despesas')
                                            ->label('Despesas')
                                            ->options([
                                                'Energia' => 'Energia',
                                                'Água' => 'Água',
                                                'Tributos' => 'Tributos',
                                                'taxa de coleta de lixo' => 'taxa de coleta de lixo',
                                            ])
                                            ->columns(2)
                                            ->columnSpan(1),
                                    ])->columns(2)
                            ]),

                        Forms\Components\Tabs\Tab::make('6. Gestores/Fiscais')
                            ->schema([
                                Forms\Components\Repeater::make('gestores')
                                    ->relationship('gestores')
                                    ->schema([
                                        Forms\Components\TextInput::make('ato_diario')
                                            ->label('Ato Diário'),

                                        Forms\Components\DatePicker::make('data_publicacao')
                                            ->label('Data Publicação')
                                            ->displayFormat('d/m/Y'),

                                        Forms\Components\Select::make('gestor_fiscal')
                                            ->label('Gestor/Fiscal')
                                            ->options([
                                                'Gestor' => 'Gestor',
                                                'Fiscal' => 'Fiscal',
                                            ]),

                                        Forms\Components\Select::make('nome')
                                            ->label('Nome')
                                            ->relationship('nomeRelacaoref', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\DatePicker::make('data_encerramento')
                                            ->label('Data encerramento')
                                            ->displayFormat('d/m/Y'),

                                        Forms\Components\Select::make('atualizado_por')
                                            ->label('Atualizado por')
                                            ->relationship('atualizadoPorRelacaoref', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->hidden(),
                                    ])
                                    ->columns(5)
                                    ->defaultItems(0)
                                    ->hiddenLabel()
                                    ->addActionLabel('Adicionar gestores/fiscais')
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('imovelRelacaoref.descricao')
                    ->label('Imóvel')
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('resumo')
                    ->label('Partes/Terceiros')
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('proprietario_responsavel')
                    ->label('Proprietário/Responsável')
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('condicao_uso')
                    ->label('Condição de Uso')
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('num_processo')
                    ->label('Núm. Processo')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('data_assinatura')
                    ->label('Data Assinatura')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vencimento')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vigencia')
                    ->label('Vigência')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square'),

                Tables\Actions\DeleteAction::make()
                    ->label('Excluir')
                    ->color('danger')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ]),
            ])
            ->searchPlaceholder('Entre com a palavra-chave')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->emptyStateHeading('Nenhum Imóvel Ocupado por Terceiros encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCedidos::route('/'),
            //'create' => Pages\CreateCedido::route('/create'),
            //'edit' => Pages\EditCedido::route('/{record}/edit'),
        ];
    }
}
