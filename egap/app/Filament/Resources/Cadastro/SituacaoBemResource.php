<?php

namespace App\Filament\Resources\Cadastro;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\Cadastro\SituacaoBemResource\Pages\ListSituacaoBems;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Cadastro\SituacaoBem;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class SituacaoBemResource extends Resource
{
    protected static ?string $model = SituacaoBem::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-circle';
    protected static string | \UnitEnum | null $navigationGroup = 'Cadastro';
    protected static ?string $navigationLabel = 'Situação do Bem';
    protected static ?string $modelLabel = 'Situação do Bem';
    protected static ?string $pluralModelLabel = 'Situações do Bem';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('situacao')
                    ->label('Status')
                    ->columnSpanFull(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('id', '#', isFirstColumn: true),
                TableColumns::text('descricao', 'Descrição'),
                TableColumns::text('situacao', 'Status'),
                TableColumns::updatedBy('atualizado_por.name'),
            ])
            ->filters([
                //
            ])
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSituacaoBems::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}
