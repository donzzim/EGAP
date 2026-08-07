<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\ModelosResource\Pages\CreateModelos;
use App\Filament\Resources\Cadastro\ModelosResource\Pages\EditModelos;
use App\Filament\Resources\Cadastro\ModelosResource\Pages\ListModelos;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Cadastro\Modelos;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ModelosResource extends Resource
{
    protected static ?string $model = Modelos::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|\UnitEnum|null $navigationGroup = 'Cadastro';

    protected static ?string $navigationLabel = 'Modelos';

    protected static ?string $modelLabel = 'Modelo';

    protected static ?string $pluralModelLabel = 'Modelos';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Modelo')
                    ->schema([
                        Select::make('marca')
                            ->label('Marca')
                            ->relationship('marca_ref', 'descricao')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('descricao')
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
                TableColumns::updatedBy('atualizado_por.name'),
            ])
            ->defaultSort('descricao');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModelos::route('/'),
            'create' => CreateModelos::route('/create'),
            'edit' => EditModelos::route('/{record}/edit'),
        ];
    }
}
