<?php

namespace App\Filament\Resources\Processo;

use App\Filament\Resources\Processo\TipoDocumentoResource\Pages;
use App\Models\Processo\MatTipoDocumento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TipoDocumentoResource extends Resource
{
    protected static ?string $model = MatTipoDocumento::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Processos';
    protected static ?string $navigationLabel = 'Tipos de Documento';
    protected static ?string $modelLabel = 'Tipo de Documento';
    protected static ?string $pluralModelLabel = 'Tipos de Documento';
    protected static ?string $slug = 'processos/tipos-documento';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                Tables\Actions\ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Excluir')
                    ->modalHeading('Excluir registro')
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
            ])
            ->searchPlaceholder('Entre com a palavra-chave')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->emptyStateHeading('Nenhum Tipo de Documento encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoDocumentos::route('/'),
            'create' => Pages\CreateTipoDocumento::route('/create'), // Descomente para usar página separada
            'edit' => Pages\EditTipoDocumento::route('/{record}/edit'), // Descomente para usar página separada
        ];
    }
}
