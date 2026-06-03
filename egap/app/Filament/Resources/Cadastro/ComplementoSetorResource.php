<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages;
use App\Models\Cadastro\ComplementoSetor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComplementoSetorResource extends Resource
{
    protected static ?string $model = ComplementoSetor::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Complemento de Setor';

    protected static ?string $modelLabel = 'Complemento de Setor';

    protected static ?string $pluralModelLabel = 'Complementos de Setor';

    protected static ?string $navigationGroup = 'Cadastro';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->rows(4)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Placeholder::make('date_time')
                    ->label('Data de Atualização')
                    ->content(fn ($record) => $record?->date_time?->format('d/m/Y H:i'))
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
                    ->wrap(),

                Tables\Columns\TextColumn::make('atualizado_por.name')
                    ->label('Atualizado por')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Data Atualização')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'asc')
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages\ListComplementoSetors::route('/'),
            'create' => \App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages\CreateComplementoSetor::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages\EditComplementoSetor::route('/{record}/edit'),
        ];
    }
}
