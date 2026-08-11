<?php

namespace App\Filament\Resources\Processo;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use App\Filament\Resources\Processo\TipoProcessoResource\Pages\ListTipoProcessos;
use App\Filament\Resources\Processo\TipoProcessoResource\Pages\CreateTipoProcesso;
use App\Filament\Resources\Processo\TipoProcessoResource\Pages\EditTipoProcesso;
use App\Filament\Resources\Processo\TipoProcessoResource\Pages;
use App\Models\Processo\MatTipoProcesso;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TipoProcessoResource extends Resource
{
    protected static ?string $model = MatTipoProcesso::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static string | \UnitEnum | null $navigationGroup = 'Processos';
    protected static ?string $navigationLabel = 'Tipos de Processo';
    protected static ?string $modelLabel = 'Tipo de Processo';
    protected static ?string $pluralModelLabel = 'Tipos de Processo';
    protected static ?string $slug = 'processos/tipos-processos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
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
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                DeleteAction::make()
                    ->tooltip('Excluir')
                    ->modalHeading('Excluir registro')
                    ->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->striped()
            ->deferLoading()
            ->searchPlaceholder('Entre com a palavra-chave')
            ->emptyStateHeading('Nenhum Tipo de Processo encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTipoProcessos::route('/'),
            'create' => CreateTipoProcesso::route('/create'),
            'edit' => EditTipoProcesso::route('/{record}/edit'),
        ];
    }
}
