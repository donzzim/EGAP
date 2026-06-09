<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\ElementoDespesaResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Cadastro\ElementoDespesa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ElementoDespesaResource extends Resource
{
    protected static ?string $model = ElementoDespesa::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Elemento de Despesa';
    protected static ?string $modelLabel = 'Elemento de Despesa';
    protected static ?string $pluralModelLabel = 'Elementos de Despesa';
    protected static ?string $navigationGroup = 'Cadastro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('CodigodaClasse')
                    ->label('Código da Classe')
                    ->numeric()
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('DescricaodaClasse')
                    ->label('Descrição da Classe')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('Despesa')
                    ->label('Despesa')
                    ->maxLength(2)
                    ->required(),

                Forms\Components\TextInput::make('VidaUtil')
                    ->label('Vida Útil (anos)')
                    ->numeric()
                    ->minValue(0),

                Forms\Components\TextInput::make('ValorResidual')
                    ->label('Valor Residual')
                    ->numeric()
                    ->step(0.0000000001)
                    ->prefix('R$'),

                Forms\Components\TextInput::make('item_patrimonial')
                    ->label('Item Patrimonial')
                    ->maxLength(255),
            ])
            ->columns(2);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('CodigodaClasse', 'Código', isFirstColumn: true),
                TableColumns::text('DescricaodaClasse', 'Descrição')
                    ->limit(40),
                TableColumns::text('Despesa')
                    ->badge(),
                TableColumns::text('VidaUtil', 'Vida Útil'),
                TableColumns::dateTime('date_time', 'Atualizado em', 'd/m/Y H:i'),
                TableColumns::text('atualizado_por.name', 'Usuário'),
                TableColumns::money('ValorResidual')
            ])
            ->defaultSort('CodigodaClasse');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cadastro\ElementoDespesaResource\Pages\ListElementoDespesas::route('/'),
            'create' => \App\Filament\Resources\Cadastro\ElementoDespesaResource\Pages\CreateElementoDespesa::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\ElementoDespesaResource\Pages\EditElementoDespesa::route('/{record}/edit'),
        ];
    }
}
