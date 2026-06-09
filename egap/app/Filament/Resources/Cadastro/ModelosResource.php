<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\ModelosResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Filament\Resources\Cadastro\ModelosResource\RelationManagers;
use App\Models\Cadastro\Modelos;
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
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('marca_ref.descricao', 'Marca', isFirstColumn: true)
                    ->default('Sem marca'),
                TableColumns::text('descricao', 'Modelo'),
                TableColumns::dateTime('date_time', 'Atualizado em', 'd/m/Y H:i'),
                TableColumns::text('atualizado_por.name', 'Atualizado por'),
            ])
            ->defaultSort('descricao');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cadastro\ModelosResource\Pages\ListModelos::route('/'),
            'create' => \App\Filament\Resources\Cadastro\ModelosResource\Pages\CreateModelos::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\ModelosResource\Pages\EditModelos::route('/{record}/edit'),
        ];
    }
}
