<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\Pages;
use App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\RelationManagers;
use App\Models\Cadastro\DescricaoDetalhada;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DescricaoDetalhadaResource extends Resource
{
    protected static ?string $model = DescricaoDetalhada::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $navigationLabel = 'Descrição Detalhada';

    protected static ?string $pluralModelLabel = 'Descrições Detalhadas';

    protected static ?string $navigationGroup = 'Cadastro';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Informações Gerais')
                ->schema([
                    Forms\Components\Select::make('descricao_resumida')
                        ->label('Descrição Resumida')
                        ->relationship('descricao_resumida_text' , 'Descricao'),

                    Forms\Components\Select::make('marca')
                        ->label('Marca')
                        ->relationship('marca_text' , 'descricao'),

                    Forms\Components\Select::make('modelo')
                        ->label('Modelo')
                        ->relationship('modelo_text' , 'descricao'),

                    Forms\Components\Textarea::make('descricao_detalhada')
                        ->label('Descrição Detalhada')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('valor_mercado')
                        ->label('Valor de Mercado')
                        ->numeric()
                        ->prefix('R$')
                        ->step('0.01')
                        ->columnSpan(2),
                    Forms\Components\Select::make('visibilidade')
                        ->options([
                            '0' => 'Ninguém',
                            '1' => 'Comarcas',
                            '2' => 'Tribunal',
                            '3' => 'Todos',
                        ])
                        ->native(false)
                        ->columnSpan(1),
                ])
                ->columns(3),

            Forms\Components\Section::make('Arquivos')
                ->schema([

                    Forms\Components\FileUpload::make('imagem')
                        ->image()
                        ->directory('descricoes/imagens')
                        ->imagePreviewHeight('150'),

                    Forms\Components\FileUpload::make('pdf')
                        ->directory('descricoes/pdfs')
                        ->acceptedFileTypes(['application/pdf']),

                ])
                ->columns(2),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('descricao_resumida_text.Descricao')
                    ->label('Descrição Resumida')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('descricao_detalhada')
                    ->label('Descrição Detalhada')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('marca_text.descricao')
                    ->label('Marca')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('modelo_text.descricao')
                    ->label('Modelo')
                    ->wrap()
                    ->searchable(),


                Tables\Columns\TextColumn::make('valor_mercado')
                    ->money('BRL')
                    ->sortable()
                    ->alignCenter(),

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
                        }
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('atualizado_por_usuario.name')
                    ->label('Atualizado por')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

            ])
            ->defaultSort('id')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\Pages\ListDescricaoDetalhadas::route('/'),
            'create' => \App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\Pages\CreateDescricaoDetalhada::route('/create'),
            'edit' => \App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\Pages\EditDescricaoDetalhada::route('/{record}/edit'),
        ];
    }
}
