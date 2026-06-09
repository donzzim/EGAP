<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\ContaContabilResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Filament\Resources\Cadastro\ContaContabilResource\RelationManagers;
use App\Models\Cadastro\ContaContabil;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContaContabilResource extends Resource
{
    protected static ?string $model = ContaContabil::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Conta Contábil';
    protected static ?string $modelLabel = 'Conta Contábil';
    protected static ?string $pluralModelLabel = 'Contas Contábeis';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?string $maxContentWidth = '3xl';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da Conta')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('codigo')
                                    ->label('Código')
                                    ->required()
                                    ->mask('*.*.*.*.*.**.**')
                                    ->maxLength(15)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('titulo')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Forms\Components\Textarea::make('funcao')
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
                TableColumns::dateTime('date_time', 'Atualizado em', 'd/m/Y H:i'),
                TableColumns::text('atualizado_por.name', 'Atualizado por'),
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
            'index' => \App\Filament\Resources\Cadastro\ContaContabilResource\Pages\ListContaContabils::route('/'),
            'create' => \App\Filament\Resources\Cadastro\ContaContabilResource\Pages\CreateContaContabil::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\ContaContabilResource\Pages\EditContaContabil::route('/{record}/edit'),
        ];
    }
}
