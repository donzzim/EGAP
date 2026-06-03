<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\CentroCustoResource\Pages;
use App\Models\Cadastro\CentroCusto;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CentroCustoResource extends Resource
{
    protected static ?string $model = CentroCusto::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?string $navigationLabel = 'Centro de Custo (SIGEFES)';

    protected static ?string $modelLabel = 'Centro de Custo';
    protected static ?string $pluralModelLabel = 'Centros de Custo';

//    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([

                        TextInput::make('codigo')
                            ->label('Código')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        TextInput::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(255),

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([

                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->alignCenter()
                    ->sortable()
                    ->wrap(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                Tables\Actions\ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Excluir')
                    ->modalHeading('Excluir registro')
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('codigo');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cadastro\CentroCustoResource\Pages\ListCentroCustos::route('/'),
            'create' => \App\Filament\Resources\Cadastro\CentroCustoResource\Pages\CreateCentroCusto::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\CentroCustoResource\Pages\EditCentroCusto::route('/{record}/edit'),
        ];
    }
}
