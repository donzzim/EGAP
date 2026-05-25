<?php

namespace App\Filament\Resources\Almoxarifado;

use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Resources\Almoxarifado\TipoMovimentacaoNotaFiscalResource\Pages;
use App\Models\Almoxarifado\TipoMovimentacaoNotaFiscal;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TipoMovimentacaoNotaFiscalResource extends Resource
{
    protected static ?string $model = TipoMovimentacaoNotaFiscal::class;
    protected static ?string $cluster = AlmoxarifadoCluster::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Tipo de Movimentação';
    protected static ?string $pluralLabel = 'Tipos de Movimentação';

    //protected static ?string $navigationGroup = 'Almoxarifado';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable(),

                TextColumn::make('atualizadoPor.name')
                    ->label('Atualizado por')
                    ->alignCenter()
                    ->default(' - ')
                    ->searchable(),

                TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->alignCenter()
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoMovimentacaoNotaFiscals::route('/'),
            'create' => Pages\CreateTipoMovimentacaoNotaFiscal::route('/create'),
            'edit' => Pages\EditTipoMovimentacaoNotaFiscal::route('/{record}/edit'),
        ];
    }
}
