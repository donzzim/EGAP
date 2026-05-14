<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TipoImovelResource\Pages;
use App\Models\Patrimonio\BensImoveis\TipoImovel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TipoImovelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TipoImovel::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Tipo de Imóvel';
    protected static ?string $modelLabel = 'Tipo de Imóvel';
    protected static ?string $pluralModelLabel = 'Tipos de Imóvel';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 17;
    protected static ?string $slug = 'bens-imoveis/tipos-imoveis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('desc_tipo_imovel')
                            ->label('desc tipo imovel')
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

                Tables\Columns\TextColumn::make('desc_tipo_imovel')
                    ->label('desc tipo imovel')
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
                    ->modalHeading('Editar Tipo de Imóvel')
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
            ->emptyStateHeading('Nenhum Tipo de Imóvel encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoImovels::route('/'),
        ];
    }
}
