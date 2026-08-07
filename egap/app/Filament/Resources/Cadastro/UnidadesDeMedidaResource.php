<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\UnidadesDeMedidaResource\Pages\CreateUnidadesDeMedida;
use App\Filament\Resources\Cadastro\UnidadesDeMedidaResource\Pages\EditUnidadesDeMedida;
use App\Filament\Resources\Cadastro\UnidadesDeMedidaResource\Pages\ListUnidadesDeMedida;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Cadastro\UnidadesDeMedida;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UnidadesDeMedidaResource extends Resource
{
    protected static ?string $model = UnidadesDeMedida::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'Unidades de Medida';

    protected static ?string $modelLabel = 'Unidade de Medida';

    protected static ?string $pluralModelLabel = 'Unidades de Medida';

    protected static string|\UnitEnum|null $navigationGroup = 'Cadastro';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('Sigla')
                                    ->label('Sigla')
                                    ->required()
                                    ->maxLength(2),

                                TextInput::make('Unidade')
                                    ->label('Descrição da Unidade')
                                    ->required()
                                    ->maxLength(100),
                            ]),
                    ])
                    ->columns(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('Sigla', isFirstColumn: true),
                TableColumns::text('Unidade'),
                TableColumns::updatedBy('atualizado_por.name'),
            ])
            ->defaultSort('Sigla')
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnidadesDeMedida::route('/'),
            'create' => CreateUnidadesDeMedida::route('/create'),
            'edit' => EditUnidadesDeMedida::route('/{record}/edit'),
        ];
    }
}
