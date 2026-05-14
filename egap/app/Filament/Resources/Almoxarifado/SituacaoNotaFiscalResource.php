<?php

namespace App\Filament\Egap\Resources\Almoxarifado;

use App\Filament\Egap\Resources\Almoxarifado\SituacaoNotaFiscalResource\Pages;
use App\Models\Egap\Almoxarifado\SituacaoNotaFiscal;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SituacaoNotaFiscalResource extends Resource
{
    protected static ?string $model = SituacaoNotaFiscal::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Situação da Nota Fiscal';
    protected static ?string $pluralLabel = 'Situações da Nota Fiscal';
    protected static ?string $pluralModelLabel = 'Situações da Nota Fiscal';

    protected static ?string $navigationGroup = 'Almoxarifado';

    protected static ?int $navigationSort = 4;

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
            'index' => Pages\ListSituacaoNotaFiscals::route('/'),
            'create' => Pages\CreateSituacaoNotaFiscal::route('/create'),
            'edit' => Pages\EditSituacaoNotaFiscal::route('/{record}/edit'),
        ];
    }
}
