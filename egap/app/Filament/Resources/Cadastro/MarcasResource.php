<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\MarcasResource\Pages;
use App\Filament\Resources\Cadastro\MarcasResource\RelationManagers;
use App\Models\Cadastro\Marcas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarcasResource extends Resource
{
    protected static ?string $model = Marcas::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $recordTitleAttribute = 'descricao';
    protected static ?string $modelLabel = 'Marcas ';
    protected static ?string $pluralModelLabel = 'Marcas ';
    protected static ?string $navigationLabel = 'Marcas ';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?int $navigationSort = 9;

    public static function getFormfields(): array
    {
        return [
            Forms\Components\TextInput::make('descricao')
                ->label('Descrição')
                ->required()
                ->maxLength(255)
                ->default(null)
                ->columnSpanFull(),
            Forms\Components\Select::make('tipobem')
                ->label('Tipo do Bem')
                ->options([
                    '0' => 'Outros',
                    '1' => 'Veículos',
                ])
                ->columnSpanFull(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormFields());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('descricao')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->alignCenter()
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('atualizado_por.name')
                    ->label('Atualizado por')
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipobem')
                    ->alignCenter()
                    ->label('Tipo do Bem')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
             ->selectCurrentPageOnly()
            ->paginated([50, 100, 150, 200, 'all'])
            ->defaultPaginationPageOption(50)
            ->striped()
            ->deferLoading();
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
            'index' => \App\Filament\Resources\Cadastro\MarcasResource\Pages\ListMarcas::route('/'),
            'create' => \App\Filament\Resources\Cadastro\MarcasResource\Pages\CreateMarcas::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\MarcasResource\Pages\EditMarcas::route('/{record}/edit'),
        ];
    }
}
