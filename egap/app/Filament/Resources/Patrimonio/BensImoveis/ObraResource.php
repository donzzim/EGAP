<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\ObraResource\Pages;
use App\Models\Patrimonio\BensImoveis\Obra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class ObraResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Obra::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Obras e Ampliações';
    protected static ?string $modelLabel = 'Obra e Ampliação';
    protected static ?string $pluralModelLabel = 'Obras e Ampliações';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'bens-imoveis/obras';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('id_imovel')
                            ->label('Imóveis')
                            ->relationship('imovelRelacaoref', 'descricao')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição')
                            ->columnSpanFull()
                            ->rows(4),

                        Forms\Components\DatePicker::make('data')
                            ->label('Data')
                            ->displayFormat('d/m/Y'),

                        Forms\Components\TextInput::make('valor')
                            ->label('Valor (R$)')
                            ->numeric(),

                        Forms\Components\Select::make('atualizado_por')
                            ->label('Atualizado por')
                            ->relationship('atualizadoPorRelacaoref', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('date_time')
                            ->label('date time')
                            ->hidden(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('imovelRelacaoref.descricao')
                    ->label('Imóveis')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('data')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor (R$)')
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
                    ->modalHeading('Editar Obras e Ampliações')
                    ->modalWidth('md'),

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
            ->emptyStateHeading('Nenhuma Obra e Ampliação encontrada');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListObras::route('/'),
        ];
    }
}
