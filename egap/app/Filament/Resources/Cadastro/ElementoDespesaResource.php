<?php

namespace App\Filament\Egap\Resources\Cadastro;

use App\Filament\Egap\Resources\Cadastro\ElementoDespesaResource\Pages;
use App\Models\Egap\Cadastro\ElementoDespesa;
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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('CodigodaClasse')
                    ->label('Código')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('DescricaodaClasse')
                    ->label('Descrição')
                    ->sortable()
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('Despesa')
                    ->badge()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('VidaUtil')
                    ->label('Vida Útil')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('atualizado_por.name')
                    ->label('Usuário')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('ValorResidual')
                    ->money('BRL', true)
                    ->alignCenter(),
            ])
            ->defaultSort('CodigodaClasse')
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
            'index' => \App\Filament\Egap\Resources\Cadastro\ElementoDespesaResource\Pages\ListElementoDespesas::route('/'),
            'create' => \App\Filament\Egap\Resources\Cadastro\ElementoDespesaResource\Pages\CreateElementoDespesa::route('/create'),
            'edit' => \App\Filament\Egap\Resources\Cadastro\ElementoDespesaResource\Pages\EditElementoDespesa::route('/{record}/edit'),
        ];
    }
}
