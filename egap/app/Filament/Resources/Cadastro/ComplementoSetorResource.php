<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Cadastro\ComplementoSetor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComplementoSetorResource extends Resource
{
    protected static ?string $model = ComplementoSetor::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Complemento de Setor';

    protected static ?string $modelLabel = 'Complemento de Setor';

    protected static ?string $pluralModelLabel = 'Complementos de Setor';

    protected static ?string $navigationGroup = 'Cadastro';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('descricao')
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
                TableColumns::text('atualizado_por.name', 'Atualizado por'),
                TableColumns::dateTime('date_time', 'Data Atualização', 'd/m/Y H:i'),
            ])
            ->defaultSort('id', 'asc')
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages\ListComplementoSetors::route('/'),
            'create' => \App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages\CreateComplementoSetor::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages\EditComplementoSetor::route('/{record}/edit'),
        ];
    }
}
