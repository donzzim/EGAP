<?php

namespace App\Filament\Resources\Processo;

use App\Filament\Resources\Processo\TipoProcessoResource\Pages;
use App\Models\Processo\MatTipoProcesso;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TipoProcessoResource extends Resource
{
    protected static ?string $model = MatTipoProcesso::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Processos';
    protected static ?string $navigationLabel = 'Tipos de Processo';
    protected static ?string $modelLabel = 'Tipo de Processo';
    protected static ?string $pluralModelLabel = 'Tipos de Processo';
    protected static ?string $slug = 'processos/tipos-processos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('descricao')
                ->required()
                ->maxLength(255)
                ->label('Descrição')
                ->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
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
                Tables\Actions\BulkActionGroup::make([]),
            ])
            ->striped()
            ->deferLoading()
            ->searchPlaceholder('Entre com a palavra-chave')
            ->emptyStateHeading('Nenhum Tipo de Processo encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoProcessos::route('/'),
            'create' => Pages\CreateTipoProcesso::route('/create'),
            'edit' => Pages\EditTipoProcesso::route('/{record}/edit'),
        ];
    }
}
