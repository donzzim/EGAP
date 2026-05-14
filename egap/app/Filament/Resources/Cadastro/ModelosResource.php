<?php

namespace App\Filament\Egap\Resources\Cadastro;

use App\Filament\Egap\Resources\Cadastro\ModelosResource\Pages;
use App\Filament\Egap\Resources\Cadastro\ModelosResource\RelationManagers;
use App\Models\Egap\Cadastro\Modelos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ModelosResource extends Resource
{
    protected static ?string $model = Modelos::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?string $navigationLabel = 'Modelos';
    protected static ?string $modelLabel = 'Modelo';
    protected static ?string $pluralModelLabel = 'Modelos';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados do Modelo')
                    ->schema([
                        Forms\Components\Select::make('marca')
                            ->label('Marca')
                            ->relationship('marca_ref', 'descricao')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('descricao')
                            ->label('Descrição do Modelo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('marca_ref.descricao')
                    ->label('Marca')
                    ->default('Sem marca')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Modelo')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->alignCenter()
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizado_por.name')
                    ->label('Atualizado por')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('descricao');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Egap\Resources\Cadastro\ModelosResource\Pages\ListModelos::route('/'),
            'create' => \App\Filament\Egap\Resources\Cadastro\ModelosResource\Pages\CreateModelos::route('/create'),
            'edit' => \App\Filament\Egap\Resources\Cadastro\ModelosResource\Pages\EditModelos::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['marca_ref', 'atualizado_por']);
    }
}
