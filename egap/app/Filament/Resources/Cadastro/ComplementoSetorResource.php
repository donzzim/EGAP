<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages\CreateComplementoSetor;
use App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages\EditComplementoSetor;
use App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages\ListComplementoSetors;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Cadastro\ComplementoSetor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ComplementoSetorResource extends Resource
{
    protected static ?string $model = ComplementoSetor::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Complemento de Setor';

    protected static ?string $modelLabel = 'Complemento de Setor';

    protected static ?string $pluralModelLabel = 'Complementos de Setor';

    protected static string|\UnitEnum|null $navigationGroup = 'Cadastro';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('id', '#', isFirstColumn: true),
                TableColumns::text('descricao', 'Descrição')
                    ->wrap(),
                TableColumns::updatedBy('atualizado_por.name'),
            ])
            ->defaultSort('id', 'asc')
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComplementoSetors::route('/'),
            'create' => CreateComplementoSetor::route('/create'),
            'edit' => EditComplementoSetor::route('/{record}/edit'),
        ];
    }
}
