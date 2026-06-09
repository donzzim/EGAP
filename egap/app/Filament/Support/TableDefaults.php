<?php

namespace App\Filament\Support;

use Filament\Tables;
use Filament\Tables\Table;

class TableDefaults
{
    public static function apply(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->actions(self::actions())
            ->bulkActions(self::bulkActions())
            ->paginated([25, 50, 100])
            ->striped();
    }

    public static function actions(): array
    {
        return [
            Tables\Actions\EditAction::make()
                ->tooltip('Editar')
                ->hiddenLabel(),
            Tables\Actions\ViewAction::make()
                ->tooltip('Visualizar')
                ->hiddenLabel(),
            Tables\Actions\DeleteAction::make()
                ->tooltip('Excluir')
                ->hiddenLabel(),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make()
                ->label('Excluir'),
        ];
    }
}
