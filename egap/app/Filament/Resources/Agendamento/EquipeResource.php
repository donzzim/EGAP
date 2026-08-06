<?php

namespace App\Filament\Resources\Agendamento;

use App\Filament\Resources\Agendamento\EquipeResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Agendamento\Equipe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipeResource extends Resource
{
    protected static ?string $model = Equipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Agendamento';
    protected static ?string $modelLabel = 'Equipe de Transporte';
    protected static ?string $pluralModelLabel = 'Equipes de Transporte';
    protected static ?string $navigationLabel = 'Equipe de Transporte';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados do membro')
                ->description('Informe os dados principais do integrante da equipe.')
                ->icon('heroicon-o-users')
                ->schema([
                    Forms\Components\Grid::make(12)
                        ->schema([
                            Forms\Components\Select::make('id_pessoa')
                                ->label('Pessoa')
                                ->relationship('idPessoaRef', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder('Selecione uma pessoa')
                                ->native(false)
                                ->columnSpan(6),

                            Forms\Components\Select::make('funcao')
                                ->label('Função')
                                ->options([
                                    'Condutor' => 'Condutor',
                                    'Carregador' => 'Carregador',
                                    'Controlador' => 'Controlador',
                                ])
                                ->required()
                                ->columnSpan(6),

                            Forms\Components\TextInput::make('contato')
                                ->label('Contato')
                                ->required()
                                ->numeric()
                                ->mask('(**)*****-****')
                                ->maxLength(255)
                                ->placeholder('Telefone, ramal ou outro contato')
                                ->columnSpan(12),
                        ]),

                    Forms\Components\Fieldset::make('Status')
                        ->schema([
                            Forms\Components\Toggle::make('disponivel')
                                ->label('Disponível')
                                ->default(true)
                                ->inline(false),

                            Forms\Components\Toggle::make('ativo')
                                ->label('Ativo')
                                ->default(true)
                                ->inline(false),
                        ])
                        ->columns(2),
                ])
                ->columns(1)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('funcao', 'Função', true)
                    ->badge()
                    ->color('gray'),

                TableColumns::text('idPessoaRef.name', 'Nome')
                    ->weight('medium')
                    ->wrap(),

                TableColumns::text('contato', 'Contato')
                    ->copyable()
                    ->wrap(),

                Tables\Columns\IconColumn::make('disponivel')
                    ->label('Disponível')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->alignCenter(),

                TableColumns::updatedBy('idUserRef.name'),
            ])
            ->defaultSort('funcao', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipes::route('/'),
            'create' => Pages\CreateEquipe::route('/create'),
            'edit' => Pages\EditEquipe::route('/{record}/edit'),
        ];
    }
}
