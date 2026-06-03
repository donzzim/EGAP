<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\DescricaoResumidaResource\Pages;
use App\Models\Cadastro\DescricaoResumida;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DescricaoResumidaResource extends Resource
{
    protected static ?string $model = DescricaoResumida::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Descrição Resumida';

    protected static ?string $pluralModelLabel = 'Descrições Resumidas';

    protected static ?string $navigationGroup = 'Cadastro';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informações Gerais')
                ->schema([
                    Forms\Components\Select::make('id_tipo_material')
                        ->label('Tipo Material')
                        ->options([
                            'P' => 'Material Permanente',
                            'C' => 'Material de Consumo',
                            'D' => 'Material de Consumo Durável',
                        ]),

                    Forms\Components\TextInput::make('Descricao')
                        ->label('Descrição')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('CodigodaClasse')
                        ->label('Código da Classe')
                        ->relationship(
                            name: 'codigo_da_classe',
                            titleAttribute: 'CodigodaClasse'
                        )
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('ContaContabil')
                        ->label('Conta Contábil')
                        ->relationship(
                            name: 'conta_contabil',
                            titleAttribute: 'titulo'
                        )
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('id_produto')
                        ->label('Produto')
                        ->relationship(
                            name: 'produto_id',
                            titleAttribute: 'Descricao'
                        )
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('visibilidade')
                        ->options([
                            '0' => 'Ninguém',
                            '1' => 'Comarcas',
                            '2' => 'Tribunal',
                            '3' => 'Todos',
                        ])
                        ->native(false),
                ])
                ->columns(2),
            Forms\Components\Section::make('Arquivos')
                ->schema([
                    Forms\Components\FileUpload::make('imagem')
                        ->image()
                        ->directory('descricoes/imagens')
                        ->imagePreviewHeight('150'),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('Descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('codigo_da_classe.DescricaodaClasse')
                    ->label('Classe')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('conta_contabil.descricao')
                    ->label('Conta Contábil')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizado_por.name')
                    ->label('Atualizado por')
                    ->sortable(),

                Tables\Columns\TextColumn::make('visibilidade')
                    ->getStateUsing(function ($record){
                        switch ($record->visibilidade){
                            case 0:
                                return 'Ninguém';
                            case 1:
                                return 'Comarcas';
                            case 2:
                                return 'Tribunal';
                            case 3:
                                return 'Todos';
                            default:
                                return 'Nenhuma';
                        }
                    })
                    ->alignCenter(),
            ])
            ->defaultSort('id', 'desc')
//            ->filters()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                Tables\Actions\ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Excluir')
                    ->hiddenLabel()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cadastro\DescricaoResumidaResource\Pages\ListDescricaoResumidas::route('/'),
            'create' => \App\Filament\Resources\Cadastro\DescricaoResumidaResource\Pages\CreateDescricaoResumida::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\DescricaoResumidaResource\Pages\EditDescricaoResumida::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}
