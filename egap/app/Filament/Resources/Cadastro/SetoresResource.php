<?php

namespace App\Filament\Egap\Resources\Cadastro;

use App\Filament\Egap\Resources\Cadastro\SetoresResource\Pages;
use App\Models\Egap\Cadastro\Setores;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class SetoresResource extends Resource
{
    protected static ?string $model = Setores::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Setores';
    protected static ?string $modelLabel = 'Setor';
    protected static ?string $pluralModelLabel = 'Setores';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?int $navigationSort = 6;
    protected static ?string $maxContentWidth = '6xl';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identificação')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('CodigoPai')
                                ->label('Setor Pai')
                                ->relationship('pai', 'Setor')
                                ->searchable()
                                ->preload()
                                ->nullable(),

                            TextInput::make('UnidadeOrganizacional')
                                ->label('Unidade Organizacional')
                                ->maxLength(255),

                            TextInput::make('Setor')
                                ->label('Nome do Setor')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('CodigodaUO')
                                ->label('Código da UO')
                                ->numeric(),

                            TextInput::make('ordem')
                                ->label('Ordem')
                                ->numeric(),

                            TextInput::make('cd_orgao')
                                ->label('Código do Órgão')
                                ->numeric(),
                        ]),
                    ])->columns(1),

                Section::make('Informações Complementares')
                    ->schema([
                        Grid::make(2)->schema([

                            TextInput::make('email')
                                ->label('E-mail')
                                ->email()
                                ->maxLength(255),

                            TextInput::make('centrocusto')
                                ->label('Centro de Custo')
                                ->maxLength(255),

                            Textarea::make('comarca')
                                ->label('Comarca')
                                ->rows(2),

                            Textarea::make('vara')
                                ->label('Vara')
                                ->rows(2),

                            Textarea::make('presidencia')
                                ->label('Presidência')
                                ->rows(2),

                            TextInput::make('cns')
                                ->label('CNS')
                                ->maxLength(255),
                        ]),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('CodigoPai')
                    ->label('Código Pai')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('Setor')
                    ->label('Setor')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('atualizado_por.name')
                    ->label('Atualizado por')
                    ->sortable(),

                Tables\Columns\TextColumn::make('CodigodaUO')
                    ->label('Código UO')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->groups([
                Group::make('UnidadeOrganizacional')
                    ->label('')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn ($record) => $record->UnidadeOrganizacional)
                    ->getTitleFromRecordUsing(fn ($record) =>
                        $record->UnidadeOrganizacional .
                        ' (' .
                        Setores::where('UnidadeOrganizacional', $record->UnidadeOrganizacional)->count()
                        . ')'
                    ),
            ])
            ->defaultGroup('UnidadeOrganizacional')
            ->defaultSort('UnidadeOrganizacional')
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Excluir selecionados'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Egap\Resources\Cadastro\SetoresResource\Pages\ListSetores::route('/'),
            'create' => \App\Filament\Egap\Resources\Cadastro\SetoresResource\Pages\CreateSetores::route('/create'),
            'edit' => \App\Filament\Egap\Resources\Cadastro\SetoresResource\Pages\EditSetores::route('/{record}/edit'),
        ];
    }
}
