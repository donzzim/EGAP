<?php

namespace App\Filament\Resources\Cadastro;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\Cadastro\ContaContabilResource\Pages\ListContaContabils;
use App\Filament\Resources\Cadastro\ContaContabilResource\Pages\CreateContaContabil;
use App\Filament\Resources\Cadastro\ContaContabilResource\Pages\EditContaContabil;
use App\Filament\Resources\Cadastro\ContaContabilResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Filament\Resources\Cadastro\ContaContabilResource\RelationManagers;
use App\Models\Cadastro\ContaContabil;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContaContabilResource extends Resource
{
    protected static ?string $model = ContaContabil::class;
    protected static ?int $navigationSort = 2;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Conta Contábil';
    protected static ?string $modelLabel = 'Conta Contábil';
    protected static ?string $pluralModelLabel = 'Contas Contábeis';
    protected static string | \UnitEnum | null $navigationGroup = 'Cadastro';
    protected static ?string $maxContentWidth = '3xl';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Conta')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('codigo')
                                    ->label('Código')
                                    ->required()
                                    ->mask('*.*.*.*.*.**.**')
                                    ->maxLength(15)
                                    ->columnSpan(1),

                                TextInput::make('titulo')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Textarea::make('funcao')
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
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('codigo', 'Código', isFirstColumn: true),
                TableColumns::text('titulo', 'Título')
                    ->limit(50),
                TableColumns::text('funcao', 'Função')
                    ->wrap(),
                TableColumns::updatedBy('atualizado_por.name'),
            ])
            ->defaultSort('codigo');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContaContabils::route('/'),
            'create' => CreateContaContabil::route('/create'),
            'edit' => EditContaContabil::route('/{record}/edit'),
        ];
    }
}
