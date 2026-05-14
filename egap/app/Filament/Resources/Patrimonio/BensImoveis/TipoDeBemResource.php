<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis;

use App\Filament\Egap\Clusters\PatrimonioCluster;
use App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoDeBemResource\Pages;
use App\Models\Egap\Patrimonio\BensImoveis\TipoDeBem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TipoDeBemResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TipoDeBem::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Tipo de bem';
    protected static ?string $modelLabel = 'Tipo de bem';
    protected static ?string $pluralModelLabel = 'Tipos de bem';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 16;
    protected static ?string $slug = 'bens-imoveis/tipos-de-bem';

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
                    ->modalHeading('Editar Tipo de bem')
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
            ->emptyStateHeading('Nenhum Tipo de bem encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoDeBems::route('/'),
        ];
    }
}
