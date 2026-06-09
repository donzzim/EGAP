<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\MarcasResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('descricao', 'Descrição', isFirstColumn: true),
                TableColumns::dateTime('date_time', 'Atualizado em'),
                TableColumns::text('atualizado_por.name', 'Atualizado por')
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
            'index' => \App\Filament\Resources\Cadastro\MarcasResource\Pages\ListMarcas::route('/'),
            'create' => \App\Filament\Resources\Cadastro\MarcasResource\Pages\CreateMarcas::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\MarcasResource\Pages\EditMarcas::route('/{record}/edit'),
        ];
    }
}
