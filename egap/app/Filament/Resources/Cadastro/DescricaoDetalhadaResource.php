<?php

namespace App\Filament\Resources\Cadastro;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\Pages\ListDescricaoDetalhadas;
use App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\Pages\CreateDescricaoDetalhada;
use App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\Pages\EditDescricaoDetalhada;
use App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\RelationManagers;
use App\Models\Cadastro\DescricaoDetalhada;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DescricaoDetalhadaResource extends Resource
{
    protected static ?string $model = DescricaoDetalhada::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $navigationLabel = 'Descrição Detalhada';

    protected static ?string $pluralModelLabel = 'Descrições Detalhadas';

    protected static string | \UnitEnum | null $navigationGroup = 'Cadastro';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informações Gerais')
                ->schema([
                    Select::make('descricao_resumida')
                        ->label('Descrição Resumida')
                        ->relationship('descricao_resumida_text' , 'Descricao'),

                    Select::make('marca')
                        ->label('Marca')
                        ->relationship('marca_text' , 'descricao'),

                    Select::make('modelo')
                        ->label('Modelo')
                        ->relationship('modelo_text' , 'descricao'),

                    Textarea::make('descricao_detalhada')
                        ->label('Descrição Detalhada')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull(),

                    TextInput::make('valor_mercado')
                        ->label('Valor de Mercado')
                        ->numeric()
                        ->prefix('R$')
                        ->step('0.01')
                        ->columnSpan(2),
                    Select::make('visibilidade')
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

            Section::make('Arquivos')
                ->schema([

                    FileUpload::make('imagem')
                        ->image()
                        ->disk('public')
                        ->directory('images/descricoes')
                        ->imagePreviewHeight('150'),

                    FileUpload::make('pdf')
                        ->disk('public')
                        ->directory('files/descricoes')
                        ->acceptedFileTypes(['application/pdf']),

                ])
                ->columns(2),

        ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('descricao_resumida_text.Descricao', 'Descrição Resumida', isFirstColumn: true)
                    ->wrap(),
                TableColumns::text('descricao_detalhada', 'Descrição Detalhada')
                    ->wrap(),
                TableColumns::text('marca_text.descricao', 'Marca')
                    ->wrap(),
                TableColumns::text('modelo_text.descricao', 'Modelo')
                    ->wrap(),
                TableColumns::money('valor_mercado'),
                TableColumns::text('visibilidade')
                    ->getStateUsing(function ($record) {
                        switch ($record->visibilidade) {
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
                    }),
                TableColumns::updatedBy('atualizado_por_usuario.name'),
            ])
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDescricaoDetalhadas::route('/'),
            'create' => CreateDescricaoDetalhada::route('/create'),
            'edit' => EditDescricaoDetalhada::route('/{record}/edit'),
        ];
    }
}
