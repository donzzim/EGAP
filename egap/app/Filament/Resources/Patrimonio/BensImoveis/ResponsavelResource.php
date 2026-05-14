<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages;
use App\Models\Patrimonio\BensImoveis\Responsavel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class ResponsavelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Responsavel::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Responsável';
    protected static ?string $modelLabel = 'Responsável';
    protected static ?string $pluralModelLabel = 'Responsáveis';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 13;
    protected static ?string $slug = 'bens-imoveis/responsaveis';

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

                        Forms\Components\TextInput::make('proprietario')
                            ->label('proprietario')
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

                Tables\Columns\TextColumn::make('proprietario')
                    ->label('proprietario')
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
                    ->modalHeading('Editar Responsável')
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
            ->emptyStateHeading('Nenhum Responsável encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResponsavels::route('/'),
        ];
    }
}
