<?php

namespace App\Filament\Resources\Cadastro;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use App\Filament\Resources\Cadastro\CentroCustoResource\Pages\ListCentroCustos;
use App\Filament\Resources\Cadastro\CentroCustoResource\Pages\CreateCentroCusto;
use App\Filament\Resources\Cadastro\CentroCustoResource\Pages\EditCentroCusto;
use App\Filament\Resources\Cadastro\CentroCustoResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Cadastro\CentroCusto;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CentroCustoResource extends Resource
{
    protected static ?string $model = CentroCusto::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static string | \UnitEnum | null $navigationGroup = 'Cadastro';
    protected static ?string $navigationLabel = 'Centro de Custo (SIGEFES)';

    protected static ?string $modelLabel = 'Centro de Custo';
    protected static ?string $pluralModelLabel = 'Centros de Custo';

//    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('codigo')
                                ->label('Código')
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true),

                            TextInput::make('descricao')
                                ->label('Descrição')
                                ->required()
                                ->maxLength(255),

                        ])
                ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('id', '#', isFirstColumn: true),
                TableColumns::text('codigo', 'Código'),
                TableColumns::text('descricao', 'Descrição')
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('codigo');
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
            'index' => ListCentroCustos::route('/'),
            'create' => CreateCentroCusto::route('/create'),
            'edit' => EditCentroCusto::route('/{record}/edit'),
        ];
    }
}
