<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages;
use App\Models\Patrimonio\BensImoveis\Denominacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class DenominacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Denominacao::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Denominação';
    protected static ?string $modelLabel = 'Denominação';
    protected static ?string $pluralModelLabel = 'Denominacao';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 11;
    protected static ?string $slug = 'bens-imoveis/denominacoes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('denominacao')
                            ->label('denominacao')
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
                Tables\Columns\TextColumn::make('id')
                    ->label('id')
                    ->sortable()
                    ->searchable()
                    ->width('80px'),

                Tables\Columns\TextColumn::make('denominacao')
                    ->label('denominacao')
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
                    ->modalHeading('Editar Denominação')
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
            ->emptyStateHeading('Nenhuma Denominação encontrada');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDenominacaos::route('/'),
        ];
    }
}
