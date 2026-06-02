<?php

namespace App\Filament\Resources\Patrimonio\RelationManager;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransferenciaRelationManager extends RelationManager
{
    protected static string $relationship = 'transferencias';

    protected static ?string $title = 'Bens / Materiais Vinculados ao Termo';

    public function form(Form $form): Form
    {
        return $form->schema([]); // Apenas visualização
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('patrimonioRef.NumPatrimonio')
                    ->label('Num. Patrimônio')
                    ->default(fn ($record) => $record->NumPatrimonio ?? 'Sem Número')
                    ->description(fn ($record) => $record->patrimonioRef?->Descricao ?? $record->MaterialDescricao ?? null)
                    ->wrap()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unidadeJudiciariaRef.UnidadeOrganizacional')
                    ->label('Unidade Judiciária Atual')
                    ->default(fn ($record) => $record->UnidadeJudiciariaAtual ?? 'Não Carregado')
                    ->wrap(),

                Tables\Columns\TextColumn::make('setorRef.Setor')
                    ->label('Setor Atual')
                    ->default(fn ($record) => $record->SetorAtual ?? 'Não Carregado')
                    ->wrap(),

                Tables\Columns\TextColumn::make('complementoRef.descricao')
                    ->label('Complemento')
                    ->default(fn ($record) => $record->ComplementoAtual ?? 'NÃO INFORMADO'),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usuarioRef.name')
                    ->label('Atualizado por')
                    ->default(fn ($record) => $record->AtualizadoPor ?? 'Seção de Patrimônio'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver'),
            ])
            ->bulkActions([]);
    }
}
