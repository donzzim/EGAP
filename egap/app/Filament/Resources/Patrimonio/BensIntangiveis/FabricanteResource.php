<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensIntangiveis;

use App\Filament\Egap\Clusters\PatrimonioCluster;
use App\Filament\Egap\Resources\Patrimonio\BensIntangiveis\FabricanteResource\Pages;
use App\Models\Egap\Patrimonio\BensIntangiveis\Fabricante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class FabricanteResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Fabricante::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Fabricantes';
    protected static ?string $modelLabel = 'Fabricante';
    protected static ?string $pluralModelLabel = 'Fabricantes';
    protected static ?string $navigationGroup = 'Bens Intangíveis';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes do Fabricante')
                    ->description('Informe o nome ou a razão social da empresa fabricante do bem intangível.')
                    ->schema([
                        Forms\Components\TextInput::make('descricao')
                            ->label('Descrição / Nome do Fabricante')
                            ->placeholder('Ex: Microsoft, Adobe, Oracle, TOTVS...')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Fabricante')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizadoPorRef.nome')
                    ->label('Atualizado por')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                // Adicione filtros aqui, se desejar
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFabricantes::route('/'),
            'create' => Pages\CreateFabricante::route('/create'),
            'edit' => Pages\EditFabricante::route('/{record}/edit'),
        ];
    }
}
