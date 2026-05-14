<?php

namespace App\Filament\Egap\Resources\Cadastro;

use App\Filament\Egap\Resources\Cadastro\ContaContabilResource\Pages;
use App\Filament\Egap\Resources\Cadastro\ContaContabilResource\RelationManagers;
use App\Models\Egap\Cadastro\ContaContabil;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContaContabilResource extends Resource
{
    protected static ?string $model = ContaContabil::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Conta Contábil';
    protected static ?string $modelLabel = 'Conta Contábil';
    protected static ?string $pluralModelLabel = 'Contas Contábeis';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?string $maxContentWidth = '3xl';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da Conta')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('codigo')
                                    ->label('Código')
                                    ->required()
                                    ->mask('*.*.*.*.*.**.**')
                                    ->maxLength(15)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('titulo')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Forms\Components\Textarea::make('funcao')
                                    ->label('Função')
                                    ->required()
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(1)
                    ->compact(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
//                    ->width('150px')
                    ->sortable(),

                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->limit(50)
//                    ->width('150px')
                    ->sortable(),

                Tables\Columns\TextColumn::make('funcao')
                    ->label('Função')
                    ->wrap()
//                    ->width('250px')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
//                    ->width('100px')
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizado_por.name')
                    ->label('Atualizado por')
                    ->sortable()
//                    ->width('100px')
                    ->toggleable(),
            ])
            ->defaultSort('codigo')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),

                Tables\Actions\DeleteAction::make()
                    ->label('Excluir'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Excluir selecionados'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Egap\Resources\Cadastro\ContaContabilResource\Pages\ListContaContabils::route('/'),
            'create' => \App\Filament\Egap\Resources\Cadastro\ContaContabilResource\Pages\CreateContaContabil::route('/create'),
            'edit' => \App\Filament\Egap\Resources\Cadastro\ContaContabilResource\Pages\EditContaContabil::route('/{record}/edit'),
        ];
    }
}
