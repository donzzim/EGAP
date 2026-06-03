<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\SituacaoBemResource\Pages;
use App\Filament\Resources\Cadastro\SituacaoBemResource\RelationManagers;
use App\Models\Cadastro\SituacaoBem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SituacaoBemResource extends Resource
{
    protected static ?string $model = SituacaoBem::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?string $navigationLabel = 'Situação do Bem';
    protected static ?string $modelLabel = 'Situação do Bem';
    protected static ?string $pluralModelLabel = 'Situações do Bem';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da Situação')
                    ->schema([
                        Forms\Components\TextInput::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('situacao')
                            ->label('Status')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('situacao')
                    ->label('Status')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizado_por.name')
                    ->label('Atualizado por')
                    ->sortable()
                    ->searchable(),
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
                    ->hiddenLabel()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('descricao');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cadastro\SituacaoBemResource\Pages\ListSituacaoBems::route('/'),
            'create' => \App\Filament\Resources\Cadastro\SituacaoBemResource\Pages\CreateSituacaoBem::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\SituacaoBemResource\Pages\EditSituacaoBem::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}
