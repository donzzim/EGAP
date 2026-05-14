<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis;
use App\Models\Patrimonio\BensMoveis\TransferenciaBemMovel;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TransferenciaBemResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TransferenciaBemMovel::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Histórico das movimentações';
    protected static ?string $modelLabel = 'Transferência';
    protected static ?int $navigationSort = 2;


    /**
     * ✅ DESATIVAR BOTÃO "NEW":
     * Ao retornar false aqui, o Filament esconde o botão de criar e
     * bloqueia o acesso à página de criação.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        /** * Mantemos o método vazio ou apenas retornando o form
         * já que não haverá criação/edição por aqui.
         */
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bem.NumPatrimonio')
                    ->label('Patrimônio')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('unidadeAnteriorRel.Setor')
                    ->label('Origem'),

                Tables\Columns\TextColumn::make('unidadeAtualRel.Setor')
                    ->label('Destino'),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Data')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                // Se quiser permitir visualizar os detalhes (apenas leitura), adicione:
                // Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => BensMoveis\TransferenciaBemResource\Pages\ListTransferenciaBems::route('/'),
            /** ✅ REMOVIDO: A rota 'create' foi retirada para seguir o padrão de produção. */
        ];
    }
}
