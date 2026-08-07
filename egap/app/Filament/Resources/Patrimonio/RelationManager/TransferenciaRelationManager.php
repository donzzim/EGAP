<?php

namespace App\Filament\Resources\Patrimonio\RelationManager;

use App\Filament\Support\TableColumns;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransferenciaRelationManager extends RelationManager
{
    protected static string $relationship = 'transferencias';

    protected static ?string $title = 'Bens / Materiais Vinculados ao Termo';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]); // Apenas visualização
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('patrimonioRef.NumPatrimonio')
                    ->label('Num. Patrimônio')
                    ->default(fn ($record) => $record->NumPatrimonio ?? 'Sem Número')
                    ->description(fn ($record) => $record->patrimonioRef?->Descricao ?? $record->MaterialDescricao ?? null)
                    ->wrap()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unidadeJudiciariaRef.UnidadeOrganizacional')
                    ->label('Unidade Judiciária Atual')
                    ->default(fn ($record) => $record->UnidadeJudiciariaAtual ?? 'Não Carregado')
                    ->wrap(),

                TextColumn::make('setorRef.Setor')
                    ->label('Setor Atual')
                    ->default(fn ($record) => $record->SetorAtual ?? 'Não Carregado')
                    ->wrap(),

                TextColumn::make('complementoRef.descricao')
                    ->label('Complemento')
                    ->default(fn ($record) => $record->ComplementoAtual ?? 'NÃO INFORMADO'),

                TableColumns::updatedBy('usuarioRef.name')
                    ->default(fn ($record) => $record->AtualizadoPor ?? 'Seção de Patrimônio'),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([
                ViewAction::make()->label('Ver'),
            ])
            ->toolbarActions([]);
    }
}
