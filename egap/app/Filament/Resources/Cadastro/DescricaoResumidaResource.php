<?php

namespace App\Filament\Resources\Cadastro;

use App\Filament\Resources\Cadastro\DescricaoResumidaResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
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

    protected static ?string $modelLabel = 'Descrição Resumida';

    protected static ?string $navigationGroup = 'Cadastro';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informações Gerais')
                ->schema([
                    Forms\Components\Select::make('id_tipo_material')
                        ->label('Tipo Material')
                        ->required()
                        ->options([
                            'P' => 'Material Permanente',
                            'C' => 'Material de Consumo',
                            'D' => 'Material de Consumo Durável',
                        ])
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('Descricao')
                        ->label('Descrição')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('ContaContabil')
                        ->label('Conta Contábil')
                        ->required()
                        ->relationship(
                            name: 'conta_contabil',
                            titleAttribute: 'titulo'
                        )
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('id_produto')
                        ->label('Elemento de Despesa')
                        ->required()
                        ->relationship(
                            name: 'produto_id',
                            titleAttribute: 'DescricaodaClasse'
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
                        ->required()
                        ->native(false)
                        ->columnSpanFull(),
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
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('Descricao', 'Descrição', isFirstColumn: true)
                    ->wrap()
                    ->limit(50),
                TableColumns::text('codigo_da_classe.DescricaodaClasse', 'Classe'),
                TableColumns::text('conta_contabil.titulo', 'Conta Contábil'),
                TableColumns::dateTime('date_time', 'Atualizado em', 'd/m/Y H:i'),
                TableColumns::text('atualizado_por.name', 'Atualizado por'),
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
            ])
            ->defaultSort('id', 'desc');
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
