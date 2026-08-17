<?php

namespace App\Filament\Resources\Agendamento;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use App\Filament\Resources\Agendamento\FrotaResource\Pages\ListFrotas;
use App\Filament\Resources\Agendamento\FrotaResource\Pages\CreateFrota;
use App\Filament\Resources\Agendamento\FrotaResource\Pages\EditFrota;
use App\Filament\Resources\Agendamento\FrotaResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Agendamento\Frota;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FrotaResource extends Resource
{
    protected static ?string $model = Frota::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';
    protected static string | \UnitEnum | null $navigationGroup = 'Agendamento';
    protected static ?string $modelLabel = 'Veículo';
    protected static ?string $pluralModelLabel = 'Frota';
    protected static ?string $navigationLabel = 'Frota de Veículos';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    TextInput::make('descricao')
                        ->label('Descrição')
                        ->placeholder('Ex: Fiat Strada branca')
                        ->required()
                        ->maxLength(50)
                        ->columnSpan(12),

                    Select::make('marca')
                        ->label('Marca')
                        ->relationship('marcaRef', 'descricao')
                        ->searchable()
                        ->required()
                        ->preload()
                        ->placeholder('Selecione a marca')
                        ->columnSpan(4),

                    Select::make('modelo')
                        ->label('Modelo')
                        ->required()
                        ->relationship('modeloRef', 'descricao')
                        ->searchable()
                        ->preload()
                        ->placeholder('Selecione o modelo')
                        ->columnSpan(4),

                    TextInput::make('placa')
                        ->label('Placa')
                        ->required()
                        ->placeholder('ABC-1D23')
                        ->maxLength(255)
                        ->columnSpan(4),

                    Select::make('proprietario')
                        ->label('Proprietário')
                        ->required()
                        ->options([
                            '1' => 'Tribunal de Justiça',
                            '2' => 'Empresa Globo',
                        ])
                        ->required()
                        ->columnSpan(12),

                    Fieldset::make('Status do veículo')
                        ->schema([
                            Toggle::make('disponivel')
                                ->label('Disponível')
                                ->required()
                                ->default(true)
                                ->inline(false),

                            Toggle::make('ativo')
                                ->label('Ativo')
                                ->required()
                                ->default(true)
                                ->inline(false),
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('descricao', 'Descrição', true)
                    ->badge(),

                TableColumns::text('marcaRef.descricao', 'Marca'),

                TableColumns::text('modeloRef.descricao', 'Modelo'),

                TableColumns::text('placa', 'Placa')
                    ->badge()
                    ->color('gray'),

                TableColumns::text('proprietario', 'Proprietário'),

                IconColumn::make('disponivel')
                    ->label('Disponível')
                    ->alignCenter()
                    ->default(false)
                    ->boolean(),

                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->alignCenter()
                    ->boolean(),

                TableColumns::updatedBy('idUserRef.name'),
            ])
            ->defaultSort('descricao', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFrotas::route('/'),
        ];
    }
}
