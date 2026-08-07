<?php

namespace App\Filament\Resources\Processo;

use App\Filament\Resources\Processo\TipoDocumentoResource\Pages\CreateTipoDocumento;
use App\Filament\Resources\Processo\TipoDocumentoResource\Pages\EditTipoDocumento;
use App\Filament\Resources\Processo\TipoDocumentoResource\Pages\ListTipoDocumentos;
use App\Models\Processo\MatTipoDocumento;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TipoDocumentoResource extends Resource
{
    protected static ?string $model = MatTipoDocumento::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static string|\UnitEnum|null $navigationGroup = 'Processos';

    protected static ?string $navigationLabel = 'Tipos de Documento';

    protected static ?string $modelLabel = 'Tipo de Documento';

    protected static ?string $pluralModelLabel = 'Tipos de Documento';

    protected static ?string $slug = 'processos/tipos-documento';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                DeleteAction::make()
                    ->tooltip('Excluir')
                    ->modalHeading('Excluir registro')
                    ->hiddenLabel(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()->label('Excluir Selecionados'),
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
            'index' => ListTipoDocumentos::route('/'),
            'create' => CreateTipoDocumento::route('/create'), // Descomente para usar página separada
            'edit' => EditTipoDocumento::route('/{record}/edit'), // Descomente para usar página separada
        ];
    }
}
