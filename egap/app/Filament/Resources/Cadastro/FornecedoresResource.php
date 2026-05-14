<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\FornecedoresResource\Pages;
use App\Filament\Resources\Cadastro\FornecedoresResource\RelationManagers;
use App\Models\Cadastro\Fornecedores;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FornecedoresResource extends Resource
{
    protected static ?string $model = Fornecedores::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Fornecedores';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?string $modelLabel = 'Fornecedor';
    protected static ?string $pluralModelLabel = 'Fornecedores';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('NomeFornecedor')
                    ->label('Nome do Fornecedor')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('Pessoa')
                    ->options([
                        'Física' => 'Física',
                        'Jurídica' => 'Jurídica',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('CNPJ')
                    ->label('CNPJ')
                    ->mask('99.999.999/9999-99')
                    ->stripCharacters(['.', '/', '-'])
                    ->rule('digits:14')
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('NomeFornecedor')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('Pessoa')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('CNPJ')
                    ->label('CNPJ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->alignCenter()
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizado_por.name')
                    ->label('Atualizado por')
                    ->alignCenter()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cadastro\FornecedoresResource\Pages\ListFornecedores::route('/'),
            'create' => \App\Filament\Resources\Cadastro\FornecedoresResource\Pages\CreateFornecedores::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\FornecedoresResource\Pages\EditFornecedores::route('/{record}/edit'),
        ];
    }
}
