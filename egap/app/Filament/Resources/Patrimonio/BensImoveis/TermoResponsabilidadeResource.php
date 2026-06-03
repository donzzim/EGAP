<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages;
use App\Models\Patrimonio\BensImoveis\TermoResponsabilidade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TermoResponsabilidadeResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TermoResponsabilidade::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Termos de Responsabilidade';
    protected static ?string $modelLabel = 'Termo de Responsabilidade';
    protected static ?string $pluralModelLabel = 'Termos de Responsabilidade';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 7;
    protected static ?string $slug = 'bens-imoveis/termo-responsabilidade-imoveis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('num_termo')
                            ->label('Termo Nº')
                            ->numeric(),

                        Forms\Components\TextInput::make('ano_termo')
                            ->label('Ano')
                            ->numeric(),

                        Forms\Components\FileUpload::make('arquivo')
                            ->label('Arquivo')
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('atualizado_em')
                            ->label('Atualizado em')
                            ->displayFormat('d/m/Y'),

                        Forms\Components\Select::make('atualizado_por')
                            ->label('Atualizado por')
                            ->relationship('atualizadoPorRelacaoref', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Repeater::make('termosImoveis')
                            ->relationship('termosImoveis')
                            ->label('')
                            ->schema([
                                Forms\Components\DateTimePicker::make('date_time')
                                    ->label('date time')
                                    ->displayFormat('Y-m-d H:i:s'),

                                Forms\Components\Select::make('termo')
                                    ->label('Termo')
                                    ->relationship('termoRelacaoref', 'num_termo')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('imovel')
                                    ->label('Imóvel')
                                    ->relationship('imovelRelacaoref', 'descricao')
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addable(false)
                            ->deletable(false)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('num_termo')
                    ->label('Termo Nº')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('ano_termo')
                    ->label('Ano')
                    ->sortable()
                    ->searchable(),

                    Tables\Columns\TextColumn::make('arquivo')
                    ->label('Arquivo')
                    ->searchable()
                    ->url(fn ($record) => $record->arquivo ? "https://sistemas.tjes.jus.br/patrimonio{$record->arquivo}" : null)
                    ->openUrlInNewTab()
                    ->extraCellAttributes(['style' => 'color: #3b82f6; text-decoration: underline; cursor: pointer;']),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('atualizadoPorRelacaoref.name')
                    ->label('Atualizado por')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('termosImoveis.imovelRelacaoref.descricao')
                    ->label('Imóvel')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Editar Termos de Responsabilidade')
                    ->modalWidth('4xl'),

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
            ->emptyStateHeading('Nenhum Termo de Responsabilidade encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTermoResponsabilidades::route('/'),
        ];
    }
}
