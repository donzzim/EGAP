<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensIntangiveis;

use App\Filament\Egap\Clusters\PatrimonioCluster;
use App\Filament\Egap\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource\Pages;
use App\Models\Egap\Patrimonio\BensIntangiveis\TipoBemIntagivel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TipoBemIntangivelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TipoBemIntagivel::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Tipos de Intangíveis';
    protected static ?string $modelLabel = 'Tipo de Bem Intangível';
    protected static ?string $pluralModelLabel = 'Tipos de Bens Intangíveis';
    protected static ?string $navigationGroup = 'Bens Intangíveis';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes do Tipo')
                    ->description('Defina a nomenclatura para classificar os bens intangíveis.')
                    ->schema([
                        Forms\Components\TextInput::make('descricao')
                            ->label('Descrição')
                            ->placeholder('Ex: Software, Licença, Marca, Patente...')
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
                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizadoPorRef.name')
                    ->label('Atualizado por')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                // Filtros podem ser adicionados aqui
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
            'index' => Pages\ListTipoBemIntangivels::route('/'),
            'create' => Pages\CreateTipoBemIntangivel::route('/create'),
            'edit' => Pages\EditTipoBemIntangivel::route('/{record}/edit'),
        ];
    }
}
