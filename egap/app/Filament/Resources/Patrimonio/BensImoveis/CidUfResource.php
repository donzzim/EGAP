<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\CidUfResource\Pages;
use App\Models\Patrimonio\BensImoveis\CidUf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class CidUfResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = CidUf::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Cidade/UF';
    protected static ?string $modelLabel = 'Cidade/UF';
    protected static ?string $pluralModelLabel = 'Cidades/UF';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 9;
    protected static ?string $slug = 'bens-imoveis/cidades-uf';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('id_cidade')
                            ->label('id cidade')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('cd_uf')
                            ->label('cd uf')
                            ->maxLength(2)
                            ->required(),

                        Forms\Components\TextInput::make('cd_cep_cidade')
                            ->label('cd cep cidade')
                            ->maxLength(20)
                            ->required(),
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('id')
                    ->sortable()
                    ->searchable()
                    ->width('80px'),

                Tables\Columns\TextColumn::make('id_cidade')
                    ->label('id cidade')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cd_uf')
                    ->label('cd uf')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cd_cep_cidade')
                    ->label('cd cep cidade')
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
                    ->modalHeading('Editar Cidade/UF')
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
            ->emptyStateHeading('Nenhuma Cidade/UF encontrada');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCidUfs::route('/'),
        ];
    }
}
