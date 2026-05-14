<?php

namespace App\Filament\Egap\Resources\Cadastro;

use App\Filament\Egap\Resources\Cadastro\UnidadesDeMedidaResource\Pages;
use App\Filament\Egap\Resources\Cadastro\UnidadesDeMedidaResource\RelationManagers;
use App\Models\Egap\Cadastro\UnidadesDeMedida;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UnidadesDeMedidaResource extends Resource
{
    protected static ?string $model = UnidadesDeMedida::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Unidades de Medida';
    protected static ?string $modelLabel = 'Unidade de Medida';
    protected static ?string $pluralModelLabel = 'Unidades de Medida';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\TextInput::make('Sigla')
                                    ->label('Sigla')
                                    ->required()
                                    ->maxLength(2),
//                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('Unidade')
                                    ->label('Descrição da Unidade')
                                    ->required()
                                    ->maxLength(100),
//                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Sigla')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('Unidade')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizado_por.name')
                    ->label('Atualizado por')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('Sigla')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Egap\Resources\Cadastro\UnidadesDeMedidaResource\Pages\ListUnidadesDeMedida::route('/'),
            'create' => \App\Filament\Egap\Resources\Cadastro\UnidadesDeMedidaResource\Pages\CreateUnidadesDeMedida::route('/create'),
            'edit' => \App\Filament\Egap\Resources\Cadastro\UnidadesDeMedidaResource\Pages\EditUnidadesDeMedida::route('/{record}/edit'),
        ];
    }
}
