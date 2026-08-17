<?php

namespace App\Filament\Resources\Cadastro;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Filament\Resources\Cadastro\MarcasResource\Pages\ListMarcas;
use App\Filament\Resources\Cadastro\MarcasResource\Pages\CreateMarcas;
use App\Filament\Resources\Cadastro\MarcasResource\Pages\EditMarcas;
use App\Filament\Resources\Cadastro\MarcasResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Filament\Resources\Cadastro\MarcasResource\RelationManagers;
use App\Models\Cadastro\Marcas;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarcasResource extends Resource
{
    protected static ?string $model = Marcas::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $recordTitleAttribute = 'descricao';
    protected static ?string $modelLabel = 'Marcas ';
    protected static ?string $pluralModelLabel = 'Marcas ';
    protected static ?string $navigationLabel = 'Marcas ';
    protected static string | \UnitEnum | null $navigationGroup = 'Cadastro';
    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255)
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('tipobem')
                    ->label('Tipo do Bem')
                    ->options([
                        '0' => 'Outros',
                        '1' => 'Veículos',
                    ])
                    ->columnSpanFull(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('descricao', 'Descrição', isFirstColumn: true),
                TableColumns::text('tipobem', 'Tipo do Bem'),
                TableColumns::updatedBy('atualizado_por.name')
            ]);
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
            'index' => ListMarcas::route('/'),
        ];
    }
}
