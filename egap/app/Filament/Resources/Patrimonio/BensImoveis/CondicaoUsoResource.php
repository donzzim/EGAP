<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis;

use App\Filament\Egap\Clusters\PatrimonioCluster;
use App\Filament\Egap\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages;
use App\Models\Egap\Patrimonio\BensImoveis\CondicaoUso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class CondicaoUsoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = CondicaoUso::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Condição de Uso';
    protected static ?string $modelLabel = 'Condição de Uso';
    protected static ?string $pluralModelLabel = 'Condições de Uso';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 10;
    protected static ?string $slug = 'bens-imoveis/condicoes-uso';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('descricao')
                            ->label('descricao')
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

                Tables\Columns\TextColumn::make('descricao')
                    ->label('descricao')
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
                    ->modalHeading('Editar Condição de Uso / Forma de Aquisição')
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
            ->emptyStateHeading('Nenhuma Condição de Uso encontrada');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCondicaoUsos::route('/'),
        ];
    }
}
