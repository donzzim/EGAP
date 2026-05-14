<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\SituacaoResource\Pages;
use App\Models\Patrimonio\BensImoveis\Situacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class SituacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Situacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationLabel = 'Situação';
    protected static ?string $modelLabel = 'Situação';
    protected static ?string $pluralModelLabel = 'Situações';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 14;
    protected static ?string $slug = 'bens-imoveis/situacao';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('Descricao')
                            ->label('Descricao')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Id')
                    ->label('Id')
                    ->sortable()
                    ->searchable()
                    ->width('80px'),

                Tables\Columns\TextColumn::make('Descricao')
                    ->label('Descricao')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Editar Situação')
                    ->modalWidth('md'),

                Tables\Actions\DeleteAction::make()
                    ->label('Excluir')
                    ->color('danger')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ]),
            ])
            ->searchPlaceholder('Entre com a palavra-chave')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->emptyStateHeading('Nenhuma Situação encontrada');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSituacaos::route('/'),
        ];
    }
}
