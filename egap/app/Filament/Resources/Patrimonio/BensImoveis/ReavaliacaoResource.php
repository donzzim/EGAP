<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\ReavaliacaoResource\Pages;
use App\Models\Patrimonio\BensImoveis\Reavaliacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class ReavaliacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Reavaliacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Reavaliação';
    protected static ?string $modelLabel = 'Reavaliação';
    protected static ?string $pluralModelLabel = 'Reavaliação';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'bens-imoveis/reavaliacoes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Reavaliação dos Imóveis')
                            ->schema([
                                Forms\Components\Select::make('Id_imovel')
                                    ->label('Imóvel')
                                    ->relationship('imovelRelacaoref', 'descricao')
                                    ->searchable()
                                    ->preload()
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        $reavaliacao = \Illuminate\Support\Facades\DB::connection('egap')->table('imo_reavaliacao')
                                            ->where('Id_imovel', $record->Id)
                                            ->orderBy('Id', 'desc')
                                            ->first();

                                        if ($reavaliacao) {
                                            $valor = number_format((float)$reavaliacao->valor_reavaliacao, 2, '.', '');
                                            $data = date('d/m/Y', strtotime($reavaliacao->data_reavaliacao));
                                            return "{$record->descricao} <span style='display:none'> [{$valor}, {$data}]</span>";
                                        }
                                        return $record->descricao;
                                    })
                                    ->allowHtml(),

                                Forms\Components\DatePicker::make('data_reavaliacao')
                                    ->label('Data Reavaliação')
                                    ->default(now())
                                    ->displayFormat('d/m/Y'),

                                Forms\Components\TextInput::make('valor_reavaliacao')
                                    ->label('Valor Reavaliação')
                                    ->numeric(),

                                Forms\Components\TextInput::make('vida_util_reavaliacao')
                                    ->label('Vida Útil Reavaliação')
                                    ->numeric(),

                                Forms\Components\Select::make('Id_estadoconservacao')
                                    ->label('Estado de Conservação')
                                    ->relationship('estadoConservacaoRelacaoref', 'descEstadoConservacao')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\TextInput::make('ajuste_contabil')
                                    ->label('Ajuste Contábil')
                                    ->numeric(),

                                Forms\Components\Textarea::make('observacao')
                                    ->label('Observação')
                                    ->columnSpanFull()
                                    ->rows(4),
                            ])->columns(4),

                        Forms\Components\Tabs\Tab::make('Complemento')
                            ->schema([
                                Forms\Components\TextInput::make('valor_mercado')
                                    ->label('Valor Mercado')
                                    ->numeric(),

                                Forms\Components\Select::make('atualizado_por')
                                    ->label('Atualizado por')
                                    ->relationship('atualizadoPorRelacaoref', 'name')
                                    ->default(fn () => auth()->id())
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\DateTimePicker::make('data_disponibilizacao')
                                    ->label('Data Disponibilização')
                                    ->default(now())
                                    ->displayFormat('d/m/Y H:i:s'),

                                Forms\Components\DateTimePicker::make('data_referencia')
                                    ->label('Data Referência')
                                    ->default(now())
                                    ->displayFormat('d/m/Y H:i:s'),

                                Forms\Components\TextInput::make('valor_aquisicao')
                                    ->label('Valor Aquisição')
                                    ->numeric(),

                                Forms\Components\TextInput::make('vida_util_siafi')
                                    ->label('Vida Útil SIAFI')
                                    ->numeric(),

                                Forms\Components\TextInput::make('vida_util')
                                    ->label('Vida Útil')
                                    ->numeric(),

                                Forms\Components\TextInput::make('tempo_utilizacao_meses')
                                    ->label('Tempo Utilização Meses')
                                    ->numeric(),

                                Forms\Components\TextInput::make('vida_util_remanescente_meses')
                                    ->label('Vida Útil Remanescente Meses')
                                    ->numeric(),

                                Forms\Components\TextInput::make('vida_util_estimada_anos')
                                    ->label('Vida Útil Estimada Anos')
                                    ->numeric(),

                                Forms\Components\TextInput::make('PUB1')
                                    ->label('PUB1')
                                    ->numeric(),

                                Forms\Components\TextInput::make('PUV')
                                    ->label('PUV')
                                    ->numeric(),

                                Forms\Components\TextInput::make('FR')
                                    ->label('FR')
                                    ->numeric(),

                                Forms\Components\TextInput::make('utilizacao_bem_anos')
                                    ->label('Utilização Bem Anos')
                                    ->numeric(),

                                Forms\Components\TextInput::make('idade_aparente_anos')
                                    ->label('Idade Aparente Anos')
                                    ->numeric(),

                                Forms\Components\DateTimePicker::make('date_time')
                                    ->label('date time')
                                    ->hidden(),
                            ])->columns(4),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('Id')
                    ->label('Id')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('imovelRelacaoref.descricao')
                    ->label('Imóvel')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('data_reavaliacao')
                    ->label('Data Reavaliação')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor_reavaliacao')
                    ->label('Valor Reavaliação')
                    ->numeric(2, ',', '.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vida_util_reavaliacao')
                    ->label('Vida Útil Reavaliação')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('estadoConservacaoRelacaoref.descEstadoConservacao')
                    ->label('Estado de Conservação')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('ajuste_contabil')
                    ->label('Ajuste Contábil')
                    ->numeric(2, ',', '.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('observacao')
                    ->label('Observação')
                    ->limit(50)
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
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Editar Reavaliação')
                    ->modalWidth('7xl'),

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
            ->emptyStateHeading('Nenhuma Reavaliação encontrada');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReavaliacoes::route('/'),
        ];
    }
}
