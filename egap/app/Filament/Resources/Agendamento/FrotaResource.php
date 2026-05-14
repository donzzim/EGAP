<?php

namespace App\Filament\Resources\Agendamento;

use App\Filament\Resources\Agendamento\FrotaResource\Pages;
use App\Models\Agendamento\Frota;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FrotaResource extends Resource
{
    protected static ?string $model = Frota::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Agendamento';
    protected static ?string $modelLabel = 'Veículo';
    protected static ?string $pluralModelLabel = 'Frota';
    protected static ?string $navigationLabel = 'Frota de Veículos';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados do veículo')
                ->description('Preencha as informações principais do veículo.')
                ->icon('heroicon-o-truck')
                ->schema([
                    Forms\Components\Grid::make(12)
                        ->schema([
                            Forms\Components\TextInput::make('descricao')
                                ->label('Descrição')
                                ->placeholder('Ex: Fiat Strada branca')
                                ->required()
                                ->maxLength(50)
                                ->columnSpan(12),

                            Forms\Components\Select::make('marca')
                                ->label('Marca')
                                ->relationship('marcaRef', 'descricao')
                                ->searchable()
                                ->required()
                                ->preload()
                                ->placeholder('Selecione a marca')
                                ->columnSpan(4),

                            Forms\Components\Select::make('modelo')
                                ->label('Modelo')
                                ->required()
                                ->relationship('modeloRef', 'descricao')
                                ->searchable()
                                ->preload()
                                ->placeholder('Selecione o modelo')
                                ->columnSpan(4),

                            Forms\Components\TextInput::make('placa')
                                ->label('Placa')
                                ->required()
                                ->placeholder('ABC-1D23')
                                ->maxLength(255)
                                ->columnSpan(4),

                            Forms\Components\Select::make('proprietario')
                                ->label('Proprietário')
                                ->required()
                                ->options([
                                    '1' => 'Tribunal de Justiça',
                                    '2' => 'Empresa Globo',
                                ])
                                ->required()
                                ->columnSpan(12),
                        ]),

                    Forms\Components\Fieldset::make('Status do veículo')
                        ->schema([
                            Forms\Components\Toggle::make('disponivel')
                                ->label('Disponível')
                                ->required()
                                ->default(true)
                                ->inline(false),

                            Forms\Components\Toggle::make('ativo')
                                ->label('Ativo')
                                ->required()
                                ->default(true)
                                ->inline(false),
                        ])
                        ->columns(2),
                ])
                ->columns(1)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('marcaRef.descricao')
                    ->label('Marca')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('modeloRef.descricao')
                    ->label('Modelo')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('placa')
                    ->label('Placa')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('proprietario')
                    ->label('Proprietário')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('disponivel')
                    ->label('Disponível')
                    ->alignCenter()
                    ->default(false)
                    ->boolean(),

                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->alignCenter()
                    ->boolean(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->alignCenter()
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('idUserRef.name')
                    ->label('Atualizado por')
                    ->alignCenter()
                    ->default(' - ')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('disponivel')
                    ->label('Disponível'),

                Tables\Filters\TernaryFilter::make('ativo')
                    ->label('Ativo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFrotas::route('/'),
            'create' => Pages\CreateFrota::route('/create'),
            'edit' => Pages\EditFrota::route('/{record}/edit'),
        ];
    }
}
